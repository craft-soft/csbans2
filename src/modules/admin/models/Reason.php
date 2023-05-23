<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "{{%reasons}}".
 *
 * @property int $id
 * @property string $reason
 * @property int|null $static_bantime
 *
 * @property-read ReasonsToSet[] $reasonsToSets
 */
class Reason extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%reasons}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['reason'], 'required'],
            [['static_bantime'], 'integer'],
            [['static_bantime'], 'default', 'value' => 0],
            [['reason'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/reasons', 'ATTRIBUTE_REASON_ID'),
            'reason' => Yii::t('admin/reasons', 'ATTRIBUTE_REASON_REASON'),
            'static_bantime' => Yii::t('admin/reasons', 'ATTRIBUTE_REASON_BANTIME'),
        ];
    }
        /**
     * Gets query for [[ReasonsToSets]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReasonsToSets(): \yii\db\ActiveQuery
    {
        return $this->hasMany(ReasonsToSet::class, ['reasonid' => 'id']);
    }
}
