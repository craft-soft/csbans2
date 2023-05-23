<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\components\ipGeo\IpGeo;
use app\components\User;
use app\modules\admin\models\{Ban, search\BansSearch};
use app\rbac\Permissions;
use app\services\BanViewService;
use Yii;
use yii\filters\{AccessControl, VerbFilter};
use yii\helpers\ArrayHelper;
use yii\web\{Controller, NotFoundHttpException, Request, Response, Session};
use yii\widgets\ActiveForm;

class BansController extends Controller
{
    private ?Ban $modelCache = null;

    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'unban' => ['POST'],
                ]
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['index', 'view'],
                        'allow' => true,
                    ],
                    [
                        'actions' => ['create'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_BANS_ADD]
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_BANS_EDIT],
                        'roleParams' => $this->roleParams(),
                    ],
                    [
                        'actions' => ['unban'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_BANS_UNBAN],
                        'roleParams' => $this->roleParams(),
                    ],
                    [
                        'actions' => ['delete'],
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_BANS_DELETE],
                        'roleParams' => $this->roleParams(),
                    ],
                ],
            ],
        ];
    }

    private function roleParams(): array
    {
        $id = Yii::$app->getRequest()->get('id');
        if ($id) {
            return [
                'ban' => $this->findModel((int)Yii::$app->getRequest()->get('id'))
            ];
        }
        return [];
    }

    /**
     * Lists all Ban models.
     * @param Request $request
     * @param User $user
     * @return string
     */
    public function actionIndex(Request $request, User $user): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/bans', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/bans', 'BREADCRUMBS_INDEX')
            ];
        }
        $searchModel = new BansSearch();
        $dataProvider = $searchModel->search($request->queryParams);
        return $this->render('index.twig', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'canViewIp' => $user->can(Permissions::PERMISSION_IP_VIEW),
            'rowOptions' =>  function(\app\models\Ban $model) {
                $options = [];
                if ($model->isUnbanned()) {
                    $options['class'] = 'table-success';
                }
                return $options;
            }
        ]);
    }

    public function actionCreate(Request $request, Session $session, User $user, ?array $reban = null)
    {
        $model = new Ban();
        if ($reban) {
            $model->load($reban, '');
        }
        if ($model->load($request->post())) {
            $model->admin_nick = $user->getName();
            if ($request->post('ajax') === 'bans-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                $session->addFlash('success', Yii::t('admin/bans', 'BAN_CREATED_SUCCESSFULLY'));
                return $this->redirect(['index']);
            }
        }
        $this->getView()->title = Yii::t('admin/bans', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            Yii::t('admin/bans', 'BREADCRUMBS_CREATE'),
        ];
        return $this->render('create.twig', [
            'model' => $model
        ]);
    }

    public function actionUpdate(int $id, User $user, Request $request, Session $session)
    {
        $model = $this->findModel($id);
        if ($model->load($request->post())) {
            $model->admin_nick = $user->getName();
            if ($request->post('ajax') === 'bans-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                $session->addFlash('success', Yii::t('admin/bans', 'BAN_UPDATED_SUCCESSFULLY'));
                return $this->redirect(['index']);
            }
        }
        $this->getView()->title = Yii::t('admin/bans', 'PAGE_TITLE_UPDATE', [
            'playerName' => $model->player_nick
        ]);
        $this->getView()->params['breadcrumbs'] = [
            Yii::t('admin/bans', 'BREADCRUMBS_UPDATE', [
                'playerName' => $model->player_nick
            ]),
        ];
        return $this->render('update.twig', [
            'model' => $model
        ]);
    }

    public function actionView(int $id, User $user, Request $request): string
    {
        $model = $this->findModel($id);
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/bans', 'PAGE_TITLE_VIEW', [
                'playerName' => $model->player_nick,
            ]);
        }
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/bans', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/bans', 'BREADCRUMBS_VIEW', [
                'playerName' => $model->player_nick,
            ])
        ];
        $service = new BanViewService($model);
        return $this->render('view.twig', ArrayHelper::merge($service->viewParams(), [
            'canViewIp' => $user->can(Permissions::PERMISSION_IP_VIEW),
            'canUnban' => !$model->isUnbanned() && $user->can(Permissions::PERMISSION_BANS_UNBAN, [
                'ban' => $model
            ]),
            'canDelete' => $user->can(Permissions::PERMISSION_BANS_DELETE, [
                'ban' => $model
            ]),
            'canUpdate' => $user->can(Permissions::PERMISSION_BANS_EDIT, [
                'ban' => $model
            ])
        ]));
    }

    public function actionUnban(int $id, Session  $session): Response
    {
        $result = $this->findModel($id)->unban();
        if ($result) {
            $session->setFlash('success', Yii::t('admin/bans', 'SUCCESSFULLY_UNBANNED'));
        } else {
            $session->setFlash('warning', Yii::t('admin/bans', 'UNBAN_ERROR'));
        }
        return $this->redirect(['index']);
    }

    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }

    /**
     * @param int $id
     * @return Ban
     * @throws NotFoundHttpException
     */
    private function findModel(int $id): Ban
    {
        if ($this->modelCache === null) {
            $this->modelCache = Ban::findOne($id);
            if (!$this->modelCache) {
                throw new NotFoundHttpException();
            }
        }
        return $this->modelCache;
    }
}
