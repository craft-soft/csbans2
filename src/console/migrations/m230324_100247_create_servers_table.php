<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\Server;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%serverinfo}}`.
 */
class m230324_100247_create_servers_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Server::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'timestamp' => $this->integer()->unsigned()->null(),
            'hostname' => $this->string(100)->null(),
            'address' => $this->string(24)->null(),
            'gametype' => $this->string(24)->null(),
            'rcon' => $this->string(24)->null(),
            'amxban_version' => $this->string(12)->null(),
            'amxban_motd' => $this->string(64)->null(),
            'motd_delay' => $this->integer()->unsigned()->notNull()->defaultValue(10),
            'amxban_menu' => $this->integer()->unsigned()->notNull()->defaultValue(1),
            'reasons' => $this->integer()->unsigned()->null(),
            'timezone_fixx' => $this->tinyInteger(3)->notNull()->defaultValue(0),
        ]);
        $this->createIndex('server_ind1', Server::tableName(), 'address');
        $this->addForeignKey(
            'servers_fk1',
            Server::tableName(),
            'reasons',
            '{{%reasons_set}}',
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
        $this->dropTable(Server::tableName());
    }
}
