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
 *
 * @property-read string $authKey
 */
class Webadmin extends \app\models\Webadmin
{
    public ?string $password_input = null;

    /**
     * @var string|array|null
     */
    public $roles;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $rules = [
            [['username', 'email'], 'required'],
            [['username'], 'unique'],
            [['level', 'try'], 'integer'],
            [['try'], 'integer', 'min' => 0, 'max' => 3],
            [['username', 'password_input'], 'string', 'max' => 32],
            [['logcode', 'email'], 'string', 'max' => 64],
            [['email'], 'email'],
            [['password_input'], 'string', 'min' => 4],
            [
                ['roles'],
                'each',
                'rule' => [
                    'in',
                    'range' => array_keys($this->allRoles())
                ],
                'message' => Yii::t('admin/webadmins', 'VALIDATE_ROLES_INCORRECT')
            ],
        ];
        if ($this->getIsNewRecord()) {
            $rules[] = ['password_input', 'required'];
        }
        return $rules;
    }

    public function attributeLabels(): array
    {
        return array_merge(parent::attributeLabels(), [
            'roles' => Yii::t('admin/webadmins', 'ATTRIBUTE_ROLES')
        ]);
    }

    public function allRoles(): array
    {
        $roles = [];
        foreach (Yii::$app->getAuthManager()->getRoles() as $role) {
            $roles[$role->name] = Yii::t('rbac', $role->description);
        }
        return $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function beforeSave($insert): bool
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }
        if ($this->password_input) {
            $this->hashPassword($this->password_input);
        }
        return true;
    }

    public function afterFind()
    {
        parent::afterFind();
        $this->roles = [];
        foreach (Yii::$app->getAuthManager()->getRolesByUser($this->id) as $role) {
            $this->roles[] = $role->name;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $am = Yii::$app->getAuthManager();
        $am->revokeAll($this->id);
        if ($this->roles) {
            $roles = [];
            foreach ($am->getRoles() as $role) {
                $roles[$role->name] = $role;
            }
            foreach ($this->roles as $roleName) {
                if (isset($roles[$roleName])) {
                    $am->assign($roles[$roleName], $this->id);
                }
            }
        }
    }
}
