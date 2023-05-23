<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace tests\unit;

use app\rbac\RbacService;
use Faker\Factory;
use tests\fixtures\WebadminsFixture;
use yii\rbac\Permission;
use yii\rbac\Role;

class RbacTest extends \Codeception\Test\Unit
{
    private RbacService $service;

    public function _before()
    {
        parent::_before();
        $this->service = new RbacService(\Yii::$app->getAuthManager());
    }

    public function _fixtures(): array
    {
        return [
            'webadmins' => WebadminsFixture::class,
        ];
    }

    public function testCreatePermissionAndRole()
    {
        $faker = Factory::create();

        // Create permission test
        $permissionName = $faker->userName;
        $permissionDescription = $faker->realText;
        $permission1 = $this->service->createPermission($permissionName, $permissionDescription);
        verify($permission1)->instanceOf(Permission::class);
        verify($permission1->name)->equals($permissionName);
        verify($permission1->description)->equals($permissionDescription);

        // Create role and assign permission to role
        $roleName = $faker->userName;
        $roleDescription = $faker->realText;
        $role = $this->service->createRole($roleName, $roleDescription, [$permission1->name]);
        verify($role)->instanceOf(Role::class);
        verify($role->name)->equals($roleName);
        verify($role->description)->equals($roleDescription);


        // Check permissions in role
        $rolePermissions = array_values($this->service->getAuthManager()->getPermissionsByRole($role->name));
        verify($rolePermissions)->arrayCount(1);
        verify($rolePermissions[0])->instanceOf(get_class($permission1));
    }
}
