<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\AmxAdmin;
use yii\data\ActiveDataProvider;
use yii\web\{Controller, NotFoundHttpException, Request};

class AdminsController extends Controller
{
    public function actionIndex(Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('amxAdmins', 'PAGE_TITLE_INDEX');
        }
        $dataProvider = new ActiveDataProvider([
            'query' => AmxAdmin::find()
                ->where('[[ashow]] = 1 AND ([[expired]] = 0 OR [[expired]] > UNIX_TIMESTAMP())'),
            'sort' => [
                'defaultOrder' => [
                    'expired' => SORT_DESC,
                    'nickname' => SORT_ASC
                ],
            ]
        ]);
        return $this->render('index.twig', [
            'admins' => $dataProvider->getModels(),
            'sort' => $dataProvider->getSort(),
            'pagination' => $dataProvider->getPagination()
        ]);
    }

    public function actionView(int $id): string
    {
        $model = AmxAdmin::findOne(['id' => $id]);
        if (!$model) {
            throw new NotFoundHttpException();
        }
        $this->getView()->title = Yii::t('amxAdmins', 'PAGE_TITLE_VIEW', ['adminName' => $model->nickname]);
        return $this->render('view.twig', [
            'admin' => $model,
            'flags' => $model->getViewAccessFlags(),
            'accountType' => $model->getViewAccountType(),
            'servers' => $model->getAdminsServers()->alias('link')->joinWith('server server')->all()
        ]);
    }
}
