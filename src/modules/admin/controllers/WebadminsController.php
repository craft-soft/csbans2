<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\modules\admin\models\{search\WebadminSearch, Webadmin, WebadminAuth};
use app\rbac\{Permissions};
use app\rbac\RbacService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\{AccessControl, VerbFilter};
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\{Controller, NotFoundHttpException, Request, Response, Session, User};

/**
 * WebadminsController implements the CRUD actions for Webadmin model.
 */
class WebadminsController extends Controller
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
                        'actions' => ['index', 'view', 'auth-item'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBADMINS_VIEW]
                    ],
                    [
                        'actions' => ['create', 'update', 'delete'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBADMINS_EDIT]
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
     * Lists all Webadmin models.
     * @param Request $request
     * @param User $user
     * @return string
     */
    public function actionIndex(Request $request, User $user): string
    {
        $searchModel = new WebadminSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/webadmins', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/webadmins', 'BREADCRUMBS_INDEX')
            ];
        }
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'canUpdate' => $user->can(Permissions::PERMISSION_WEBADMINS_EDIT),
            'canViewProfile' => function(Webadmin $webadmin) use ($user) {
                return $user->getId() === $webadmin->id || $user->can(Permissions::PERMISSION_WEBADMINS_EDIT);
            },
            'profileButton' => function($url, Webadmin $webadmin) {
                return Html::a(
                    Html::tag('i', '', ['class' => 'fas fa-user']),
                    Url::to(['/admin/profile/index', 'id' => $webadmin->id]),
                    ['data-pjax' => '0']
                );
            }
        ]);
    }

    /**
     * Displays a single Webadmin model.
     * @param int $id ID
     * @param RbacService $rbacService
     * @param Session $session
     * @param User $user
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id, RbacService $rbacService, Session $session, User $user): string
    {
        $model = $this->findModel($id);
        $this->getView()->title = Yii::t('admin/webadmins', 'PAGE_TITLE_VIEW', [
            'username' => $model->username,
            'id' => $model->id
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/webadmins', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/webadmins', 'BREADCRUMBS_VIEW', [
                'username' => $model->username,
                'id' => $model->id
            ])
        ];
        $permissions = [];
        foreach ($rbacService->getAuthManager()->getPermissionsByUser($model->id) as $permission) {
            $permissions[] = Yii::t('rbac', $permission->description);
        }
        $authsDataProvider = null;
        if ((int)$model->id === (int)$user->getId() || $user->can(Permissions::PERMISSION_WEBADMIN_AUTHS_VIEW)) {
            $authsDataProvider = new ActiveDataProvider([
                'query' => WebadminAuth::find()->where(['admin_id' => $model->id]),
                'sort' => [
                    'defaultOrder' => [
                        'date' => SORT_DESC,
                    ],
                    // The current session is always in top
                    'attributes' => [
                        'date'
                    ],
                ],
                'pagination' => [
                    'pageParam' => 'auth-history-page',
                    'pageSizeParam' => 'auth-history-per-page'
                ]
            ]);
        }
        return $this->render('view.twig', [
            'model' => $model,
            'permissions' => $permissions,
            'currentSessionId' => $session->getId(),
            'authsDataProvider' => $authsDataProvider,
            'authGridRowOptions' => function(WebadminAuth $auth) use ($session) {
                $options = [];
                if ($auth->session_id === $session->getId()) {
                    $options['class'] = 'table-success';
                }
                return $options;
            },
        ]);
    }

    /**
     * Creates a new Webadmin model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @param Request $request
     * @return Response|string
     */
    public function actionCreate(Request $request)
    {
        $model = new Webadmin();

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }
        $this->getView()->title = Yii::t('admin/webadmins', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/webadmins', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/webadmins', 'BREADCRUMBS_CREATE'),
        ];
        return $this->render('create.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Webadmin model.
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
        $this->getView()->title = Yii::t('admin/webadmins', 'PAGE_TITLE_UPDATE', [
            'username' => $model->username,
            'id' => $model->id
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/webadmins', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            [
                'label' => Yii::t('admin/webadmins', 'BREADCRUMBS_VIEW', [
                    'username' => $model->username,
                    'id' => $model->id
                ]),
                'url' => ['view', 'id' => $model->id]
            ],
            Yii::t('admin/webadmins', 'BREADCRUMBS_UPDATE', [
                'username' => $model->username,
                'id' => $model->id
            ]),
        ];
        return $this->render('update.twig', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Webadmin model.
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
     * Finds the Webadmin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Webadmin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Webadmin
    {
        if (($model = Webadmin::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
    }
}
