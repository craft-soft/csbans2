<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace unit\components\formatter;

use app\components\Formatter;
use app\models\Ban;
use Yii;

class FormatterBanTimeTest extends \Codeception\Test\Unit
{
    /**
     * @var Formatter|\yii\i18n\Formatter
     */
    private $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = \Yii::$app->getFormatter();
    }

    public function testIsWork()
    {
        $this->assertInstanceOf(Formatter::class, $this->formatter);
    }

    /**
     * @depends testIsWork
     */
    public function testForeverBanTime()
    {
        $time = 0;
        $this->assertEquals(Yii::t('bans', Ban::BAN_TIMES[$time]), $this->formatter->asBanLength($time));
    }

    /**
     * @depends testIsWork
     */
    public function testStandardBanTime()
    {
        $time = 5;
        $this->assertEquals(Yii::t('bans', Ban::BAN_TIMES[$time]), $this->formatter->asBanLength($time));
    }

    /**
     * @depends testIsWork
     */
    public function testNonStandardBanTime()
    {
        $time = 123456;
        $this->assertEquals(Yii::$app->getFormatter()->asDuration($time * 60), $this->formatter->asBanLength($time));
    }

    /**
     * @depends testIsWork
     */
    public function testBanTimeIsNull()
    {
        $time = null;
        $this->assertEquals(Yii::$app->getFormatter()->nullDisplay, $this->formatter->asBanLength($time));
    }
}
