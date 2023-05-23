<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%links}}".
 *
 * @property int $id
 * @property string $label
 * @property string $url
 * @property int $sort
 * @property int $created_at
 * @property int $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property-read string $translatedLabel
 */
class Link extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%links}}';
    }

    public static function getList(): array
    {
        /** @var Link[] $allLinks */
        $allLinks = static::find()->orderBy(['sort' => SORT_ASC])->all();
        $links = [];
        foreach ($allLinks as $link) {
            $links[$link->url] = Yii::t('mainMenu', $link->label);
        }
        return $links;
    }

    public function getTranslatedLabel(): string
    {
        return Yii::t('mainMenu', $this->label);
    }
}
