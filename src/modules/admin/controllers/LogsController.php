<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\models\Log;
use app\modules\admin\models\search\LogSearch;
use app\rbac\Permissions;
use Yii;
use yii\filters\AccessControl;
use yii\web\{NotFoundHttpException, Request};
use yii\web\Controller;

/**
 * LogsController implements the CRUD actions for Log model.
 */
class LogsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBSETTINGS_VIEW]
                    ],
                ]
            ],
        ];
    }

    /**
     * Lists all Log models.
     * @param Request $request
     * @return string
     */
    public function actionIndex(Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/logs', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                $this->getView()->title,
            ];
        }
        $searchModel = new LogSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Log model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException|\yii\base\InvalidConfigException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        $this->getView()->title = Yii::t('admin/logs', 'PAGE_TITLE_VIEW', [
            'datetime' => Yii::$app->getFormatter()->asDatetime($model->timestamp),
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/logs', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/logs', 'BREADCRUMBS_VIEW', [
                'datetime' => Yii::$app->getFormatter()->asDatetime($model->timestamp),
            ])
        ];
        return $this->render('view.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Log the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Log
    {
        if (($model = Log::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
