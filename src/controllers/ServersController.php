<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\controllers;

use app\models\Server;
use yii\filters\VerbFilter;
use app\components\Formatter;
use app\services\stats\StatsService;
use app\components\server\query\{Info, OnlineServerInfo, ResultToView};
use yii\web\{AssetManager, Controller, NotFoundHttpException, Request, Response};

class ServersController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'online-data' => ['post', 'ajax']
                ],
            ],
        ];
    }

    /**
     * Action to get online information from the game server
     * @param OnlineServerInfo $info
     * @param Request $request
     * @param Response $response
     * @param Formatter $formatter
     * @param AssetManager $assetManager
     * @return array|null
     */
    public function actionOnlineData(
        OnlineServerInfo $info,
        Request $request,
        Response $response,
        Formatter $formatter,
        AssetManager $assetManager
    ): ?array
    {
        $ip = $request->post('ip');
        $port = (int)$request->post('port');
        $game = $request->post('game');
        $sort = $request->post('sort');
        $response->format = Response::FORMAT_JSON;
        try {
            $serverInfo = $info->getInfo($ip, $port, $game);
            $resultToView = new ResultToView($serverInfo, $formatter, $assetManager);
            return $resultToView->format($sort);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function actionIndex(): string
    {
        $service = new StatsService();
        return $this->render('index.twig', [
            'servers' => Server::find()->all(),
            'stats' => [
                'servers' => $service->servers(),
                'amxAdmins' => $service->amxAdmins(),
                'totalBans' => $service->totalBans(),
                'activeBans' => $service->activeBans(),
                'expiredBans' => $service->expiredBans(),
                'permanentBans' => $service->permanentBans(),
                'temporaryBans' => $service->temporaryBans(),
            ],
        ]);
    }
    public function actionView(int $id, Formatter $formatter, AssetManager $assetManager): string
    {
        $server = Server::findOne(['id' => $id]);
        if (!$server) {
            throw new NotFoundHttpException();
        }
        $view = new ResultToView(new Info(), $formatter, $assetManager);
        return $this->render('view.twig', [
            'server' => $server,
            'defaultMapImage' => $assetManager->publish($view->noresponseMapImage())[1]
        ]);
    }
}
