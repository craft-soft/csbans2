<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin\controllers;

use app\components\systemInfo\SystemInfo;
use app\modules\admin\services\AdminActionsService;
use yii\caching\Cache;
use yii\filters\VerbFilter;
use yii\web\{BadRequestHttpException, Controller, Request, Response, Session};

/**
 * Default controller for the `admin` module
 */
class SystemController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'version' => ['GET', 'ajax'],
                    'actions' => ['POST', 'ajax'],
                ],
            ],
        ];
    }

    /**
     * Renders the index view for the module
     * @param SystemInfo $systemInfo
     * @return string
     */
    public function actionIndex(SystemInfo $systemInfo): string
    {
        return $this->render('index.twig', [
            'siteVersion' => $systemInfo->siteVersion(),
            'variables' => $systemInfo->systemVariables(),
            'modules' => $systemInfo->systemModules(),
        ]);
    }

    public function actionVersion(SystemInfo $systemInfo, Cache $cache): Response
    {
        $cacheKey = '__app_version';
        $version = $cache->get($cacheKey);
        if ($version === false) {
            $version = $systemInfo->getVersion();
            $cache->set($cacheKey, $version, 86400);
        }
        $return = [
            'text' => \Yii::t('admin/system', 'APP_VERSION_CORRECT'),
            'type' => 'success'
        ];
        if (!$version['latest']) {
            $return = [
                'text' => \Yii::t('admin/system', 'APP_VERSION_NOT_RECEIVED'),
                'type' => 'danger'
            ];
        } else if ($version['needUpdate']) {
            $return = [
                'text' => \Yii::t('admin/system', 'APP_VERSION_NEED_UPDATE', [
                    'version' => $version['latest']
                ]),
                'type' => 'info'
            ];
        }
        return $this->asJson($return);
    }

    public function actionActions(Request $request, Session $session, AdminActionsService $adminActionsService)
    {
        $action = $request->post('action');
        if (!$adminActionsService->actionExist($action)) {
            throw new BadRequestHttpException();
        }
        $result = $this->asJson($adminActionsService->executeAction($action));
        $session->setFlash('success', \Yii::t(
            'admin/system',
            $result ? 'SYSTEM_ACTION_EXECUTED' : 'SYSTEM_ACTION_NOT_EXECUTED'
        ));
    }
}
