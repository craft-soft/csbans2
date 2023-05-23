<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query\providers;

use app\components\server\query\Info;
use xPaw\SourceQuery\SourceQuery as SourceQueryAlias;

class SourceQueryProvider extends BaseProvider
{
    private ?array $data = null;

    public function getData()
    {
        if ($this->data === null) {
            $query = new SourceQueryAlias();
            try {
                $query->Connect($this->getIp(), $this->getPort(), 1, SourceQueryAlias::GOLDSOURCE);
            } catch (\Throwable $e) {
                return null;
            }
            $info = $query->GetInfo();
            try {
                $players = $query->GetPlayers();
            } catch (\Throwable $e) {}
            try {
                $rules = $query->GetRules();
            } catch (\Throwable $e) {}
        }
    }

    public function getInfo(): Info
    {
        // TODO: Implement getInfo() method.
    }
}
