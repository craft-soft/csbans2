<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\components\theme;

class ThemeEvent extends \yii\base\Event
{
    const EVENT_THEME_CHANGED = 'csb2_theme_changed';

    /**
     * @var Theme
     */
    public $theme;
}