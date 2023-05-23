<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap\writeLog;

use app\models\Log;
use app\models\Webadmin;

class LogModel extends Log
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['timestamp'], 'required'],
            [['timestamp'], 'integer'],
            [['ip'], 'string', 'max' => 15],
            [['username'], 'string', 'max' => 32],
            [['action'], 'string', 'max' => 64],
            [['remarks'], 'string'],
            [['username'], 'exist', 'skipOnError' => true, 'targetClass' => Webadmin::class, 'targetAttribute' => ['username' => 'username']],
        ];
    }
}
