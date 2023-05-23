<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%webadmin_profile}}".
 *
 * @property int $admin_id
 * @property string|null $first_name
 * @property string|null $last_name
 * @property string|null $avatar_name
 * @property string|null $language
 *
 * @property-read Webadmin $admin
 */
class WebadminProfile extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%webadmin_profile}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'first_name' => Yii::t('webadmins', 'PROFILE_ATTRIBUTE_FIRSTNAME'),
            'last_name' => Yii::t('webadmins', 'PROFILE_ATTRIBUTE_LASTNAME'),
            'language' => Yii::t('webadmins', 'PROFILE_ATTRIBUTE_LANGUAGE'),
        ];
    }

    /**
     * Gets query for [[Admin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Webadmin::class, ['id' => 'admin_id']);
    }
}
