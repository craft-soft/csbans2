<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%comments}}".
 *
 * @property int $id
 * @property string $name
 * @property string $comment
 * @property string $email
 * @property string|null $addr
 * @property int $date
 * @property int $bid
 * @property int $moderated
 *
 * @property-read Ban $ban
 */
class Comment extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%comments}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('comments', 'ID'),
            'name' => Yii::t('comments', 'ATTRIBUTE_NAME'),
            'comment' => Yii::t('comments', 'ATTRIBUTE_COMMENT'),
            'email' => Yii::t('comments', 'ATTRIBUTE_EMAIL'),
            'addr' => Yii::t('comments', 'ATTRIBUTE_ADDR'),
            'date' => Yii::t('comments', 'ATTRIBUTE_DATE'),
            'bid' => Yii::t('comments', 'ATTRIBUTE_BID'),
            'moderated' => Yii::t('comments', 'ATTRIBUTE_MODERATED'),
        ];
    }
        /**
     * Gets query for [[Ban]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBan(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Ban::class, ['bid' => 'bid']);
    }

}
