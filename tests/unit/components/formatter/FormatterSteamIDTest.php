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

class FormatterSteamIDTest extends \Codeception\Test\Unit
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
        $steam = 'STEAM_1:1:518021082';
        $actual = $this->formatter->asSteamid($steam);
        $expected = '<a href="https://steamcommunity.com/profiles/76561198996307893" target="_blank">'.$steam.'</a>';
        $this->assertEquals($expected, $actual);
    }

    /**
     * @depends testIsWork
     */
    public function testIncorrectValue()
    {
        $steam = 'incorrect steamId';
        $this->assertEquals($steam, $this->formatter->asSteamid($steam));
    }

    /**
     * @depends testIsWork
     */
    public function testSteamIDIsNull()
    {
        $this->assertEquals(Yii::$app->getFormatter()->nullDisplay, $this->formatter->asSteamid(null));
    }
}
