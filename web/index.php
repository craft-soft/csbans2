<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

if (getenv('IS_DEV')) {
    define('YII_DEBUG', true);
    define('YII_ENV', 'dev');
}

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = \yii\helpers\ArrayHelper::merge(
    require __DIR__ . '/../src/config/main.php',
    require __DIR__ . '/../src/config/web.php',
);

(new yii\web\Application($config))->run();
