<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%webadmins}}".
 *
 * @property int $id
 * @property string $username Логин
 * @property string $password Пароль
 * @property int $level Уровень
 * @property string|null $logcode ХЗ че такое. В CsBans 1 не использовалось
 * @property string|null $email E-mail
 * @property int|null $last_action Последний вход
 * @property int|null $try Попытки входа
 *
 * @property-read string $authKey
 * @property-read WebadminProfile $profile
 */
class Webadmin extends \yii\db\ActiveRecord implements IdentityInterface
{
    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%webadmins}}';
    }

    public function validatePassword($password): bool
    {
        return md5($password) === $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('webadmins', 'ATTRIBUTE_ID'),
            'username' => Yii::t('webadmins', 'ATTRIBUTE_LOGIN'),
            'password' => Yii::t('webadmins', 'ATTRIBUTE_PASSWORD'),
            'password_input' => Yii::t('webadmins', 'ATTRIBUTE_PASSWORD'),
            'level' => Yii::t('webadmins', 'ATTRIBUTE_LEVEL'),
            'logcode' => 'ХЗ че такое. В CsBans 1 не использовалось',
            'email' => Yii::t('webadmins', 'ATTRIBUTE_EMAIL'),
            'last_action' => Yii::t('webadmins', 'ATTRIBUTE_LAST_AUTH'),
            'try' => Yii::t('webadmins', 'ATTRIBUTE_TRY'),
        ];
    }

    public function incrementFails()
    {
        $this->try++;
        $this->last_action = time();
        $this->save(false, ['try', 'last_action']);
    }

    public function resetTry()
    {
        $this->try = 0;
        $this->last_action = time();
        $this->save(false, ['try', 'last_action']);
    }

    /**
     * Хэширует пароль веб админа
     * @param string $password
     * @return $this
     */
    public function hashPassword(string $password): self
    {
        $this->password = md5($password);
        return $this;
    }

    public function loginAttemptsExhausted(): bool
    {
        // TODO: Вынести максимальное количество попыток в админку
        return $this->try >= 3;
    }

    public function needSleepBeforeLogin(): bool
    {
        return $this->loginAttemptsExhausted() && $this->last_action > (time() - 1 * 60);
    }

    /**
     * Gets query for [[Profile]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProfile(): \yii\db\ActiveQuery
    {
        return $this->hasOne(WebadminProfile::class, ['admin_id' => 'id']);
    }

    public function profile(): WebadminProfile
    {
        if (!$this->profile) {
            $profile = new WebadminProfile();
            $profile->admin_id = $this->id;
            return $profile;
        }
        return $this->profile;
    }

    /**
     * @param $id
     * @return Webadmin|array|\yii\db\ActiveRecord|null
     */
    public static function findIdentity($id)
    {
        return static::find()->where(['id' => $id])->one();
    }

    public static function findIdentityByAccessToken($token, $type = null): ?IdentityInterface
    {
        throw new NotSupportedException();
    }

    public function getId(): int
    {
        return (int)$this->id;
    }

    public function getAuthKey(): string
    {
        return md5("$this->username$this->password");
    }

    public function validateAuthKey($authKey): bool
    {
        return $authKey === $this->getAuthKey();
    }
}
