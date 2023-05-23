<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\widgets\loading;

use app\widgets\loading\assets\LoadingAsset;
use yii\base\Widget;

class Loading extends Widget
{
    public function init()
    {
        parent::init();
        LoadingAsset::register($this->getView());
    }

    public function run(): string
    {
        return <<<HTML
<div id="lds-roller-overlay">
    <div class="lds-roller-overlay-container">
        <div class="lds-roller">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</div>
HTML;
    }
}
