<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo\providers;

use app\components\ipGeo\IpData;
use GeoIp2\Database\Reader;

class DbIpLite extends BaseProvider
{
    protected string $name = 'Db-Ip Lite';

    protected ?string $credentialUrl = 'https://db-ip.com/';

    protected string $dbFIle = __DIR__ . '/data/dbip-city-lite.mmdb';

    public function getData(string $ip): IpData
    {
        $reader = new Reader($this->dbFIle);
        $data = $reader->city($ip);
        $ipData = new IpData();
        $ipData->setCity($data->city->names[$this->getLang()] ?? $data->city->name)
            ->setCountry($data->country->names[$this->getLang()] ?? $data->country->name)
            ->setCountryCode($data->country->isoCode)
            ->setTimezone($data->location->timeZone)
            ->setLat($data->location->latitude)
            ->setLon($data->location->longitude)
            ->setRegionName($data->mostSpecificSubdivision->names[$this->getLang()] ?? $data->mostSpecificSubdivision->name);
        return $ipData;
    }
}
