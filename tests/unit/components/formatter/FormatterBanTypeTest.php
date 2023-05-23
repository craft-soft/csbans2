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

class FormatterBanTypeTest extends \Codeception\Test\Unit
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
    public function testCorrectValue()
    {
        $this->assertEquals(Ban::TYPES[Ban::BAN_TYPE_IP], $this->formatter->asBanType(Ban::BAN_TYPE_IP));
    }

    /**
     * @depends testIsWork
     */
    public function testIncorrectValue()
    {
        $incorrectType = 'incorrectType';
        $this->assertEquals($incorrectType, $this->formatter->asBanType($incorrectType));
    }

    /**
     * @depends testIsWork
     */
    public function testBanTypeIsNull()
    {
        $this->assertEquals(Yii::$app->getFormatter()->nullDisplay, $this->formatter->asBanType(null));
    }
}
