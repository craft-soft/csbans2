<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\forms\LoginForm;
use yii\web\{Controller, Request, Response};
use yii\filters\{AccessControl, VerbFilter};
use yii\widgets\ActiveForm;

class AuthController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }


    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin(Request $request)
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $this->getView()->title = Yii::t('login', 'PAGE_TITLE');
        $this->getView()->params['breadcrumbs'] = [
            Yii::t('login', 'VIEW_TITLE')
        ];
        $model = new LoginForm();
        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'login-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->login()) {
                return $this->goBack();
            }
        }

        $model->password = '';
        return $this->render('login.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }
}
