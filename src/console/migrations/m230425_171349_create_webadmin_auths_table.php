<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\{Webadmin};
use app\modules\admin\models\WebadminAuth;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%webadmin_auths}}`.
 */
class m230425_171349_create_webadmin_auths_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(WebadminAuth::tableName(), [
            'id' => $this->primaryKey(),
            'admin_id' => $this->integer()->notNull(),
            'date' => $this->integer()->unsigned()->notNull(),
            'ip' => $this->string(15)->null(),
            'user_agent' => $this->string(255)->notNull(),
            'session_id' => $this->string(64)->notNull()
        ]);
        $this->addForeignKey(
            'webadmin_auth_ibfk1',
            WebadminAuth::tableName(),
            'admin_id',
            Webadmin::tableName(),
            'id',
            'CASCADE',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(WebadminAuth::tableName());
    }
}
