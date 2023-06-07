<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

/** @noinspection PhpIllegalPsrClassPathInspection */

use app\models\Webadmin;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%webadmins}}`.
 */
class m230316_092608_create_webadmins_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Webadmin::tableName(), [
            'id' => $this->primaryKey(),
            'username' => $this->string(32)->notNull()->comment('Логин'),
            'password' => $this->char(32)->notNull()->comment('Пароль'),
            'level' => $this->integer(3)->unsigned()->null()->comment('Уровень'),
            'logcode' => $this->string(64)->null()->comment('ХЗ че такое. В CsBans 1 не использовалось'),
            'email' => $this->string(64)->null()->comment('E-mail'),
            'last_action' => $this->integer(11)->unsigned()->null()->comment('Последний вход'),
            'try' => $this->tinyInteger(1)->unsigned()->defaultValue(0)->comment('Попытки входа'),
        ]);
        $this->createIndex('webadmins_ind1', Webadmin::tableName(), 'username');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Webadmin::tableName());
    }
}
