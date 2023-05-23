<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace tests\unit\components;

use app\components\ipGeo\IpData;
use app\components\ipGeo\IpGeo;

class IpGeoTest extends \Codeception\Test\Unit
{
    public function testValidIp()
    {
        $ipGeo = \Yii::$container->get(IpGeo::class);
        $result = $ipGeo->getData('160.95.8.238');
        $this->assertInstanceOf(IpData::class, $result);
        $this->assertEquals('Columbus', $result->getCity());
        $this->assertEquals('США', $result->getCountry());
        $this->assertEquals('us', $result->getCountryCode());
    }
    public function testValidIpAnotherLang()
    {
        \Yii::$app->language = 'en-US';
        $ipGeo = \Yii::$container->get(IpGeo::class);
        $result = $ipGeo->getData('160.95.8.238');
        $this->assertEquals('United States', $result->getCountry());
    }
    public function testInvalidIp()
    {
        $ipGeo = \Yii::$container->get(IpGeo::class);
        $result = $ipGeo->getData('Invalid IP');
        $this->assertNull($result);
    }
    public function testLocalIp()
    {
        $ipGeo = \Yii::$container->get(IpGeo::class);
        $result = $ipGeo->getData('192.168.0.1');
        $this->assertNull($result);
    }
    public function testLoopIp()
    {
        $ipGeo = \Yii::$container->get(IpGeo::class);
        $result = $ipGeo->getData('127.0.0.1');
        $this->assertNull($result);
    }
}
