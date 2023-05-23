<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Migration;

/**
 * Handles the creation of table `{{%flagged}}`.
 */
class m230418_113728_create_flagged_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%flagged}}', [
            'fid' => $this->primaryKey()->unsigned(),
            'player_ip' => $this->string(15)->null(),
            'player_id' => $this->string(24)->null(),
            'player_nick' => $this->string(64)->notNull()->defaultValue('PLAYER_NICK_UNKNOWN'),
            'admin_ip' => $this->string(15)->null(),
            'admin_id' => $this->string(24)->null(),
            'server_ip' => $this->string(24)->null(),
            'admin_nick' => $this->string(64)->notNull()->defaultValue('PLAYER_NICK_UNKNOWN'),
            'reason' => $this->string(100)->null(),
            'created' => $this->integer(10)->unsigned()->null(),
            'length' => $this->integer(10)->unsigned()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('{{%flagged}}');
    }
}
