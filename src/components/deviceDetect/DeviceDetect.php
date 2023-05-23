<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\deviceDetect;

use DeviceDetector\{DeviceDetector, Cache\CacheInterface};

class DeviceDetect
{
    private ?CacheInterface $cache;

    /**
     * @param CacheInterface|null $cache
     */
    public function __construct(?CacheInterface $cache = null)
    {
        $this->cache = $cache;
    }

    public function parse(string $useragent): ?Device
    {
        $dd = new DeviceDetector($useragent);
        $dd->setCache($this->cache);
        $dd->parse();
        if (!$dd->isParsed() || !$dd->getClient() || !$dd->getOs()) {
            return null;
        }
        $ddClient = $dd->getClient();
        $client = new Client($ddClient['name'] ?? null, $ddClient['version'] ?? null);
        if ($dd->isSmartphone()) {
            $client->setType(Client::TYPE_MOBILE);
        } else if ($dd->isTablet()) {
            $client->setType(Client::TYPE_TABLET);
        }
        $ddOs = $dd->getOs();
        $os = new Os(
            $ddOs['name'] ?? null,
            $ddOs['family'] ?? null,
            $ddOs['platform'] ?? null,
            $ddOs['version'] ?? null,
        );
        return new Device($client, $os);
    }
}
