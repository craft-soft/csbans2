<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "{{%reasons_to_set}}".
 *
 * @property int $id
 * @property int $setid
 * @property int $reasonid
 *
 * @property-read Reason $reason
 * @property-read ReasonsSet $set
 */
class ReasonsToSet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%reasons_to_set}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['setid', 'reasonid'], 'required'],
            [['setid', 'reasonid'], 'integer'],
            [['setid'], 'exist', 'skipOnError' => true, 'targetClass' => ReasonsSet::class, 'targetAttribute' => ['setid' => 'id']],
            [['reasonid'], 'exist', 'skipOnError' => true, 'targetClass' => Reason::class, 'targetAttribute' => ['reasonid' => 'id']],
        ];
    }

    /**
     * Gets query for [[Reason]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getReason(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Reason::class, ['id' => 'reasonid']);
    }

    /**
     * Gets query for [[Set]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSet(): \yii\db\ActiveQuery
    {
        return $this->hasOne(ReasonsSet::class, ['id' => 'setid']);
    }
}
