<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%amxadmins}}".
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $password
 * @property string|null $access
 * @property string|null $flags
 * @property string|null $steamid
 * @property string|null $nickname
 * @property int|null $icq
 * @property int $ashow
 * @property int|null $created
 * @property int|null $expired
 * @property int|null $days
 *
 * @property-read AdminsServer[] $adminsServers
 * @property-read Server[] $servers
 * @property-read null|string $viewExpired
 * @property-read array $viewAccessFlags
 * @property-read null $accessFlags
 * @property-read string $viewAccountType
 */
class AmxAdmin extends \yii\db\ActiveRecord
{
    protected const ACCOUNT_FLAG_NICK = 'a';
    protected const ACCOUNT_FLAG_STEAMID = 'c';
    protected const ACCOUNT_FLAG_IP = 'd';

    private const ACCOUNT_FLAGS = [
        self::ACCOUNT_FLAG_NICK => 'ACCOUNT_FLAG_NICK',
        self::ACCOUNT_FLAG_STEAMID => 'ACCOUNT_FLAG_STEAMID',
        self::ACCOUNT_FLAG_IP => 'ACCOUNT_FLAG_IP',
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%amxadmins}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('amxAdmins', 'ATTRIBUTE_ID'),
            'username' => Yii::t('amxAdmins', 'ATTRIBUTE_USERNAME'),
            'password' => Yii::t('amxAdmins', 'ATTRIBUTE_PASSWORD'),
            'access' => Yii::t('amxAdmins', 'ATTRIBUTE_ACCESS'),
            'flags' => Yii::t('amxAdmins', 'ATTRIBUTE_FLAGS'),
            'steamid' => Yii::t('amxAdmins', 'ATTRIBUTE_STEAMID'),
            'nickname' => Yii::t('amxAdmins', 'ATTRIBUTE_NICKNAME'),
            'icq' => Yii::t('amxAdmins', 'ATTRIBUTE_ICQ'),
            'ashow' => Yii::t('amxAdmins', 'ATTRIBUTE_ASHOW'),
            'created' => Yii::t('amxAdmins', 'ATTRIBUTE_CREATED'),
            'expired' => Yii::t('amxAdmins', 'ATTRIBUTE_EXPIRED'),
            'viewExpired' => Yii::t('amxAdmins', 'ATTRIBUTE_EXPIRED'),
            'days' => Yii::t('amxAdmins', 'ATTRIBUTE_DAYS'),
            'accountType' => \Yii::t('amxAdmins', 'ATTRIBUTE_ACCOUNT_TYPE'),
            'accessFlags' => \Yii::t('amxAdmins', 'ATTRIBUTE_ACCESS_FLAGS'),
            'forever' => \Yii::t('amxAdmins', 'ATTRIBUTE_FOREVER'),
            'expiredDate' => \Yii::t('amxAdmins', 'ATTRIBUTE_EXPIRED_DATE'),
            'serversList' => \Yii::t('amxAdmins', 'ATTRIBUTE_SERVERS_LIST'),
        ];
    }

    /**
     * Gets query for [[AdminsServers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdminsServers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(AdminsServer::class, ['admin_id' => 'id']);
    }
    /**
     * Gets query for [[Servers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getServers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Serverinfo::class, ['id' => 'server_id'])->via('adminsServers');
    }

    public function accountFlags(): array
    {
        $flags = [];
        foreach (self::ACCOUNT_FLAGS as $flag => $key) {
            $flags[$flag] = Yii::t('amxAdmins', $key);
        }
        return $flags;
    }

    public function getViewAccountType(): string
    {
        $flagsArray = str_split($this->flags);
        sort($flagsArray);
        $type = self::ACCOUNT_FLAGS[$flagsArray[0]];
        if (in_array('e', $flagsArray)) {
            $type .= '_WITH_PASSWORD';
        }
        return Yii::t('amxAdmins', $type);
    }

    public function getAccessFlags()
    {
        if (!$this->access) {
            return null;
        }
        $flagsArray = str_split($this->access);
        sort($flagsArray);
        return $flagsArray;
    }

    public function getViewAccessFlags(): array
    {
        $flags = [];
        $adminFlags = $this->getAccessFlags();
        if (!$adminFlags) {
            return [];
        }
        foreach (self::accessFlags() as $flag => $label) {
            if (in_array($flag, $adminFlags)) {
                $flags[$flag] = $label;
            }
        }
        return $flags;
    }

    public function getViewExpired(): ?string
    {
        if (!$this->days) {
            return Yii::t('amxAdmins', 'EXPIRED_NEVER');
        }
        $expiredDate = \DateTime::createFromFormat('U', (string)$this->expired);
        $now = new \DateTime();
        $diff = $now->diff($expiredDate);
        if ($diff->invert) {
            return Yii::t('amxAdmins', 'EXPIRED', [
                'expiredDate' => date('d.m.Y', $this->expired)
            ]);
        }
        return Yii::$app->getFormatter()->asDatetime($expiredDate);
    }

    public static function accessFlags(): array
    {
        return [
            'a' => Yii::t('amxAdmins', 'ACCESS_FLAG_A'),
            'b' => Yii::t('amxAdmins', 'ACCESS_FLAG_B'),
            'c' => Yii::t('amxAdmins', 'ACCESS_FLAG_C'),
            'd' => Yii::t('amxAdmins', 'ACCESS_FLAG_D'),
            'e' => Yii::t('amxAdmins', 'ACCESS_FLAG_E'),
            'f' => Yii::t('amxAdmins', 'ACCESS_FLAG_F'),
            'g' => Yii::t('amxAdmins', 'ACCESS_FLAG_G'),
            'h' => Yii::t('amxAdmins', 'ACCESS_FLAG_H'),
            'i' => Yii::t('amxAdmins', 'ACCESS_FLAG_I'),
            'j' => Yii::t('amxAdmins', 'ACCESS_FLAG_J'),
            'k' => Yii::t('amxAdmins', 'ACCESS_FLAG_K'),
            'l' => Yii::t('amxAdmins', 'ACCESS_FLAG_L'),
            'm' => Yii::t('amxAdmins', 'ACCESS_FLAG_M'),
            'n' => Yii::t('amxAdmins', 'ACCESS_FLAG_N'),
            'o' => Yii::t('amxAdmins', 'ACCESS_FLAG_O'),
            'p' => Yii::t('amxAdmins', 'ACCESS_FLAG_P'),
            'q' => Yii::t('amxAdmins', 'ACCESS_FLAG_Q'),
            'r' => Yii::t('amxAdmins', 'ACCESS_FLAG_R'),
            's' => Yii::t('amxAdmins', 'ACCESS_FLAG_S'),
            't' => Yii::t('amxAdmins', 'ACCESS_FLAG_T'),
            'u' => Yii::t('amxAdmins', 'ACCESS_FLAG_U'),
            'z' => Yii::t('amxAdmins', 'ACCESS_FLAG_Z'),
        ];
    }
}
