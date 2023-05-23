<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query;

use app\components\server\query\providers\{BaseProvider, GameQProvider, SourceQueryProvider};

class OnlineServerInfo
{
    private const PROVIDER_GAMEQ = 1;
    private const PROVIDER_SOURCE_QUERY = 2;

    private const PROVIDERS = [
        self::PROVIDER_GAMEQ => GameQProvider::class,
        self::PROVIDER_SOURCE_QUERY => SourceQueryProvider::class
    ];

    private int $provider;

    /**
     * @param int $provider
     */
    public function __construct(int $provider = self::PROVIDER_GAMEQ)
    {
        $this->provider = $provider;
    }

    public function allProviders(): array
    {
        return [
            self::PROVIDER_GAMEQ => 'GameQ',
            self::PROVIDER_SOURCE_QUERY => 'SourceQuery by xPaw',
        ];
    }

    public function getInfo(string $ip, int $port, string $gameType): Info
    {
        $providerClass = self::PROVIDERS[$this->provider];
        /** @var BaseProvider $provider */
        $provider = new $providerClass($ip, $port, $gameType);
        return $provider->getInfo();
    }
}
