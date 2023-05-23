<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\controllers;

use yii\web\Controller;
use app\services\IndexViewService;
use app\components\params\AppParams;

class DefaultController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
                'view' => 'error.twig'
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @param AppParams $appParams
     * @return \yii\web\Response|string
     */
    public function actionIndex(AppParams $appParams)
    {
        if ($appParams->start_page && $appParams->start_page !== '/') {
            return $this->redirect($appParams->start_page);
        }
        $service = new IndexViewService($appParams);
        return $this->render('index.twig', [
            'bans' => $service->bans(),
            'servers' => $service->servers(),
            'content' => $this->getView()->renderDynamicContent($appParams->view_index_page_html)
        ]);
    }
}
