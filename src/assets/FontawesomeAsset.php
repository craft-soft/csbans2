<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\assets;

use yii\web\AssetBundle;

class FontawesomeAsset extends AssetBundle
{
    public $sourcePath = '@app/assets/vendor/fontawesome-free';

    public $css = [
        'css/all.min.css'
    ];
}
