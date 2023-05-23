<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace tests\unit\components;

use app\components\deviceDetect\Client;
use app\components\deviceDetect\Device;
use app\components\deviceDetect\DeviceDetect;
use app\components\deviceDetect\Os;

class DeviceDetectTest extends \Codeception\Test\Unit
{
    private ?DeviceDetect $detect = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->detect = \Yii::$container->get(DeviceDetect::class);
    }

    public function testComponentWorks()
    {
        $this->assertInstanceOf(DeviceDetect::class, $this->detect);
    }

    public function testValidUseragent()
    {
        $result = $this->detect->parse('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36');
        $this->assertNotNull($result);
        $this->assertInstanceOf(Device::class, $result);
        $this->assertInstanceOf(Os::class, $result->getOs());
        $this->assertInstanceOf(Client::class, $result->getClient());
        $this->assertEquals('Chrome', $result->getClient()->getName());
        $this->assertEquals('112.0', $result->getClient()->getVersion());
        $this->assertEquals('GNU/Linux', $result->getOs()->getName());
        $this->assertEquals('x64', $result->getOs()->getPlatform());
    }

    public function testInvalidUseragent()
    {
        $result = $this->detect->parse('Invalid useragent');
        $this->assertNull($result);
    }
}
