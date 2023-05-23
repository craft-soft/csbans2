<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\install\assets;

class InstallAsset extends \yii\web\AssetBundle
{
    public $sourcePath = '@app/modules/install/assets/js';

    public $js = [
        'install.js'
    ];
}
