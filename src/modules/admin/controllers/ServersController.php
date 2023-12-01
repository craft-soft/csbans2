<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\components\params\AppParams;
use app\modules\admin\{events\PlayerActionEvent, models\forms\PlayerActionForm, models\Server};
use app\modules\admin\services\serverRcon\{exceptions\BadRconPasswordException,
    exceptions\NoPasswordSetException,
    PlayerAction,
    ServerRconService,};
use app\rbac\Permissions;
use Yii;
use yii\base\Event;
use yii\data\ActiveDataProvider;
use yii\filters\{AccessControl, VerbFilter};
use yii\web\{BadRequestHttpException, Controller, NotFoundHttpException, Request, Response, Session, UrlManager, User};

/**
 * ServersController implements the CRUD actions for Server model.
 */
class ServersController extends Controller
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
                        'actions' => ['index', 'view'],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['update', 'delete'],
                        'permissions' => [Permissions::PERMISSION_SERVERS_EDIT]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['rcon-console'],
                        'permissions' => [Permissions::PERMISSION_SERVERS_RCON]
                    ],
                    [
                        'allow' => true,
                        'actions' => ['player-action'],
                        'permissions' => [Permissions::PERMISSION_SERVERS_RCON, Permissions::PERMISSION_BANS_ADD]
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

    public function actionPlayerAction(int $id, Request $request, Response $response, Session $session)
    {
        $response->format = Response::FORMAT_JSON;
        $server = $this->findModel($id);
        if (!$server->rcon) {
            throw new NotFoundHttpException();
        }
        $model = new PlayerActionForm();
        if ($model->load($request->post())) {
            if (!$model->validate()) {
                return [
                    'errors' => array_values($model->getFirstErrors())
                ];
            }
            $action = new PlayerAction($model->action, $model->player, $model->message, (int)$model->length);
            $service = new ServerRconService($server->getIp(), $server->getPort(), $server->gametype, $server->rcon);
            try {
                if ($service->send($action->toString())) {
                    $session->setFlash('success', Yii::t('admin/servers', 'ONLINE_ACTION_COMPLETED'));
                    Event::trigger(
                        PlayerActionEvent::class,
                        PlayerActionEvent::EVENT_NAME,
                        new PlayerActionEvent($model->action, $model->player, $model->message, (int)$model->length)
                    );
                    return true;
                }
                return false;
            } catch (BadRconPasswordException $e) {
                $session->setFlash('warning', Yii::t('admin/servers', 'RCON_BAD_RCON_PASSWORD'));
            } catch (NoPasswordSetException $e) {
                $session->setFlash('warning', Yii::t('admin/servers', 'RCON_NO_RCON_ON_SERVER'));
            } catch(\Throwable $e) {
                $session->setFlash('error', Yii::t('admin/servers', $e->getMessage()));
            }
            return false;
        }
        throw new BadRequestHttpException();
    }

    /**
     * Lists all Server models.
     * @param Request $request
     * @param User $user
     * @return string
     */
    public function actionIndex(Request $request, User $user): string
    {
        Yii::$app->appParams->site_baseurl;
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/servers', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/servers', 'BREADCRUMBS_INDEX')
            ];
        }
        $dataProvider = new ActiveDataProvider([
            'query' => Server::find(),
        ]);
        return $this->render('index.twig', [
            'dataProvider' => $dataProvider,
            'canUpdate' => $user->can(Permissions::PERMISSION_SERVERS_EDIT),
        ]);
    }

    /**
     * Updates an existing Server model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @param Request $request
     * @param UrlManager $urlManager
     * @return Response|string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate(int $id, Request $request, UrlManager $urlManager)
    {
        $model = $this->findModel($id);

        $model->setMotdUrl($urlManager->createAbsoluteUrl(['/bans/motd']) . '?sid=%s&adm=%d&lang=%s');

        if ($model->load($request->post()) && $model->save()) {
            return $this->redirect(['index']);
        }

        $this->getView()->title = Yii::t('admin/servers', 'PAGE_TITLE_UPDATE', [
            'name' => $model->hostname,
            'address' => $model->address,
            'id' => $model->id
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'label' => Yii::t('admin/servers', 'BREADCRUMBS_INDEX'),
                'url' => ['index']
            ],
            Yii::t('admin/servers', 'BREADCRUMBS_UPDATE', [
                'name' => $model->hostname,
                'address' => $model->address,
                'id' => $model->id
            ]),
        ];

        return $this->render('update.twig', [
            'model' => $model,
        ]);
    }

    public function actionView(int $id, User $user): string
    {
        $model = $this->findModel($id);
        $this->getView()->title = Yii::t('admin/servers', 'PAGE_TITLE_VIEW', [
            'name' => $model->hostname,
            'address' => $model->address,
        ]);
        $this->getView()->params['breadcrumbs'] = [
            [
                'url' => ['index'],
                'label' => Yii::t('admin/servers', 'BREADCRUMBS_INDEX')
            ],
            Yii::t('admin/servers', 'BREADCRUMBS_VIEW', [
                'name' => $model->hostname,
                'address' => $model->address,
            ])
        ];
        return $this->render('view.twig', [
            'model' => $model,
            'canRcon' => $user->can(Permissions::PERMISSION_SERVERS_RCON),
            'canUpdate' => $user->can(Permissions::PERMISSION_SERVERS_EDIT),
            'canActions' => $model->rcon && $user->can(Permissions::PERMISSION_BANS_ADD) && $user->can(Permissions::PERMISSION_SERVERS_RCON)
        ]);
    }

    public function actionRconConsole(int $id, Session $session, Request $request, User $user)
    {
        $model = $this->findModel($id);
        if (!$model->hasRcon()) {
            $session->setFlash('warning', Yii::t('admin/servers', 'RCON_SERVER_HAS_NOT_RCON'));
            if ($user->can(Permissions::PERMISSION_SERVERS_EDIT)) {
                return $this->redirect(['update', 'id' => $model->id]);
            }
            return $this->redirect(['view', 'id' => $model->id]);
        }
        if ($request->post('command')) {
            $rconService = new ServerRconService($model->getIp(), $model->getPort(), $model->gametype, $model->rcon);
            try {
                $result = $rconService->send($request->post('command'));
            } catch (BadRconPasswordException $e) {
                $result = Yii::t('admin/servers', 'RCON_BAD_RCON_PASSWORD');
            } catch (NoPasswordSetException $e) {
                $result = Yii::t('admin/servers', 'RCON_NO_RCON_ON_SERVER');
            } catch(\Exception $e) {
                $result = $e->getMessage();
            }
            return $this->asJson($result);
        }
        return $this->render('rcon.twig', [
            'model' => $model
        ]);
    }

    /**
     * Deletes an existing Server model.
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
     * Finds the Server model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Server the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Server
    {
        if (($model = Server::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
