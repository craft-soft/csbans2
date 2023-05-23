<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo\providers;

use app\components\ipGeo\IpData;
use GeoIp2\Database\Reader;

class GeoIp2Lite extends DbIpLite
{
    protected string $name = 'GeoLite2 by MaxMind';

    protected ?string $credentialUrl = 'https://www.maxmind.com/';

    protected string $dbFIle = __DIR__ . '/data/GeoLite2-City.mmdb';
}
