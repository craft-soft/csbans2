<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\widgets\loading\assets;

use yii\web\AssetBundle;

class LoadingAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/loading/assets/src';

    public $css = [
        'css/loading.css'
    ];

    public $js = [
        'js/loading.js'
    ];
}
