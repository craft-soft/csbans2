<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use app\rbac\{Permissions};
use app\rbac\RbacService;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\web\User;

/**
 * @property-read string $translatedDescription
 * @property-read array $permissionsForForm
 */
class Role extends Model
{
    private const OFF = 0;
    private const ON = 1;
    private const OWN = 2;

    /**
     * @var string|null Role name
     */
    public ?string $name = null;

    /**
     * @var string|null Role description (human-understandable)
     */
    public ?string $description = null;

    /**
     * @var int|null Role created date
     */
    public ?int $createdAt = null;

    /**
     * @var int|null Role updated date
     */
    public ?int $updatedAt = null;

    /**
     * @var int Селектор прав для редактирования банов
     */
    public int $editBansPermission =  self::OFF;

    /**
     * @var int Селектор прав для разбана банов
     */
    public int $unbanBansPermission = self::OFF;

    /**
     * @var int Селектор прав для удаления банов
     */
    public int $deleteBansPermission =  self::OFF;

    /**
     * @var array|string List of permissions for this role
     */
    public $permissions = [];

    private bool $isNew = true;

    private ?RbacService $rbacService;

    /**
     * @var \yii\rbac\Role|null
     */
    private ?\yii\rbac\Role $currentRole = null;

    private ?User $user = null;

    /**
     * @param RbacService|null $rbacService
     * @param array $config
     */
    public function __construct(?RbacService $rbacService = null, array $config = [])
    {
        parent::__construct($config);
        $this->rbacService = $rbacService;
    }

    /**
     * @param User|null $user
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['name', 'description'], 'required'],
            ['name', 'string', 'min' => 2, 'max' => 64],
            [['editBansPermission', 'unbanBansPermission', 'deleteBansPermission'], 'in', 'range' => [self::OFF, self::ON, self::OWN]],
            ['name', 'match', 'pattern' => '/^[a-z0-9_]+$/u', 'message' => \Yii::t('admin/roles', 'VALIDATE_NAME_INVALID')],
            ['description', 'string', 'min' => 2, 'max' => 64],
            ['name', 'nameValidator'],
            ['permissions', 'permissionsValidator'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        return [
            'name' => \Yii::t('admin/roles', 'ATTRIBUTE_NAME'),
            'description' => \Yii::t('admin/roles', 'ATTRIBUTE_DESCRIPTION'),
            'permissions' => \Yii::t('admin/roles', 'ATTRIBUTE_PERMISSIONS'),
            'editBansPermission' => \Yii::t('rbac', 'PERMISSION_BANS_EDIT'),
            'unbanBansPermission' => \Yii::t('rbac', 'PERMISSION_BANS_UNBAN'),
            'deleteBansPermission' => \Yii::t('rbac', 'PERMISSION_BANS_DELETE'),
            'createdAt' => \Yii::t('admin/roles', 'ATTRIBUTE_CREATED_AT'),
            'updatedAt' => \Yii::t('admin/roles', 'ATTRIBUTE_UPDATED_AT'),
        ];
    }

    /**
     * @return string
     */
    public function getTranslatedDescription(): string
    {
        return \Yii::t('rbac', $this->description);
    }

    public function permissions(): array
    {
        $permissions = [];
        foreach ($this->permissions as $permissionName) {
            $permissions[$permissionName] = Permissions::getPermission($permissionName);
        }
        return $permissions;
    }

    public function nameValidator()
    {
        if (
            $this->currentRole &&
            $this->name !== $this->currentRole->name &&
            $this->rbacService->getAuthManager()->getRole($this->name) !== null
        ) {
            $this->addError('name', \Yii::t('admin/roles', 'VALIDATE_ROLE_EXISTS', ['name' => $this->name]));
        }
    }

    public function permissionsValidator()
    {
        if ($this->isNew || !$this->user) {
            return;
        }
        // Names of all roles of the current user
        $userRoles = ArrayHelper::getColumn($this->rbacService->getAuthManager()->getRolesByUser($this->user->getId()), 'name');

        // Skip if the user is editing a role they are not a part of
        if (!in_array($this->name, $userRoles)) {
            return;
        }
        if (!in_array(Permissions::PERMISSION_WEBADMINS_EDIT, $this->permissions)) {
            $this->addError(
                'permissions',
                \Yii::t(
                    'admin/roles',
                    'VALIDATE_UNCHECK_WEBSETTINGS',
                    ['permission' => \Yii::t('rbac', 'PERMISSION_WEBADMINS_EDIT')]
                )
            );
        }
    }

    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $permissions = $this->permissions ?: [];
        if ($this->editBansPermission === self::OWN) {
            $permissions[] = Permissions::PERMISSION_BANS_EDIT_OWN;
        } else if ($this->editBansPermission === self::ON) {
            $permissions[] = Permissions::PERMISSION_BANS_EDIT;
        }
        if ($this->unbanBansPermission === self::OWN) {
            $permissions[] = Permissions::PERMISSION_BANS_UNBAN_OWN;
        } else if ($this->unbanBansPermission === self::ON) {
            $permissions[] = Permissions::PERMISSION_BANS_UNBAN;
        }
        if ($this->deleteBansPermission === self::OWN) {
            $permissions[] = Permissions::PERMISSION_BANS_DELETE_OWN;
        } else if ($this->deleteBansPermission === self::ON) {
            $permissions[] = Permissions::PERMISSION_BANS_DELETE;
        }
        try {
            if ($this->isNew) {
                $this->rbacService->createRole($this->name, $this->description, $permissions);
            } else {
                $this->rbacService->updateRole($this->currentRole->name, $this->name, $this->description, $permissions);
            }
        } catch (\Throwable $e) {
            $this->addError('name', $e->getMessage());
        }
        return true;
    }

    public function getPermissionsForForm(): array
    {
        $permissions = [];
        $bansPermissions = [
            Permissions::PERMISSION_BANS_EDIT,
            Permissions::PERMISSION_BANS_EDIT_OWN,
            Permissions::PERMISSION_BANS_UNBAN,
            Permissions::PERMISSION_BANS_UNBAN_OWN,
            Permissions::PERMISSION_BANS_DELETE,
            Permissions::PERMISSION_BANS_DELETE_OWN,
        ];
        foreach ($this->rbacService->getAuthManager()->getPermissions() as $permission) {
            if (!in_array($permission->name, $bansPermissions)) {
                $permissions[$permission->name] = \Yii::t('rbac', $permission->description);
            }
        }
        return $permissions;
    }

    public function bansPermissionsValues(): array
    {
        return [
            self::OFF => \Yii::t('admin/roles', 'VALUE_OFF'),
            self::ON => \Yii::t('admin/roles', 'VALUE_ON'),
            self::OWN => \Yii::t('admin/roles', 'VALUE_OWN'),
        ];
    }

    /**
     * Получить значение аттрибута прав банов
     * @param string $attribute
     * @return string
     */
    public function banPermissionText(string $attribute): string
    {
        return $this->bansPermissionsValues()[$this->{$attribute}];
    }

    public function getIsNew(): bool
    {
        return $this->isNew;
    }

    /**
     * Creates a new model and fill permissions
     * @param RbacService $rbacService
     * @param \yii\rbac\Role $role
     * @return Role
     */
    public static function fromRole(RbacService $rbacService, \yii\rbac\Role $role): Role
    {
        $model = new self($rbacService);
        $model->isNew = false;
        $model->currentRole = $role;
        $model->name = $role->name;
        $model->description = $role->description;
        $model->createdAt = (int)$role->createdAt;
        $model->updatedAt = (int)$role->updatedAt;
        foreach ($rbacService->getAuthManager()->getPermissionsByRole($role->name) as $permission) {
            if ($permission->name === Permissions::PERMISSION_BANS_EDIT_OWN) {
                $model->editBansPermission = self::OWN;
            } else if ($permission->name === Permissions::PERMISSION_BANS_EDIT) {
                $model->editBansPermission = self::ON;
            } else if ($permission->name === Permissions::PERMISSION_BANS_UNBAN_OWN) {
                $model->unbanBansPermission = self::OWN;
            } else if ($permission->name === Permissions::PERMISSION_BANS_UNBAN) {
                $model->unbanBansPermission = self::ON;
            } else if ($permission->name === Permissions::PERMISSION_BANS_DELETE_OWN) {
                $model->deleteBansPermission = self::OWN;
            } else if ($permission->name === Permissions::PERMISSION_BANS_DELETE) {
                $model->deleteBansPermission = self::ON;
            } else {
                $model->permissions[] = $permission->name;
            }
        }
        return $model;
    }
}
