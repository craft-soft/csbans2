<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

$config = [
    'id' => 'csbans2-web',
    'layout' => 'main.twig',
    'bootstrap' => [
        'themeFactory',
        'jsUrlManager',
        'install',
        \app\bootstrap\RemoveSpaces::class,
        \app\bootstrap\BeforeRequestDispatcher::class,
        \app\bootstrap\writeLog\WriteLogDispatcher::class,
    ],
    'components' => [
        'assetManager' => [
            'class' => \yii\web\AssetManager::class,
            'linkAssets' => !YII_ENV_PROD,
            'appendTimestamp' => YII_ENV_PROD
        ],
        'themeFactory' => [
            'class' => \app\components\theme\Factory::class,
        ],
        'errorHandler' => [
            'errorAction' => 'default/error',
        ],
        'user' => [
            'class' => \app\components\User::class,
            'identityClass' => \app\models\Webadmin::class,
            'enableAutoLogin' => true,
            'loginUrl' => ['auth/login'],
        ],
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'suffix' => '',
            'rules' => [
                '' => 'default/index',
                '/' => 'default/index',
                'motd' => 'bans/motd',
                '<controller:(bans|admins|servers)>' => '<controller>/index',
                '<controller:(bans|admins|servers)>/<id[\d]+>' => '<controller>/view',
                '<controller:(bans|admins|servers|auth|tools)>/<action:[\w\d-]+>' => '<controller>/<action>',
                '<action:(error|captcha)>' => 'default/<action>',
                '<module:[\w\d-]+>' => '<module>/default/index',
                '<module:[\w\d_]+>/<controller:[\w\d-]+>' => '<module>/<controller>/index',
                '<module:[\w\d_]+>/<controller:[\w\d-]+>/<id[\d]+>' => '<module>/<controller>/view',
                '<module:[\w\d_]+>/<controller:[\w\d-]+>/<id[\d]+>/<action:[\w\d-]+>' => '<module>/<controller>/<action>',
                '<module:[\w\d_]+>/<controller:[\w\d-]+>/<action:[\w\d-]+>' => '<module>/<controller>/<action>',
                // For compatibility with CSBans 1 URLs
                [
                    'pattern' => 'motd',
                    'route' => 'bans/motd',
                    'suffix' => '.php'
                ],
                [
                    'pattern' => 'bans/index',
                    'route' => 'bans/index',
                    'suffix' => '.html'
                ],
                [
                    'pattern' => 'bans/<id:[\d]+>',
                    'route' => 'bans/view',
                    'suffix' => '.html'
                ],
                [
                    'pattern' => 'serverinfo/index',
                    'route' => 'servers/index',
                    'suffix' => '.html'
                ],
                [
                    'pattern' => 'serverinfo/<id:[\d]+>',
                    'route' => 'servers/view',
                    'suffix' => '.html'
                ],
                [
                    'pattern' => 'amaxadmins/index',
                    'route' => 'admins/index',
                    'suffix' => '.html'
                ],
            ],
        ],
        'jsUrlManager' => [
            'class' => \dmirogin\js\urlmanager\JsUrlManager::class,
        ],
        'view' => [
            'class' => \app\components\web\View::class,
            'defaultExtension' => 'twig',
            'renderers' => [
                'twig' => [
                    'class' => 'yii\twig\ViewRenderer',
                    'cachePath' => '@runtime/Twig/cache',
                    'options' => [
                        'auto_reload' => true,
                    ],
                    'globals' => [
                        'Html' => ['class' => \yii\helpers\Html::class],
                        'isDev' => YII_ENV_DEV,
                    ],
                    'uses' => [
                        'yii\bootstrap5',
                        'yii\widgets',
                        'app\widgets',
                        'RegisterJs' => \richardfan\widget\JSRegister::class,
                    ],
                    'extensions' => [
                        \yii\twig\Profile::class
                    ],
                    'filters' => [
                        'as_*' => function($filter, $value) {
                            return Yii::$app->getFormatter()->format($value, \yii\helpers\Inflector::variablize($filter));
                        },
                    ],
                    'functions' => [
                        'userCan' => function(string $permissionName) {
                            return \Yii::$app->getUser()->can(constant("\app\\rbac\Permissions::$permissionName"));
                        }
                    ]
                ],
            ],
        ],
    ],
    'modules' => [
        'noty' => [
            'class' => 'lo\modules\noty\Module',
        ],
        'admin' => [
            'class' => \app\modules\admin\Module::class,
        ],
    ],
];

if (file_exists(__DIR__ . '/web-local.php')) {
    $config = \yii\helpers\ArrayHelper::merge($config, require __DIR__ . '/web-local.php');
}
return $config;
