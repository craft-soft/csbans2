<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query\providers;

use app\components\server\query\Player;
use app\components\server\query\Info;

abstract class BaseProvider
{
    private string $ip;
    private int $port;
    private ?string $gameType;

    /**
     * @param string $ip
     * @param int $port
     * @param string|null $gameType
     */
    public function __construct(string $ip, int $port, ?string $gameType = null)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->gameType = $gameType;
    }

    /**
     * @return string
     */
    protected function getIp(): string
    {
        return $this->ip;
    }

    /**
     * @return int
     */
    protected function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return string|null
     */
    protected function getGameType(): ?string
    {
        return $this->gameType;
    }

    /**
     * @return Info
     */
    abstract public function getInfo(): Info;
}
