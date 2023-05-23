<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\modules\admin\{components\Model, models\Reason, models\ReasonsSet};
use app\rbac\Permissions;
use Throwable;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\StaleObjectException;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\{Controller, NotFoundHttpException, Request, Response, User};
use yii\widgets\ActiveForm;

/**
 * ReasonsController implements the CRUD actions for ReasonsSet model.
 */
class ReasonsController extends Controller
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
     * Lists all ReasonsSet models.
     * @param User $user
     * @param Request $request
     * @return string
     */
    public function actionIndex(User $user, Request  $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/reasons', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/reasons', 'BREADCRUMBS_INDEX')
            ];
        }
        $dataProvider = new ActiveDataProvider([
            'query' => ReasonsSet::find(),
        ]);
        return $this->render('index.twig', [
            'dataProvider' => $dataProvider,
            'canEdit' => $user->can(Permissions::PERMISSION_WEBSETTINGS_EDIT)
        ]);
    }

    /**
     * Creates a new Agent model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param Request $request
     * @return Response|string
     */
    public function actionCreate(Request $request)
    {
        $model = new ReasonsSet();
        $reasons = [new Reason()];

        $result = $this->createUpdate($model, $reasons, $request);
        if ($result) {
            return $result;
        }
        $this->getView()->title = Yii::t('admin/reasons', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/reasons', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/reasons', 'BREADCRUMBS_CREATE'),
        ];
        return $this->render('create.twig', [
            'model' => $model,
            'reasons' => $reasons
        ]);
    }

    /**
     * Updates an existing Agent model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @param Request $request
     * @return Response|string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request)
    {
        $model = $this->findModel($id);
        $reasons = $model->reasons ?: [new Reason()];

        $result = $this->createUpdate($model, $reasons, $request);
        if ($result) {
            return $result;
        }

        $this->getView()->title = Yii::t('admin/reasons', 'PAGE_TITLE_UPDATE', [
            'setname' => $model->setname,
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/reasons', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/reasons', 'BREADCRUMBS_UPDATE', [
                'setname' => $model->setname,
            ]),
        ];

        return $this->render('update.twig', [
            'model' => $model,
            'reasons' => $reasons,
        ]);
    }

    private function createUpdate(ReasonsSet $model, array $reasons, Request $request): ?Response
    {
        if ($model->load($request->post())) {
            /** @var Reason[] $reasons */
            $reasons = Model::createMultiple(Reason::class, $model->getIsNewRecord() ? [] : $reasons);
            Model::loadMultiple($reasons, $request->post());

            if ($request->post('ajax') === 'reasons-form') {
                return $this->asJson(ArrayHelper::merge(
                    ActiveForm::validateMultiple($reasons),
                    ActiveForm::validate($model)
                ));
            }
            if (!$model->getIsNewRecord()) {
                $model->unlinkAll('reasons', true);
            }
            if ($model->save()) {
                if ($reasons) {
                    foreach($reasons as $reason) {
                        if ($reason->getIsNewRecord()) {
                            $check = Reason::find()
                                ->where(['reason' => $reason->reason, 'static_bantime' => $reason->static_bantime])
                                ->one();
                            if ($check) {
                                $reason = $check;
                            }
                        }
                        $reason->save();
                        $reason->refresh();
                        $model->link('reasons', $reason);
                    }
                }
                return $this->redirect(['index']);
            }
        }
        return null;
    }


    /**
     * Deletes an existing ReasonsSet model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return mixed
     * @throws NotFoundHttpException|StaleObjectException|Throwable if the model cannot be found
     */
    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the ReasonsSet model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return ReasonsSet the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): ReasonsSet
    {
        if (($model = ReasonsSet::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('admin/reasons', 'The requested page does not exist.'));
    }
}
