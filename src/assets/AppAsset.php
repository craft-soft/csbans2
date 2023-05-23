<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\assets;

use app\components\web\View;
use yii\web\{JqueryAsset, YiiAsset};
use yii\bootstrap5\{BootstrapAsset, BootstrapPluginAsset};

class AppAsset extends CsBansAsset
{
    public $sourcePath = '@app/assets/src';

    public $js = [
        ['js/appParams.js', 'position' => View::POS_HEAD],
        'js/app.js'
    ];

    public $css = [
        'css/app.css'
    ];

    public $depends = [
        YiiAsset::class,
        JqueryAsset::class,
        BootstrapAsset::class,
        BootstrapPluginAsset::class,
        FontawesomeAsset::class
    ];
}
