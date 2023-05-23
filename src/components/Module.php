<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components;

class Module extends \yii\base\Module
{
    public function adminLinks(): array
    {
        return [];
    }
}
