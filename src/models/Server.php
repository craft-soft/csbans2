<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

/**
 * This is the model class for table "{{%serverinfo}}".
 *
 * @property int $id
 * @property int|null $timestamp
 * @property string|null $hostname
 * @property string|null $address
 * @property string|null $gametype
 * @property string|null $rcon
 * @property string|null $amxban_version
 * @property string|null $amxban_motd
 * @property int $motd_delay
 * @property int $amxban_menu
 * @property int|null $reasons
 * @property int $timezone_fixx
 *
 * @property-read string $link
 * @property-read AmxAdmin[] $admins
 */
class Server extends \yii\db\ActiveRecord
{
    private ?array $parsedAddress = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%serverinfo}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('servers', 'ATTRIBUTE_ID'),
            'hostname' => Yii::t('servers', 'ATTRIBUTE_HOSTNAME'),
            'address' => Yii::t('servers', 'ATTRIBUTE_ADDRESS'),
            'gametype' => Yii::t('servers', 'ATTRIBUTE_GAMETYPE'),
        ];
    }

    /**
     * Gets query for [[Admins]].
     *
     * @return \yii\db\ActiveQuery
     * @throws InvalidConfigException
     */
    public function getAdmins(): \yii\db\ActiveQuery
    {
        return $this->hasMany(AmxAdmin::class, ['id' => 'admin_id'])
            ->viaTable(AdminsServer::tableName(), ['server_id' => 'id']);
    }

    public function hasRcon(): bool
    {
        return (bool)$this->rcon;
    }

    public function getIp(): string
    {
        return $this->parseIp()['ip'];
    }

    public function getPort(): string
    {
        return $this->parseIp()['port'];
    }

    private function parseIp(): array
    {
        if (!$this->address || !str_contains($this->address, ':')) {
            return [
                'ip' => null,
                'port' => null,
            ];
        }
        if ($this->parsedAddress === null) {
            [$ip, $port] = explode(':', $this->address);
            $this->parsedAddress = [
                'ip' => $ip,
                'port' => $port
            ];
        }
        return $this->parsedAddress;
    }
}
