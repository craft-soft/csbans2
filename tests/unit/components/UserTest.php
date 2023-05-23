<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace unit\components;

use app\components\User;
use app\models\Webadmin;
use tests\fixtures\WebadminsFixture;

class UserTest extends \Codeception\Test\Unit
{
    private ?User $user = null;

    public function _fixtures(): array
    {
        return [
            'webadmins' => WebadminsFixture::class,
        ];
    }

    protected function _before()
    {
        parent::_before();
        $this->user = \Yii::$app->getUser();
        $this->login();
    }

    protected function _after()
    {
        parent::_after();
        $this->user->logout();
    }

    public function testLogin()
    {
        $this->assertFalse($this->user->getIsGuest());
    }

    /**
     * @depends testLogin
     */
    public function testUserName()
    {
        $this->assertEquals('admin', $this->user->getName());
    }

    public function testGuestName()
    {
        $this->user->logout();
        $this->assertNull($this->user->getName());
        $this->login();
    }

    private function login()
    {
        $identity = Webadmin::findIdentity(1);
        $this->user->login($identity);
    }
}
