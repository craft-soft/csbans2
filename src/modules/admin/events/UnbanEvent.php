<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\events;

use app\modules\admin\models\Ban;
use yii\base\Event;

class UnbanEvent extends Event
{
    private Ban $ban;

    /**
     * @param Ban $ban
     * @param array $config
     */
    public function __construct(Ban $ban, array $config = [])
    {
        $this->ban = $ban;
        parent::__construct($config);
    }

    /**
     * @return Ban
     */
    public function getBan(): Ban
    {
        return $this->ban;
    }
}
