<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\models\forms;

use app\models\Webadmin;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read Webadmin|null $user
 *
 */
class LoginForm extends Model
{
    public ?string $username = null;
    public ?string $password = null;
    public bool $rememberMe = true;

    /**
     * @var bool|null|Webadmin
     */
    private $_user = false;

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['username', 'password'], 'required'],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        return [
            'username' => Yii::t('login', 'ATTRIBUTE_LOGIN'),
            'password' => Yii::t('login', 'ATTRIBUTE_PASSWORD'),
            'rememberMe' => Yii::t('login', 'ATTRIBUTE_REMEMBER'),
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     */
    public function validatePassword(string $attribute)
    {
        $user = $this->getUser();
        if (!$this->hasErrors()) {
            if (!$user) {
                $this->addError($attribute, Yii::t(
                    'login',
                    'INCORRECT_LOGIN_OR_PASSWORD',
                ));
            } else {
                if ($user->needSleepBeforeLogin()) {
                    $this->addError($attribute, Yii::t(
                        'login',
                        'LOGIN_BLOCKED',
                        ['duration' => Yii::$app->getFormatter()->asDuration(15 * 60)]
                    ));
                } else if (!$user->validatePassword($this->password)) {
                    $this->addError($attribute, Yii::t(
                        'login',
                        'INCORRECT_LOGIN_OR_PASSWORD',
                    ));
                    $user->incrementFails();
                }
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login(): bool
    {
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }

    /**
     * Finds user by [[username]]
     *
     * @return Webadmin|null
     */
    public function getUser(): ?\app\models\Webadmin
    {
        if ($this->_user === false) {
            $this->_user = Webadmin::find()->where([
                'username' => $this->username,
            ])->one();
        }

        return $this->_user;
    }
}
