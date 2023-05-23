<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\install\models;

class Install extends \yii\base\Model
{
    private const DEFAULT_DB_PORT = 3306;
    private const DEFAULT_DB_PREFIX = 'amx_';

    public int $dbPort = self::DEFAULT_DB_PORT;
    public string $dbPrefix = self::DEFAULT_DB_PREFIX;
    public string $dbHost = '127.0.0.1';
    public string $dbUser = 'root';
    public string $dbPassword = '';
    public string $dbName = 'csbans2';
    public string $adminName = 'admin';
    public string $adminEmail = '';
    public string $adminPassword = '';

    /**
     * @inheritDoc
     */
    public function rules(): array
    {
        return [
            [['dbHost', 'dbUser', 'dbName', 'adminName', 'adminPassword', 'adminEmail'], 'required'],
            ['dbPort', 'default', 'value' => self::DEFAULT_DB_PORT],
            ['dbPrefix', 'default', 'value' => self::DEFAULT_DB_PREFIX],
            ['adminName', 'match', 'pattern' => '/^[a-z0-9_]+$/i', 'message' => \Yii::t('install', 'VALIDATE_ADMIN_NAME_INVALID')],
            ['adminEmail', 'email'],
            [['dbPassword', 'adminPassword'], 'safe'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        return [
            'dbHost' => \Yii::t('install', 'ATTRIBUTE_DB_HOST'),
            'dbPort' => \Yii::t('install', 'ATTRIBUTE_DB_PORT'),
            'dbUser' => \Yii::t('install', 'ATTRIBUTE_DB_USER'),
            'dbPassword' => \Yii::t('install', 'ATTRIBUTE_DB_PASSWORD'),
            'dbName' => \Yii::t('install', 'ATTRIBUTE_DB_NAME'),
            'dbPrefix' => \Yii::t('install', 'ATTRIBUTE_DB_PREFIX'),
            'adminName' => \Yii::t('install', 'ATTRIBUTE_ADMIN_NAME'),
            'adminPassword' => \Yii::t('install', 'ATTRIBUTE_ADMIN_PASSWORD'),
            'adminEmail' => \Yii::t('install', 'ATTRIBUTE_ADMIN_EMAIL'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function formName(): string
    {
        return '';
    }
}
