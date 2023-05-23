<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace tests\unit\components;

use app\components\params\AppParams;
use yii\base\InvalidConfigException;

class AppParamsTest extends \Codeception\Test\Unit
{
    private ?AppParams $appParams = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->appParams = \Yii::$app->appParams;
    }

    public function testIsWork()
    {
        $this->assertInstanceOf(AppParams::class, $this->appParams);
    }

    /**
     * @depends testIsWork
     */
    public function testSetParam()
    {
        $newValue = 'My Super Site';
        $this->appParams->site_name = $newValue;
        $this->assertEquals($newValue, $this->appParams->site_name);
        $this->assertNotEquals('CSBans 2', $this->appParams->site_name);
        // Restore default value after change
        $this->appParams->site_name = 'CSBans 2';
        $this->assertEquals('CSBans 2', $this->appParams->site_name);
        $this->assertNotEquals($newValue, $this->appParams->site_name);
    }

    /**
     * @depends testIsWork
     * @depends testSetParam
     */
    public function testDefaultValues()
    {
        $this->assertEquals('CSBans 2', $this->appParams->site_name);
        $this->assertEquals('default', $this->appParams->site_theme);
        $this->assertEquals('/', $this->appParams->start_page);
        $this->assertEquals(AppParams::VALUE_ALL, $this->appParams->demo_upload_enabled);
        $this->assertEquals('dem,zip,rar,jpg,gif,png', $this->appParams->demo_file_types);
        $this->assertEquals(50, $this->appParams->bans_per_page);
        $this->assertEquals(2, $this->appParams->site_ban_period);
        $this->assertEquals('csbans', $this->appParams->site_ban_reason);
        $this->assertEquals('max offences reached', $this->appParams->max_offences_reason);
        $this->assertEquals(10, $this->appParams->max_offences);
        $this->assertEquals(AppParams::VALUE_ALL, $this->appParams->comments);
        $this->assertEquals(1, $this->appParams->ip_data_provider);
        $this->assertEquals(1, $this->appParams->server_query_provider);
        $this->assertFalse($this->appParams->hide_old_bans);
        $this->assertTrue($this->appParams->bans_view_comments_count);
        $this->assertTrue($this->appParams->bans_view_files_count);
        $this->assertTrue($this->appParams->bans_view_kicks_count);
    }

    /**
     * @depends testIsWork
     */
    public function testGetNonExistentParam()
    {
        $name = 'non_existent_param';
        $this->assertNull($this->appParams->{$name});
    }

}
