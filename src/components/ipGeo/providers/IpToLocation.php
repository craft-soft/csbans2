<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo\providers;

use app\components\ipGeo\exceptions\GetDataException;
use app\components\ipGeo\IpData;

class IpToLocation extends BaseProvider
{
    protected string $name = 'IP2Location LITE';

    protected ?string $credentialUrl = 'https://lite.ip2location.com';

    public function getData(string $ip): IpData
    {
        $db = new \IP2Location\Database(__DIR__ . '/data/IP2LOCATION-LITE-DB11.BIN');
        $data = $db->lookup($ip);
        foreach (['countryCode', 'countryName', 'cityName'] as $field) {
            if (empty($data[$field]) || $data[$field] === '-') {
                throw new GetDataException();
            }
        }
        $ipData = new IpData();
        $ipData->setCity($data['cityName'] ?? null)
            ->setCountry($data['countryName'] ?? null)
            ->setCountryCode($data['countryCode'] ?? null)
            ->setTimezone($data['timeZone'] ?? null)
            ->setLat($data['latitude'] ?? null)
            ->setLon($data['longitude'] ?? null)
            ->setRegionName($data['regionName'] ?? null);
        return $ipData;
    }
}
