<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\Server;

/**
 * @inheritdoc
 */
class AdminsServer extends \app\models\AdminsServer
{
    public bool $enabled = false;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['admin_id', 'server_id'], 'required'],
            [['admin_id', 'server_id'], 'integer'],
            [['enabled'], 'boolean'],
            [['use_static_bantime'], 'string'],
            [['custom_flags'], 'string', 'max' => 32],
            [['customFlagsList'], 'each', 'rule' => ['in', 'range' => array_keys(AmxAdmin::accessFlags())]],
            [['admin_id', 'server_id'], 'unique', 'targetAttribute' => ['admin_id', 'server_id']],
            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => AmxAdmin::class, 'targetAttribute' => ['admin_id' => 'id']],
            [['server_id'], 'exist', 'skipOnError' => true, 'targetClass' => Server::class, 'targetAttribute' => ['server_id' => 'id']],
        ];
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

    public function setCustomFlagsList($value)
    {
        if ($value && is_array($value)) {
            $this->custom_flags = implode('', $value);
        }
    }
}
