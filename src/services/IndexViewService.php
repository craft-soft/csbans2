<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\services;

use app\models\{Ban, Server};
use app\components\params\AppParams;

class IndexViewService
{
    private AppParams $appParams;
    public function __construct(AppParams $appParams)
    {
        $this->appParams = $appParams;
    }

    /**
     * @return Ban[]
     */
    public function bans(): array
    {
        $query = Ban::find();
        if ($this->appParams->hide_old_bans) {
            $query->alias('ban')->joinWith('server server');
            $query->where('([[ban]].[[expired]] = 0 AND ([[ban]].[[ban_created]] + (COALESCE([[server]].[[timezone_fixx]], 0) * 3600) + ([[ban]].[[ban_length]] * 60)) > UNIX_TIMESTAMP())');
        }
        $query->orderBy(['bid' => SORT_DESC]);
        $query->limit(10);
        return $query->all();
    }

    /**
     * @return Server[]
     */
    public function servers(): array
    {
        return Server::find()->all();
    }
}
