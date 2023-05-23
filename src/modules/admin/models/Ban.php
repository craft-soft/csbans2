<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use app\modules\admin\events\UnbanEvent;
use Yii;
use yii\db\Expression;
use yii\helpers\Html;

/**
 * @inheritdoc
 */
class Ban extends \app\models\Ban
{
    public const EVENT_UNBAN = 'unban';

    public ?string $own_ban_reason = null;

    public bool $ownBanReasonVisible = false;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $banReasonId = Html::getInputId($this, 'ban_reason');
        $banType = Html::getInputId($this, 'ban_type');
        return [
            ['player_nick', 'required', 'message' => Yii::t('admin/bans', 'VALIDATE_PLAYER_NICK_REQUIRED')],
            [['ban_created', 'ban_length', 'ban_kicks', 'expired', 'imported'], 'integer'],
            [['player_ip', 'admin_ip', 'server_ip'], 'string', 'max' => 15],
            [
                'player_ip',
                'required',
                'when' => function(Ban $ban) {
                    return $ban->ban_type === self::BAN_TYPE_IP;
                },
                'whenClient' => "function() {return $('#$banType').val() === '" . self::BAN_TYPE_IP . "'}",
                'message' => Yii::t('admin/bans', 'VALIDATE_PLAYER_IP_REQUIRED')
            ],
            [
                'player_id',
                'required',
                'when' => function(Ban $ban) {
                    return $ban->ban_type === self::BAN_TYPE_STEAM;
                },
                'whenClient' => "function() {return $('#$banType').val() === '" . self::BAN_TYPE_STEAM . "'}",
                'message' => Yii::t('admin/bans', 'VALIDATE_PLAYER_ID_REQUIRED')
            ],
            [['player_ip'], 'ip', 'ipv6' => false],
            [['player_id', 'admin_id'], 'steamid'],
            [['player_nick', 'admin_nick', 'server_name'], 'string', 'max' => 100],
            [['ban_type'], 'in', 'range' => array_keys($this->types()), 'message' => Yii::t('admin/bans', 'VALIDATE_INVALID_BAN_TYPE')],
            [['ban_reason'], 'in', 'range' => array_keys($this->reasonsList()), 'message' => Yii::t('admin/bans', 'VALIDATE_INVALID_BAN_REASON')],
            [['ban_reason', 'cs_ban_reason', 'own_ban_reason'], 'string', 'max' => 255],
            [
                ['own_ban_reason'],
                'required',
                'when' => function(Ban $model) {
                    return $model->ban_reason === 'own';
                },
                'whenClient' => "function() {return $('#$banReasonId').val() === 'own'}",
                'message' => Yii::t('admin/bans', 'VALIDATE_BAN_REASON_REQUIRED')
            ],
            ['ban_created', 'default', 'value' => time()]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'own_ban_reason' => Yii::t('admin/bans', 'ATTRIBUTE_OWN_REASON'),
            'active' => \Yii::t('admin/bans', 'ATTRIBUTE_ACTIVE')
        ]);
    }

    private static ?array $reasonsCache = null;
    public function reasonsList(): array
    {
        if (self::$reasonsCache === null) {
            self::$reasonsCache = Reason::find()
                ->orderBy(['reason' => SORT_ASC])
                ->indexBy('reason')
                ->select(new Expression('TRIM([[reason]])'))
                ->column();
        }
        $reasons = self::$reasonsCache;
        if (!$reasons) {
            $this->ownBanReasonVisible = true;
        }
        $reasons['own'] = Yii::t('admin/bans', 'BAN_FORM_OWN_REASON');
        return $reasons;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->own_ban_reason && $this->ban_reason === 'own') {
            $this->ban_reason = $this->own_ban_reason;
        }
        $this->cs_ban_reason = $this->ban_reason;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function afterFind()
    {
        parent::afterFind();
        if ($this->ban_reason && !in_array(trim($this->ban_reason), $this->reasonsList())) {
            $this->own_ban_reason = $this->ban_reason;
            $this->ban_reason = 0;
            $this->ownBanReasonVisible = true;
        }
    }

    public function banTimes(): array
    {
        $times = [];
        foreach (self::BAN_TIMES as $minutes => $label) {
            $times[$minutes] = Yii::t('bans', $label);
        }
        return $times;
    }

    public function unban(): bool
    {
        $this->trigger(self::EVENT_UNBAN, new UnbanEvent($this));
        $this->expired = 1;
        return $this->save(false);
    }
}
