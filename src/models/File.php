<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\web\UploadedFile;

/**
 * This is the model class for table "{{%files}}".
 *
 * @property int $id
 * @property int $upload_time
 * @property int|null $down_count
 * @property int $bid
 * @property string $demo_file
 * @property string $demo_real
 * @property int $file_size
 * @property string|null $comment
 * @property string|null $name
 * @property string|null $email
 * @property string|null $addr
 * @property int $moderated
 *
 * @property-read Ban $ban
 */
class File extends \yii\db\ActiveRecord
{
    public const STORAGE_PATH = '@app/data/files';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%files}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('files', 'ATTRIBUTE_ID'),
            'upload_time' => Yii::t('files', 'ATTRIBUTE_UPLOAD_TIME'),
            'down_count' => Yii::t('files', 'ATTRIBUTE_DOWN_COUNT'),
            'bid' => Yii::t('files', 'ATTRIBUTE_BID'),
            'demo_file' => Yii::t('files', 'ATTRIBUTE_DEMO_FILE'),
            'demo_real' => Yii::t('files', 'ATTRIBUTE_DEMO_REAL'),
            'file_size' => Yii::t('files', 'ATTRIBUTE_FILE_SIZE'),
            'comment' => Yii::t('files', 'ATTRIBUTE_COMMENT'),
            'name' => Yii::t('files', 'ATTRIBUTE_NAME'),
            'email' => Yii::t('files', 'ATTRIBUTE_EMAIL'),
            'addr' => Yii::t('files', 'ATTRIBUTE_ADDR'),
            'date' => Yii::t('files', 'ATTRIBUTE_DATE'),
            'moderated' => Yii::t('comments', 'ATTRIBUTE_MODERATED'),
        ];
    }
        /**
     * Gets query for [[B]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBan(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Ban::class, ['bid' => 'bid']);
    }

    public function getFilePath(): string
    {
        return self::STORAGE_PATH . "/$this->demo_real";
    }
}
