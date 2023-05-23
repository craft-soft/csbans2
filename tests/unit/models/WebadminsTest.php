<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace tests\unit\models;

use app\models\Webadmin as User;
use Faker\Factory;
use tests\fixtures\WebadminsFixture;

class WebadminsTest extends \Codeception\Test\Unit
{
    public function _fixtures(): array
    {
        return [
            'webadmins' => WebadminsFixture::class,
        ];
    }

    public function testFindUserById()
    {
        $user = User::findIdentity(1);
        verify($user)->notEmpty();
        verify($user->username)->equals('admin');

        verify(User::findIdentity(999))->empty();
    }

    /**
     * @depends testFindUserById
     */
    public function testValidateUser()
    {
        $user = User::findIdentity(1);
        verify($user->validateAuthKey(md5('admind8578edf8458ce06fbc5bb76a58c5ca4')))->notEmpty();
        verify($user->validateAuthKey('test102key'))->empty();

        verify($user->validatePassword('qwerty'))->notEmpty();
        verify($user->validatePassword('123456'))->empty();
    }

    public function testAddAdminAndDelete()
    {
        $faker = Factory::create('en_US');
        $userName = $faker->userName;
        $password = $faker->password;
        $email = $faker->email;
        $lastAuth = $faker->unixTime;
        $try = $faker->randomElement([1, 2, 3, 0]);
        $user = new \app\modules\admin\models\Webadmin();
        $user->username = $userName;
        $user->level = 1;
        $user->password_input = $password;
        $user->email = $email;
        $user->last_action = $lastAuth;
        $user->try = $try;
        verify($user->save())->true();
        verify($user->errors)->empty();
        verify($user->username)->equals($userName);
        verify($user->password)->equals(md5($password));
        verify($user->email)->equals($email);
        verify($user->last_action)->equals($lastAuth);
        verify($user->try)->equals($try);
        verify($user->getAuthKey())->equals(md5($userName . md5($password)));
        verify($user->delete())->notFalse();
    }

    public function testAddAdminValidate()
    {
        $faker = Factory::create('en_US');
        $userName = $faker->randomElement(User::find()->select('username')->column());
        $user = new \app\modules\admin\models\Webadmin();

        // Test unique username
        $user->username = $userName;
        verify($user->validate(['username']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('username');

        // Test unique required
        $user->username = null;
        verify($user->validate(['username']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('username');

        // Test wrong try
        $user->try = 1000;
        verify($user->validate(['try']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('try');

        // Test try not numerical
        $user->try = 'qwerty';
        verify($user->validate(['try']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('try');

        // Test email is not email
        $user->email = 'qwerty';
        verify($user->validate(['email']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('email');

        // Test email max length
        $user->email = 'asfgsdfgsdfgsdfgsdghsdghdfjfgjhgjkgjkghjkfgjhsdghsdfgsdfgsdfgsdfgsdfgsdfgsfgsdfgsdfgsdfgsfgd@zdfgsdfg.ert';
        verify($user->validate(['email']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('email');

        // Test password_input max length
        $user->password_input = 'asfgsdfgsdfgsdfgsdghsdghdfjfgjhgjkgjkghjkfgjhsdghsdfgsdfgsdfgsdfgsdfgsdfgsfgsdfgsdfgsdfgsfgd';
        verify($user->validate(['password_input']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('password_input');

        // Test password_input min length
        $user->password_input = 'asd';
        verify($user->validate(['password_input']))->false();
        verify($user->errors)->notEmpty();
        verify($user->errors)->arrayHasKey('password_input');
    }
}
