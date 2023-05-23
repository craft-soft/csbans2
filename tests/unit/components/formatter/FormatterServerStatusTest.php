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

class FormatterServerStatusTest extends \Codeception\Test\Unit
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
    public function testServerOnline()
    {
        $this->assertEquals(Yii::t('servers', 'ONLINE_DATA_STATUS_ONLINE'), $this->formatter->asServerStatus(true));
    }

    /**
     * @depends testIsWork
     */
    public function testServerOffline()
    {
        $this->assertEquals(Yii::t('servers', 'ONLINE_DATA_STATUS_OFFLINE'), $this->formatter->asServerStatus(false));
    }
}
