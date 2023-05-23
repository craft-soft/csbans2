<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

return [
    'singletons' => [
        \app\rbac\RbacService::class => function() {
            return new \app\rbac\RbacService(Yii::$app->getAuthManager());
        },
        \app\modules\admin\services\AdminActionsService::class => function() {
            return new \app\modules\admin\services\AdminActionsService(Yii::$app->getCache(), Yii::$app->getDb());
        },
        \app\components\systemInfo\SystemInfo::class => function() {
            return new \app\components\systemInfo\SystemInfo(Yii::$app->getFormatter());
        },
        \app\components\ipGeo\IpGeo::class => function() {
            return new \app\components\ipGeo\IpGeo(
                Yii::$app->language,
                Yii::$app->appParams->ip_data_provider,
            );
        },
        \app\components\server\query\OnlineServerInfo::class => function() {
            return new \app\components\server\query\OnlineServerInfo(
                Yii::$app->appParams->server_query_provider
            );
        },
        \app\components\deviceDetect\DeviceDetect::class => function() {
            return new \app\components\deviceDetect\DeviceDetect(
                new \app\components\deviceDetect\Cache(Yii::$app->getCache())
            );
        }
    ],
    'definitions' => [
        \yii\grid\GridView::class => [
            'pager' => [
                'class' => \yii\bootstrap5\LinkPager::class
            ]
        ],
    ],
];
