<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\assets;

use yii\web\AssetBundle;

class CsBansAsset extends AssetBundle
{
    public $depends = [
        AppAsset::class
    ];

    public function getMapsImagesPath(): ?string
    {
        $path = \Yii::getAlias("$this->sourcePath/images/maps");
        if (is_dir($path)) {
            return $path;
        }
        return null;
    }

    public function getGamesIconsPath(): ?string
    {
        $path = \Yii::getAlias("$this->sourcePath/images/games");
        if (is_dir($path)) {
            return $path;
        }
        return null;
    }

    public function getOsIconsPath(): ?string
    {
        $path = \Yii::getAlias("$this->sourcePath/images/os");
        if (is_dir($path)) {
            return $path;
        }
        return null;
    }

    public function getSecureIconsPath(): ?string
    {
        $path = \Yii::getAlias("$this->sourcePath/images/secure");
        if (is_dir($path)) {
            return $path;
        }
        return null;
    }
}