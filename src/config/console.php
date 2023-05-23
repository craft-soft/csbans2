<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

return [
    'id' => 'csbans2-console',
    'controllerNamespace' => 'app\console\controllers',
    'controllerMap' => [
        'fixture' => [
            'class' => 'yii\faker\FixtureController',
        ],
        'migrate' => [
            'class' => \yii\console\controllers\MigrateController::class,
            'migrationPath' => [
                '@app/console/migrations',
                '@yii/rbac/migrations',
            ],
        ],
    ],
];
