<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace tests\fixtures;

use app\modules\admin\models\Webadmin;
use yii\test\ActiveFixture;

class WebadminsFixture extends ActiveFixture
{
    public $modelClass = Webadmin::class;

    public $dataFile = '@tests/fixtures/data/webadmins.php';
}
