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

class FormatterServerAddressTest extends \Codeception\Test\Unit
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
        $address = '192.168.1.1:27015';
        $actual = $this->formatter->asServerAddress($address);
        $expected = '<a href="steam://connect/'.$address.'">'.$address.'</a>';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @depends testIsWork
     */
    public function testServerAddressIsNull()
    {
        $this->assertEquals(Yii::$app->getFormatter()->nullDisplay, $this->formatter->asServerAddress(null));
    }
}
