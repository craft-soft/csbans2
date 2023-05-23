<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use DateTime;
use yii\db\ActiveQuery;

/**
 * This is the model class for table "{{%bans}}".
 *
 * @property int $bid
 * @property string|null $player_ip
 * @property string|null $player_id
 * @property string $player_nick
 * @property string|null $admin_ip
 * @property string|null $admin_id
 * @property string $admin_nick
 * @property string $ban_type
 * @property string|null $ban_reason
 * @property string|null $cs_ban_reason
 * @property int|null $ban_created
 * @property int|null $ban_length
 * @property string|null $server_ip
 * @property string|null $server_name
 * @property int $ban_kicks
 * @property int $expired
 * @property int $imported
 * @property-read string $viewType
 * @property-read Server $server
 * @property-read DateTime $expiredDate
 */
class Ban extends \yii\db\ActiveRecord
{
    public const BAN_TYPE_STEAM = 'S';
    public const BAN_TYPE_IP = 'SI';

    public const TYPES = [
        self::BAN_TYPE_STEAM => 'SteamID',
        self::BAN_TYPE_IP => 'IP',
    ];

    public const BAN_TIMES = [
        0 => 'BAN_LENGTH_FOREVER',
        5 => 'BAN_LENGTH_5_MIN',
        10 => 'BAN_LENGTH_10_MIN',
        15 => 'BAN_LENGTH_15_MIN',
        30 => 'BAN_LENGTH_30_MIN',
        60 => 'BAN_LENGTH_1_HOUR',
        120 => 'BAN_LENGTH_2_HOURS',
        180 => 'BAN_LENGTH_3_HOURS',
        300 => 'BAN_LENGTH_5_HOURS',
        600 => 'BAN_LENGTH_10_HOURS',
        1440 => 'BAN_LENGTH_1_DAY',
        4320 => 'BAN_LENGTH_3_DAYS',
        10080 => 'BAN_LENGTH_1_WEEK',
        20160 => 'BAN_LENGTH_2_WEEKS',
        43200 => 'BAN_LENGTH_1_MONTH',
        129600 => 'BAN_LENGTH_3_MONTHS',
        259200 => 'BAN_LENGTH_6_MONTHS',
        518400 => 'BAN_LENGTH_1_YEAR',
    ];

    public int $comments_count = 0;
    public int $files_count = 0;

    /**
     * @var mixed
     */
    public $active = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%bans}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'bid' => Yii::t('bans', 'ATTRIBUTE_ID'),
            'player_ip' => Yii::t('bans', 'ATTRIBUTE_PLAYER_IP'),
            'player_id' => Yii::t('bans', 'ATTRIBUTE_PLAYER_ID'),
            'player_nick' => Yii::t('bans', 'ATTRIBUTE_PLAYER_NICK'),
            'admin_ip' => Yii::t('bans', 'ATTRIBUTE_ADMIN_IP'),
            'admin_id' => Yii::t('bans', 'ATTRIBUTE_ADMIN_ID'),
            'admin_nick' => Yii::t('bans', 'ATTRIBUTE_ADMIN_NICK'),
            'ban_type' => Yii::t('bans', 'ATTRIBUTE_BAN_TYPE'),
            'ban_reason' => Yii::t('bans', 'ATTRIBUTE_REASON'),
            'cs_ban_reason' => Yii::t('bans', 'Cs Ban Reason'),
            'ban_created' => Yii::t('bans', 'ATTRIBUTE_CREATED'),
            'ban_length' => Yii::t('bans', 'ATTRIBUTE_LENGTH'),
            'server_ip' => Yii::t('bans', 'ATTRIBUTE_SERVER_IP'),
            'server_name' => Yii::t('bans', 'ATTRIBUTE_SERVER_NAME'),
            'ban_kicks' => Yii::t('bans', 'ATTRIBUTE_KICKS'),
            'expired' => Yii::t('bans', 'ATTRIBUTE_EXPIRED'),
            'imported' => Yii::t('bans', 'ATTRIBUTE_IMPORTED'),
            'comments_count' => Yii::t('bans', 'ATTRIBUTE_COMMENTS'),
            'files_count' => Yii::t('bans', 'ATTRIBUTE_FILES'),
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getServer(): ActiveQuery
    {
        return $this->hasOne(Server::class, ['address' => 'server_ip']);
    }

    public function types(): array
    {
        return self::TYPES;
    }

    public function getViewType(): string
    {
        return $this->types()[$this->ban_type] ?? $this->ban_type;
    }

    /**
     * Checks if the player is unbanned.
     * If the ban time has not yet expired but the expired column is set to 1, then it is unbanned from the admin panel
     * @return bool
     */
    public function isUnbanned(): bool
    {
        if ((int)$this->expired === 1) {
            return true;
        }
        $now = new DateTime();
        $expired = $this->getExpiredDate();
        $diff = $now->diff($expired);
        return (bool)$diff->invert;
    }

    public function getExpiredDate(): DateTime
    {
        // Created date to DateTime object
        $date = DateTime::createFromFormat('U', (string)$this->ban_created);
        // Add ban time minutes
        if ($this->ban_length) {
            $date->modify("+$this->ban_length minutes");
        }
        // Modify time considering server offset
        if ($this->server && $this->server->timezone_fixx) {
            $fix = (string)$this->server->timezone_fixx;
            if ($this->server->timezone_fixx > 0) {
                $fix = "+$fix";
            }
            $date->modify("$fix hours");
        }
        return clone $date;
    }

    public function getViewExpiredDate(): ?string
    {
        if ($this->expired) {
            return Yii::t('bans', 'BAN_EXPIRED_TIME_EXPIRED');
        }
        if ($this->isUnbanned()) {
            return Yii::t('bans', 'BAN_EXPIRED_TIME_UNBANNED');
        }
        return Yii::$app->getFormatter()->asDatetime($this->getExpiredDate());
    }

    public function getBanLengthView(): ?string
    {
        $length = (int)$this->ban_length;
        if (array_key_exists($length, self::BAN_TIMES)) {
            return Yii::t('bans', self::BAN_TIMES[$length]);
        }
        return Yii::$app->getFormatter()->asDuration($length * 60);
    }

    public function getServerName(): string
    {
        if (!$this->server_name) {
            return Yii::t('bans', 'SERVER_NAME_SITE');
        }
        return $this->server_name;
    }

    /**
     * @inheritDoc
     */
    public function afterFind()
    {
        parent::afterFind();
        $this->active = !$this->isUnbanned();
    }
}
