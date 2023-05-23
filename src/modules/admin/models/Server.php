<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use Yii;

/**
 * @inheritDoc
 *
 * @property-read \yii\db\ActiveQuery $reasonsSet
 * @property-read array $allTimezones
 * @property-read array $allReasonsSets
 */
class Server extends \app\models\Server
{
    public ?string $rcon_field = null;

    private ?string $motdUrl = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['motd_delay', 'amxban_menu', 'reasons', 'timezone_fixx'], 'integer'],
            [['rcon_field'], 'string', 'max' => 24],
            [['amxban_motd'], 'string', 'max' => 64],
            [['amxban_motd'], 'default', 'value' => $this->motdUrl],
            [['reasons'], 'exist', 'skipOnError' => true, 'targetClass' => ReasonsSet::class, 'targetAttribute' => ['reasons' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/servers', 'ATTRIBUTE_ID'),
            'timestamp' => Yii::t('admin/servers', 'ATTRIBUTE_TIMESTAMP'),
            'hostname' => Yii::t('admin/servers', 'ATTRIBUTE_HOSTNAME'),
            'address' => Yii::t('admin/servers', 'ATTRIBUTE_ADDRESS'),
            'gametype' => Yii::t('admin/servers', 'ATTRIBUTE_GAMETYPE'),
            'rcon_field' => Yii::t('admin/servers', 'ATTRIBUTE_RCON'),
            'amxban_version' => Yii::t('admin/servers', 'ATTRIBUTE_AMXBANS_VERSION'),
            'amxban_motd' => Yii::t('admin/servers', 'ATTRIBUTE_AMXBANS_MOTD'),
            'motd_delay' => Yii::t('admin/servers', 'ATTRIBUTE_MOTD_DELAY'),
            'amxban_menu' => Yii::t('admin/servers', 'ATTRIBUTE_AMXBAN_MENU'),
            'reasons' => Yii::t('admin/servers', 'ATTRIBUTE_REASONS'),
            'timezone_fixx' => Yii::t('admin/servers', 'ATTRIBUTE_TIMEZONE_FIX'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->rcon_field) {
            $this->rcon = $this->rcon_field;
        }
        return true;
    }

    /**
     * Gets query for [[Reasons0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReasonsSet(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ReasonsSet::class, ['id' => 'reasons']);
    }

    public function getAllReasonsSets(): array
    {
        return ReasonsSet::find()->select('setname')->indexBy('id')->column();
    }

    public function getAllTimezones(): array
    {
        $zones = range(-12, 12);
        return array_combine($zones, $zones);
    }

    /**
     * @param string|null $motdUrl
     */
    public function setMotdUrl(?string $motdUrl): void
    {
        if (!$this->motdUrl) {
            $this->motdUrl = $motdUrl;
        }
    }
}
