<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\AdminsServer;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%admins_servers}}`.
 */
class m230327_180521_create_admins_servers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(AdminsServer::tableName(), [
            'admin_id' => $this->integer()->unsigned()->notNull(),
            'server_id' => $this->integer()->unsigned()->notNull(),
            'custom_flags' => $this->string(32)->null(),
            'use_static_bantime' => "ENUM('yes', 'no')",
        ]);
        $this->addPrimaryKey('admins_servers_pk1', AdminsServer::tableName(), ['admin_id', 'server_id']);
        $this->addForeignKey(
            'admins_servers_fk1',
            AdminsServer::tableName(),
            'admin_id',
            \app\models\AmxAdmin::tableName(),
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'admins_servers_fk2',
            AdminsServer::tableName(),
            'server_id',
            \app\models\Server::tableName(),
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
        $this->dropTable(AdminsServer::tableName());
    }
}
