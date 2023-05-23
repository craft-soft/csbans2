<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\Server;
use Yii;
use yii\base\InvalidConfigException;

/**
 * This is the model class for table "{{%reasons_set}}".
 *
 * @property int $id
 * @property string $setname
 *
 * @property-read Reason[] $reasons
 * @property-read Server[] $servers
 */
class ReasonsSet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%reasons_set}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['setname'], 'required'],
            [['setname'], 'unique'],
            [['setname'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/reasons', 'ATTRIBUTE_ID'),
            'setname' => Yii::t('admin/reasons', 'ATTRIBUTE_NAME'),
        ];
    }

    /**
     * Gets query for [[ReasonsToSets]].
     *
     * @return \yii\db\ActiveQuery
     * @throws InvalidConfigException
     */
    public function getReasons(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Reason::class, ['id' => 'reasonid'])
            ->viaTable(ReasonsToSet::tableName(), ['setid' => 'id']);
    }
}
