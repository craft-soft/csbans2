<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\assets;

use app\assets\AppAsset;
use yii\web\AssetBundle;
use yii\web\View;

class AdminAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/admin/assets/src';

    public $js = [
        'js/adminlte/adminlte' . (YII_ENV_PROD ? '.min' : '') . '.js',
        'js/overlayscrollbars.browser.es6.min.js',
    ];

    public $css = [
        'css/overlayscrollbars.min.css',
        'css/adminlte/adminlte.min.css',
        'css/admin.css'
    ];

    public $depends = [
        AppAsset::class
    ];

    public function init()
    {
        parent::init();
        if (\Yii::$app->appParams->external_ya_maps_enabled) {
            $lang = \Yii::$app->language;
            $token = \Yii::$app->appParams->external_ya_api_key;
            $yandexApi = "//api-maps.yandex.ru/2.0/?load=package.full&lang=$lang";
            if ($token) {
                $yandexApi .= "&apikey=$token";
            }
            $this->js[] = [$yandexApi, 'position' => View::POS_HEAD];
        }
        $this->js[] = 'js/admin.js';
    }
}
