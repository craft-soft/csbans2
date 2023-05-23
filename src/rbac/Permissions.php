<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\rbac;

class Permissions
{
    public const ROLE_ADMIN = 'admin';

    public const PERMISSION_BANS_EDIT = 'bans_edit';
    public const PERMISSION_BANS_EDIT_OWN = 'bans_edit_own';
    public const PERMISSION_BANS_DELETE = 'bans_delete';
    public const PERMISSION_BANS_DELETE_OWN = 'bans_delete_own';
    public const PERMISSION_BANS_UNBAN = 'bans_unban';
    public const PERMISSION_BANS_UNBAN_OWN = 'bans_unban_own';
    public const PERMISSION_BANS_ADD = 'bans_add';
    public const PERMISSION_BANS_IMPORT = 'bans_import';
    public const PERMISSION_BANS_EXPORT = 'bans_export';
    public const PERMISSION_AMXADMINS_VIEW = 'amxadmins_view';
    public const PERMISSION_AMXADMINS_EDIT= 'amxadmins_edit';
    public const PERMISSION_WEBADMINS_VIEW = 'webadmins_view';
    public const PERMISSION_WEBADMINS_EDIT = 'webadmins_edit';
    public const PERMISSION_WEBSETTINGS_VIEW = 'websettings_view';
    public const PERMISSION_WEBSETTINGS_EDIT = 'websettings_edit';
    public const PERMISSION_PERMISSIONS_EDIT = 'permissions_edit';
    public const PERMISSION_PRUNE_DB = 'prune_db';
    public const PERMISSION_SERVERS_EDIT = 'servers_edit';
    public const PERMISSION_SERVERS_RCON = 'servers_rcon';
    public const PERMISSION_IP_VIEW = 'ip_view';
    public const PERMISSION_WEBADMIN_AUTHS_VIEW = 'webadmin_auths_view';
    public const PERMISSION_MODERATE_CONTENT = 'moderate_content';

    private static ?array $list = null;

    public static function getList(): ?array
    {
        if (self::$list === null) {
            $reflection = new \ReflectionClass(self::class);
            self::$list = [];
            foreach ($reflection->getConstants() as $name => $value) {
                [$key, ] = explode('_', $name, 2);
                $key = strtolower($key);
                if (!array_key_exists($key, self::$list)) {
                    self::$list[$key] = [];
                }
                self::$list[$key][$value] = \Yii::t('rbac', $name);
            }
        }
        return self::$list;
    }

    public static function getRoles()
    {
        return self::getList()['role'] ?? [];
    }

    public static function getPermissions()
    {
        return self::getList()['permission'] ?? [];
    }

    public static function getPermission(string $name)
    {
        return self::getPermissions()[$name] ?? null;
    }
}
