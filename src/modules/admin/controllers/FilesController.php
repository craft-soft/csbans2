<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use Yii;
use yii\web\Controller;
use app\rbac\Permissions;
use yii\filters\{AccessControl, VerbFilter};
use yii\web\{Request, Response, NotFoundHttpException};
use app\modules\admin\models\{File, search\FilesSearch};
use yii\widgets\ActiveForm;

/**
 * CommentsController implements the CRUD actions for Comment model.
 */
class FilesController extends Controller
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
                        'permissions' => [Permissions::PERMISSION_MODERATE_CONTENT]
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
     * Lists all Comment models.
     * @param Request $request
     * @return string
     */
    public function actionIndex(Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/files', 'PAGE_TITLE_INDEX');
        }
        $this->getView()->params['breadcrumbs'] = [
            Yii::t('admin/files', 'BREADCRUMBS_INDEX')
        ];
        $searchModel = new FilesSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Updates an existing Comment model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param Request $request
     * @return Response|string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request)
    {
        $model = $this->findModel($id);

        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'comments-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                return $this->redirect(['index']);
            }
        }

        $this->getView()->title = Yii::t('admin/files', 'PAGE_TITLE_MODERATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/files', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/files', 'BREADCRUMBS_MODERATE'),
        ];

        return $this->render('update.twig', [
            'model' => $model,
        ]);
    }

    public function actionApprove($id): Response
    {
        $this->findModel($id)->approve();
        return $this->redirect(['index']);
    }

    /**
     * Deletes an existing Comment model.
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
     * Finds the Comment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return File the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): File
    {
        if (($model = File::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
