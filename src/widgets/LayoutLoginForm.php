<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\widgets;

use yii\base\Widget;
use app\models\forms\LoginForm;

class LayoutLoginForm extends Widget
{
    public function run()
    {
        return $this->render('layoutLoginForm.twig', [
            'model' => new LoginForm()
        ]);
    }
}
