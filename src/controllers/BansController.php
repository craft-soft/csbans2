<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\controllers;

use app\components\params\AppParams;
use app\components\User;
use app\models\Comment;
use app\models\File;
use app\models\forms\bans\NewComment;
use app\models\forms\bans\NewFile;
use app\services\BanListService;
use app\services\BanViewService;
use Yii;
use app\models\Ban;
use yii\base\InvalidConfigException;
use yii\base\Security;
use yii\filters\VerbFilter;
use app\components\ipGeo\IpGeo;
use app\models\search\BansSearch;
use yii\web\{AssetManager, Controller, NotFoundHttpException, Request, Response, Session, UploadedFile};
use yii\widgets\ActiveForm;

class BansController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'flag' => ['post', 'ajax']
                ]
            ]
        ];
    }

    /**
     * Lists all Ban models.
     * @param Request $request
     * @param IpGeo $ipGeo
     * @param AssetManager $assetManager
     * @param AppParams $appParams
     * @return string
     * @throws InvalidConfigException
     */
    public function actionIndex(Request $request, IpGeo $ipGeo, AssetManager $assetManager, AppParams $appParams): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('bans', 'PAGE_TITLE_INDEX');
        }
        $searchModel = new BansSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'bans' => $dataProvider->getModels(),
            'sort' => $dataProvider->getSort(),
            'ipGeoCred' => $ipGeo->getCredentials(),
            'viewIpGeoCred' => $appParams->ip_view_provider_cred,
            'userBanned' => $searchModel->isPlayerBanned($request->getUserIP()),
            'userIp' => $request->getUserIP(),
            'defaultFlag' => $assetManager->publish($ipGeo->defaultFlag())[1],
            'pagination' => $dataProvider->getPagination(),
            'viewFiles' => $appParams->bans_view_files_count,
            'viewComments' => $appParams->bans_view_comments_count,
            'viewKicks' => $appParams->bans_view_kicks_count,
        ]);
    }

    public function actionView(int $id, Request $request, AppParams $appParams, Session $session, User $user)
    {
        $model = $this->findModel($id);
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('bans', 'PAGE_TITLE_VIEW', [
                'playerName' => $model->player_nick,
            ]);
        }
        $canComment = $user->canAddComments();
        $canAddFile = $user->canAddFiles();
        $newCommentModel = new NewComment();
        if ($canComment && $newCommentModel->load($request->post())) {
            if ($request->post('ajax') === 'new-comment-form') {
                return $this->asJson(ActiveForm::validate($newCommentModel));
            }
            if ($newCommentModel->save($model->bid, $request->getUserIP(), $appParams->moderate_comments)) {
                $message = $appParams->moderate_comments ? 'COMMENT_SAVED_MODERATE' : 'COMMENT_SAVED';
                $session->setFlash('success', Yii::t('bans', $message));
                if (!$request->getIsPjax()) {
                    return $this->refresh();
                } else {
                    $newCommentModel = new NewComment();
                }
            }
        }
        $newFileModel = new NewFile();
        if ($canAddFile && $newFileModel->load($request->post())) {
            if ($request->post('ajax') === 'new-file-form') {
                return $this->asJson(ActiveForm::validate($newFileModel));
            }
            if ($newFileModel->upload($model->bid, $request->getUserIP(), $appParams->moderate_comments)) {
                $message = $appParams->moderate_files ? 'FILE_SAVED_MODERATE' : 'FILE_SAVED';
                $session->setFlash('success', Yii::t('bans', $message));
                if (!$request->getIsPjax()) {
                    return $this->refresh();
                } else {
                    $newFileModel = new NewFile();
                }
            }
        }
        if (!$user->getIsGuest()) {
            $newCommentModel->email = $user->getIdentity()->email;
            $newCommentModel->name = $user->getName();
            $newFileModel->email = $user->getIdentity()->email;
            $newFileModel->name = $user->getName();
        }
        $service = new BanViewService($model, true);
        $historyProvider = $service->banHistoryProvider();
        $filesProvider = $service->filesProvider();
        $commentsProvider = $service->commentsProvider();
        return $this->render('view.twig', [
            'ban' => $model,
            'historyBans' => $historyProvider->getModels(),
            'historyPagination' => $historyProvider->getPagination(),
            'historySort' => $historyProvider->getSort(),
            'totalHistory' => $historyProvider->getTotalCount(),
            'hasHistory' => (bool)$historyProvider->getTotalCount(),
            'files' => $filesProvider->getModels(),
            'filesPagination' => $filesProvider->getPagination(),
            'filesSort' => $filesProvider->getSort(),
            'totalFiles' => $filesProvider->getTotalCount(),
            'hasFiles' => (bool)$filesProvider->getTotalCount(),
            'comments' => $commentsProvider->getModels(),
            'commentsPagination' => $commentsProvider->getPagination(),
            'commentsSort' => $commentsProvider->getSort(),
            'totalComments' => $commentsProvider->getTotalCount(),
            'hasComments' => (bool)$commentsProvider->getTotalCount(),
            'showKicks' => $appParams->bans_view_kicks_count,
            'newComment' => $newCommentModel,
            'newFile' => $newFileModel,
            'canComment' => $canComment,
            'canAddFile' => $canAddFile,
        ]);
    }

    public function actionFile(int $id, Response $response): Response
    {
        $model = File::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        $file = Yii::getAlias($model->getFilePath());
        if (!is_file($file)) {
            throw new NotFoundHttpException();
        }
        $model->updateCounters(['down_count' => 1]);
        return $response->sendFile($file, $model->demo_file);
    }

    public function actionFlag(int $id, Response $response, IpGeo $geo, AssetManager $assetManager): ?array
    {
        $response->format = Response::FORMAT_JSON;
        $model = $this->findModel($id);
        if (!$model->player_ip) {
            return null;
        }
        $data = $geo->getData($model->player_ip);
        if ($data) {
            $path = $data->getFlag();
            $assetManager->publish($path);
            return [
                'url' => $assetManager->getPublishedUrl($path),
                'country' => $data->getCountry()
            ];
        }
        return null;
    }

    /**
     * @param int $id
     * @return Ban
     * @throws NotFoundHttpException
     */
    private function findModel(int $id): Ban
    {
        $model = Ban::findOne($id);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        return $model;
    }
}
