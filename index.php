<?php

if(getenv('csbans2-dev')) {
    define('YII_DEBUG', true);
    define('YII_ENV', 'dev');
}

require(__DIR__ . '/app/vendor/autoload.php');
require(__DIR__ . '/app/vendor/yiisoft/yii2/Yii.php');

$config = \yii\helpers\ArrayHelper::merge(
    include __DIR__ . '/app/config/app/main.php',
    include __DIR__ . '/app/config/app/web.php'
);

(new yii\web\Application($config))->run();
