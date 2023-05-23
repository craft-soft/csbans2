<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%admins_servers}}".
 *
 * @property int $admin_id
 * @property int $server_id
 * @property string|null $custom_flags
 * @property string|null $use_static_bantime
 *
 * @property-read Amxadmin $admin
 * @property-read null $customFlagsList
 * @property-read Server $server
 */
class AdminsServer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%admins_servers}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'admin_id' => 'Admin ID',
            'server_id' => 'Server ID',
            'custom_flags' => 'Custom Flags',
            'use_static_bantime' => 'Use Static Bantime',
        ];
    }

    /**
     * Gets query for [[Admin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin(): \yii\db\ActiveQuery
    {
        return $this->hasOne(AmxAdmin::class, ['id' => 'admin_id']);
    }
            /**
     * Gets query for [[Server]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getServer(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Server::class, ['id' => 'server_id']);
    }

    public function getViewAccessFlags(): array
    {
        $flags = [];
        $adminFlags = $this->getCustomFlagsList();
        if (!$adminFlags) {
            return [];
        }
        foreach (AmxAdmin::accessFlags() as $flag => $label) {
            if (in_array($flag, $adminFlags)) {
                $flags[$flag] = $label;
            }
        }
        return $flags;
    }

    public function getCustomFlagsList()
    {
        if (!$this->custom_flags) {
            return null;
        }
        $flagsArray = str_split($this->custom_flags);
        sort($flagsArray);
        return $flagsArray;
    }

}
