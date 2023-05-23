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
 */
class Link extends \app\models\Link
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['label', 'url', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'required'],
            [['sort', 'created_at', 'updated_at', 'created_by', 'updated_by'], 'integer'],
            [['label'], 'string', 'max' => 64],
            [['url'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/links', 'ATTRIBUTE_ID'),
            'label' => Yii::t('admin/links', 'ATTRIBUTE_LABEL'),
            'url' => Yii::t('admin/links', 'ATTRIBUTE_URL'),
            'sort' => Yii::t('admin/links', 'ATTRIBUTE_SORT'),
            'created_at' => Yii::t('admin/links', 'ATTRIBUTE_CREATED_AT'),
            'updated_at' => Yii::t('admin/links', 'ATTRIBUTE_UPDATED_AT'),
            'created_by' => Yii::t('admin/links', 'ATTRIBUTE_CREATED_BY'),
            'updated_by' => Yii::t('admin/links', 'ATTRIBUTE_UPDATED_BY'),
        ];
    }
}
