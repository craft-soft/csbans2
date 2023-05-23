<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\Ban;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%bans}}`.
 */
class m230324_105112_create_bans_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Ban::tableName(), [
            'bid' => $this->primaryKey()->unsigned(),
            'player_ip' => $this->string(15)->null(),
            'player_id' => $this->string(35)->null(),
            'player_nick' => $this->string(100)->notNull()->defaultValue('PLAYER_NICK_UNKNOWN'),
            'admin_ip' => $this->string(15)->null(),
            'admin_id' => $this->string(35)->null(),
            'admin_nick' => $this->string(100)->notNull()->defaultValue('PLAYER_NICK_UNKNOWN'),
            'ban_type' => $this->string(3)->notNull()->defaultValue('S'),
            'ban_reason' => $this->string(64)->null(),
            'cs_ban_reason' => $this->string(64)->null(),
            'ban_created' => $this->integer()->unsigned()->null(),
            'ban_length' => $this->integer()->unsigned()->null(),
            'server_ip' => $this->string(24)->null(),
            'server_name' => $this->string(100)->null(),
            'ban_kicks' => $this->smallInteger(4)->unsigned()->notNull()->defaultValue(0),
            'expired' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
            'imported' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(0),
        ]);
        $this->createIndex('bans_ind1', Ban::tableName(), 'admin_id');
        $this->createIndex('bans_ind2', Ban::tableName(), 'admin_nick');
        $this->createIndex('bans_ind3', Ban::tableName(), 'player_ip');
        $this->createIndex('bans_ind4', Ban::tableName(), 'player_id');
        $this->createIndex('bans_ind5', Ban::tableName(), 'player_nick');
        $this->createIndex('bans_ind6', Ban::tableName(), 'ban_created');
        $this->createIndex('bans_ind7', Ban::tableName(), 'server_ip');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Ban::tableName());
    }
}
