<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\modules\admin\models\{AdminsServer, AmxAdmin, search\AmxAdminsSearch};
use app\rbac\Permissions;
use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\web\{Controller, NotFoundHttpException, Request, Response};
use yii\widgets\ActiveForm;

/**
 * AmxAdminsController implements the CRUD actions for AmxAdmin model.
 */
class AmxAdminsController extends Controller
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
                        'actions' => ['index', 'view'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_AMXADMINS_VIEW]
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_AMXADMINS_EDIT]
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
     * Lists all AmxAdmin models.
     * @param Request $request
     * @return string
     */
    public function actionIndex(Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/amxAdmins', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/amxAdmins', 'BREADCRUMBS_INDEX')
            ];
        }
        $searchModel = new AmxAdminsSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single AmxAdmin model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        $model = $this->findModel($id);
        $this->getView()->title = Yii::t('admin/amxAdmins', 'PAGE_TITLE_VIEW', [
            'nickname' => $model->nickname,
            'id' => $model->id
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/amxAdmins', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/amxAdmins', 'BREADCRUMBS_VIEW', [
                'nickname' => $model->nickname,
                'id' => $model->id
            ])
        ];
        $servers = $model->getAdminsServers()->alias('link')->joinWith('server server')->all();
        return $this->render('view.twig', [
            'model' => $model,
            'servers' => $servers,
            'flags' => $model->getViewAccessFlags()
        ]);
    }

    /**
     * Creates a new AmxAdmin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param Request $request
     * @return Response|string
     */
    public function actionCreate(Request $request)
    {
        $model = new AmxAdmin();

        /** @noinspection DuplicatedCode */
        if ($model->load($request->post())) {
            $links = $this->loadLinks($request->post());
            if ($request->post('ajax') === 'amxadmins-form') {
                return $this->asJson(ActiveForm::validateMultiple(ArrayHelper::merge([$model], $links)));
            }
            if ($model->save()) {
                $model->addServers($links);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $this->getView()->title = Yii::t('admin/amxAdmins', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/amxAdmins', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/amxAdmins', 'BREADCRUMBS_CREATE'),
        ];
        return $this->render('create.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing AmxAdmin model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param Request $request
     * @return Response|string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request)
    {
        $model = $this->findModel($id);
        /** @noinspection DuplicatedCode */
        if ($model->load($request->post())) {
            $links = $this->loadLinks($request->post());
            if ($request->post('ajax') === 'amxadmins-form') {
                return $this->asJson(ActiveForm::validateMultiple(ArrayHelper::merge([$model], $links)));
            }
            if ($model->save()) {
                $model->addServers($links);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }
        $this->getView()->title = Yii::t('admin/amxAdmins', 'PAGE_TITLE_UPDATE', [
            'nickname' => $model->nickname,
            'id' => $model->id
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/amxAdmins', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            [
                'label' => Yii::t('admin/amxAdmins', 'BREADCRUMBS_VIEW', [
                    'nickname' => $model->nickname,
                    'id' => $model->id
                ]),
                'url' => ['view', 'id' => $model->id]
            ],
            Yii::t('admin/amxAdmins', 'BREADCRUMBS_UPDATE', [
                'nickname' => $model->nickname,
                'id' => $model->id
            ]),
        ];

        return $this->render('update.twig', [
            'model' => $model,
        ]);
    }

    /**
     * @param AdminsServer[] $post
     * @return array
     */
    private function loadLinks(array $post): array
    {
        $formName = (new AdminsServer())->formName();
        if (!isset($post[$formName])) {
            return [];
        }
        $links = [];
        foreach ($post[$formName] as $serverId => $attributes) {
            $link = new AdminsServer();
            if ($link->load($attributes, '')) {
                $link->server_id = $serverId;
                $links[] = $link;
            }
        }
        return $links;
    }

    /**
     * Deletes an existing AmxAdmin model.
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
     * Finds the AmxAdmin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return AmxAdmin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): AmxAdmin
    {
        if (($model = AmxAdmin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('admin/servers', 'The requested page does not exist.'));
    }
}
