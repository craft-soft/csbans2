<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Connection;

include __DIR__ . '/bootstrap.php';

$config = [
    'basePath' => dirname(__DIR__, 2) . '/src',
    'vendorPath' => dirname(__DIR__, 2) . '/vendor',
    'bootstrap' => ['log', 'appParams'],
    'language' => 'en',
    'sourceLanguage' => 'en',
    'name' => 'CS:Bans 2',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
        '@console'   => '@app/console',
        '@themes' => dirname(__DIR__, 2) . '/themes',
        '@root' => dirname(__DIR__, 2),
    ],
    'container' => include __DIR__ . '/container.php',
    'components' => [
        'ipGeo' => \app\components\ipGeo\IpGeo::class,
        'appParams' => [
            'class' => \app\components\params\AppParams::class,
            'modelClass' => \app\models\AppParam::class,
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'formatter' => [
            'class' => \app\components\Formatter::class,
            'datetimeFormat' => 'php:d.m.Y H:i:s',
            'nullDisplay' => '',
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'cache' => 'cache'
        ],
        'i18n' => [
            'translations' => [
                'yii' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    'basePath' => '@yii/messages'
                ],
                'yii*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    'basePath' => '@yii/messages'
                ],
                'theme*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    'basePath' => '@theme/messages'
                ],
                'app' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    'basePath' => '@app/messages'
                ],
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'forceTranslation' => true,
                    'basePath' => '@app/messages'
                ],
            ],
        ],
        'db' => static function(): ?Connection {
            $configFile = __DIR__ . '/db.php';
            if (!is_file($configFile)) {
                return null;
            }
            $config = include $configFile;
            $port = $config['dbPort'] ?? 3306;
            $connection = new Connection();
            $connection->dsn = "mysql:host={$config['dbHost']};port=$port;dbname={$config['dbName']}";
            $connection->username = $config['dbUser'];
            $connection->password = $config['dbPassword'];
            $connection->tablePrefix = $config['dbPrefix'] ?? null;
            $connection->enableSchemaCache = true;
            unset($configFile, $config);
            return $connection;
        },
    ],
    'modules' => [
        'install' => [
            'class' => 'app\modules\install\Module',
        ],
    ],
    'params' => [
        'bsVersion' => '5.x',
    ],
];

if (file_exists(__DIR__ . '/main-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/main-local.php');
}
return $config;
