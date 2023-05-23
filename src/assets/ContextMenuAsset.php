<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class ContextMenuAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/vendor/jqueryContextMenu';

    public $css = [
        'jquery.contextMenu.min.css'
    ];

    public $js = [
        'jquery.contextMenu.min.js'
    ];

    public $depends = [
        AppAsset::class
    ];
}
