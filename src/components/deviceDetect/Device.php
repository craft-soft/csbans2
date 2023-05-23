<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\deviceDetect;

class Device
{
    private Client $client;
    private Os $os;

    /**
     * @param Client $client
     * @param Os $os
     */
    public function __construct(Client $client, Os $os)
    {
        $this->client = $client;
        $this->os = $os;
    }

    /**
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * @return Os
     */
    public function getOs(): Os
    {
        return $this->os;
    }
}
