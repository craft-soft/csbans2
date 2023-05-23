<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\rbac;

use Yii;
use yii\base\Exception;
use app\models\Webadmin;
use app\rbac\rules\BanRule;
use yii\rbac\{DbManager, Permission, Role, Rule};

class RbacService
{
    /**
     * @var DbManager
     */
    private DbManager $manager;

    /**
     * @param DbManager $authManager
     */
    public function __construct(DbManager $authManager)
    {
        $this->manager = $authManager;
    }

    /**
     * @return DbManager
     */
    public function getAuthManager(): DbManager
    {
        return $this->manager;
    }

    /**
     * @param string $name
     * @param string|null $description
     * @param array $permissions
     * @return Role
     * @throws Exception
     */
    public function createRole(string $name, ?string $description = null, array $permissions = []): Role
    {
        $role = $this->manager->createRole($name);
        $role->description = $description;
        $this->manager->add($role);
        foreach ($permissions as $permission) {
            $this->manager->addChild($role, $this->manager->getPermission($permission));
        }
        return $role;
    }

    public function updateRole(string $oldName, string $newName, ?string $newDescription, array $permissions = []): Role
    {
        $role = $this->manager->getRole($oldName);
        $this->manager->removeChildren($role);
        $role->name = $newName;
        $role->description = $newDescription;
        $this->manager->update($oldName, $role);
        foreach ($permissions as $permission) {
            $this->manager->addChild($role, $this->manager->getPermission($permission));
        }
        return $role;
    }

    public function roleHasUsers(Role $role): bool
    {
        return (bool)$this->manager->getUserIdsByRole($role->name);
    }

    public function deleteRole(Role $role): bool
    {
        return $this->manager->remove($role);
    }

    /**
     * @param string $name
     * @param string|null $description
     * @param Rule|null $rule
     * @return Permission
     * @throws \Exception
     */
    public function createPermission(string $name, ?string $description = null, ?Rule $rule = null): Permission
    {
        $permission = $this->manager->createPermission($name);
        $permission->description = $description;
        if ($rule) {
            $permission->ruleName = $rule->name;
        }
        $this->manager->add($permission);
        return $permission;
    }

    public function addBaseRolesPermissions()
    {
        $this->manager->removeAll();
        $permissions = $this->allPermissions();
        $admin = $this->createRole(Permissions::ROLE_ADMIN, 'ROLE_ADMIN');

        foreach ($permissions as $name => $value) {
            if (strpos($name, 'PERMISSION') === 0) {
                if (in_array($value, [
                    Permissions::PERMISSION_BANS_DELETE,
                    Permissions::PERMISSION_BANS_DELETE_OWN,
                    Permissions::PERMISSION_BANS_EDIT,
                    Permissions::PERMISSION_BANS_EDIT_OWN,
                    Permissions::PERMISSION_BANS_UNBAN,
                    Permissions::PERMISSION_BANS_UNBAN_OWN,
                ])
                ) {
                    continue;
                }
                $permission = $this->createPermission($value, $name);
                $this->manager->addChild($admin, $permission);
            }
        }
        $this->addBansPermissions($admin);
    }

    private function addBansPermissions(Role $admin)
    {
        $banRule = $this->manager->getRule('banRule');
        if (!$banRule) {
            $banRule = new BanRule();
            $this->manager->add($banRule);
        }
        $bansDelete = $this->createPermission(Permissions::PERMISSION_BANS_DELETE, 'PERMISSION_BANS_DELETE');
        $bansDeleteOwn = $this->createPermission(Permissions::PERMISSION_BANS_DELETE_OWN, 'PERMISSION_BANS_DELETE_OWN', $banRule);
        $this->manager->addChild($bansDeleteOwn, $bansDelete);
        $this->manager->addChild($admin, $bansDelete);

        $bansEdit = $this->createPermission(Permissions::PERMISSION_BANS_EDIT, 'PERMISSION_BANS_EDIT');
        $bansEditOwn = $this->createPermission(Permissions::PERMISSION_BANS_EDIT_OWN, 'PERMISSION_BANS_EDIT_OWN', $banRule);
        $this->manager->addChild($bansEditOwn, $bansEdit);
        $this->manager->addChild($admin, $bansEdit);

        $bansUnban = $this->createPermission(Permissions::PERMISSION_BANS_UNBAN, 'PERMISSION_BANS_UNBAN');
        $bansUnbanOwn = $this->createPermission(Permissions::PERMISSION_BANS_UNBAN_OWN, 'PERMISSION_BANS_UNBAN_OWN', $banRule);
        $this->manager->addChild($bansUnbanOwn, $bansUnban);
        $this->manager->addChild($admin, $bansUnban);
    }

    public function getRolesWithPermissions(): array
    {
        $roles = [];
        foreach ($this->manager->getRoles() as $role) {
            $roles[] = [
                'name' => $role->name,
                'description' => $role->description,
                'usersCount' => count($this->manager->getUserIdsByRole($role->name)),
                'createdAt' => $role->createdAt,
                'updatedAt' => $role->updatedAt,
            ];
        }
        return $roles;
    }

    public function allPermissions(): ?array
    {
        $reflection = new \ReflectionClass(Permissions::class);
        return $reflection->getConstants();
    }

    public function assignRoleToAdmin(Role $role, Webadmin $webadmin): void
    {
        $this->manager->assign($role, $webadmin->getId());
    }
}
