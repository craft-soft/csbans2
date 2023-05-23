<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\modules\admin\models\Role;
use app\rbac\{Permissions};
use app\rbac\RbacService;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\web\{Controller, NotFoundHttpException, Request, Session, User};
use yii\widgets\ActiveForm;

class RolesController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBADMINS_EDIT]
                    ],
                ]
            ],
        ];
    }

    public function actionIndex(RbacService $rbacService, Request $request): string
    {
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/roles', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/roles', 'BREADCRUMBS_INDEX')
            ];
        }
        $roles = [];
        foreach ($rbacService->getRolesWithPermissions() as $role) {
            $role['description'] = Yii::t('rbac', $role['description']);
            $roles[] = $role;
        }
        $dataProvider = new ArrayDataProvider([
            'key' => 'name',
            'allModels' => $roles,
            'modelClass' => Role::class,
            'sort' => [
                'defaultOrder' => [
                    'createdAt' => SORT_DESC,
                    'updatedAt' => SORT_DESC,
                ],
                'attributes' => [
                    'name',
                    'description',
                    'createdAt',
                    'updatedAt',
                ]
            ]
        ]);
        return $this->render('index.twig', [
            'dataProvider' => $dataProvider
        ]);
    }

    public function actionCreate(Request $request, RbacService $rbacService)
    {
        $model = new Role($rbacService);
        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'roles-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                return $this->redirect('index');
            }
        }
        $this->getView()->title = Yii::t('admin/roles', 'PAGE_TITLE_CREATE');
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/roles', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/roles', 'BREADCRUMBS_CREATE'),
        ];
        return $this->render('create.twig', [
            'model' => $model
        ]);
    }

    public function actionUpdate(string $id, RbacService $rbacService, Request $request, User $user)
    {
        $role = $rbacService->getAuthManager()->getRole($id);
        if (!$role) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $model = Role::fromRole($rbacService, $role);
        $model->setUser($user);
        if ($model->load($request->post())) {
            if ($request->post('ajax') === 'roles-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($model->save()) {
                return $this->redirect('index');
            }
        }
        $this->getView()->title = Yii::t('admin/roles', 'PAGE_TITLE_UPDATE', [
            'description' => Yii::t('rbac', $model->description),
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/roles', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            [
                'label' => Yii::t('admin/roles', 'BREADCRUMBS_VIEW', [
                    'description' => Yii::t('rbac', $model->description),
                ]),
                'url' => ['view', 'id' => $model->name]
            ],
            Yii::t('admin/roles', 'BREADCRUMBS_UPDATE', [
                'description' => Yii::t('rbac', $model->description),
            ]),
        ];
        return $this->render('update.twig', [
            'model' => $model
        ]);
    }

    public function actionView(string $id, RbacService $rbacService): string
    {
        $role = $rbacService->getAuthManager()->getRole($id);
        if (!$role) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $model = Role::fromRole($rbacService, $role);
        $this->getView()->title = Yii::t('admin/roles', 'PAGE_TITLE_VIEW', [
            'description' => $model->description,
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/roles', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/roles', 'BREADCRUMBS_VIEW', [
                'description' => Yii::t('rbac', $model->description),
            ])
        ];
        return $this->render('view.twig', [
            'model' => $model
        ]);
    }

    public function actionDelete(string $id, RbacService $rbacService, Session $session): \yii\web\Response
    {
        $role = $rbacService->getAuthManager()->getRole($id);
        if (!$role) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        }
        $model = Role::fromRole($rbacService, $role);
        if (!$rbacService->deleteRole($role)) {
            $session->setFlash('error', Yii::t('admin/roles', 'DELETE_ROLE_NOT_DELETED', [
                'description' => Yii::t('rbac', $model->description),
            ]));
        }
        return $this->redirect(['index']);
    }
}
