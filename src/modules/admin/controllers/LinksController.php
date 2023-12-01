<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\modules\admin\models\Link;
use app\rbac\Permissions;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\{NotFoundHttpException, Request, Response};
use yii\web\Controller;

/**
 * LinksController implements the CRUD actions for Link model.
 */
class LinksController extends Controller
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
                        'actions' => ['index'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBSETTINGS_VIEW]
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBSETTINGS_EDIT]
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Link models.
     * @param Request $request
     * @return string
     */
    public function actionIndex(Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/links', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                $this->getView()->title,
            ];
        }
        $dataProvider = new ActiveDataProvider([
            'query' => Link::find(),
        ]);
        return $this->render('index.twig', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Link model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param Request $request
     * @return Response|string
     */
    public function actionCreate(Request $request)
    {
        $model = new Link();

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $this->getView()->title = Yii::t('admin/links', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/links', 'BREADCRUMBS_CREATE'),
                'url' => ['index']
            ],
            $this->getView()->title,
        ];

        return $this->render('create.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Link model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param Request $request
     * @return Response|string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request)
    {
        $model = $this->findModel($id);

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        $this->getView()->title = Yii::t('admin/links', 'PAGE_TITLE_UPDATE', [
            'label' => Yii::t('mainMenu', $model->label),
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/links', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/links', 'BREADCRUMBS_UPDATE', [
                'label' => Yii::t('mainMenu', $model->label),
            ]),
        ];

        return $this->render('update.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Link model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException|\yii\db\StaleObjectException|\Throwable if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Link model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Link the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Link
    {
        if (($model = Link::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
