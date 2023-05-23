<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\AmxAdmin;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%amxadmins}}`.
 */
class m230324_093027_create_amxadmins_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(AmxAdmin::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'username' => $this->string(32)->null(),
            'password' => $this->string(60)->null(),
            'access' => $this->string(32)->null(),
            'flags' => $this->string(32)->null(),
            'steamid' => $this->string(32)->null(),
            'nickname' => $this->string(32)->null(),
            'icq' => $this->integer()->null(),
            'ashow' => $this->tinyInteger(1)->unsigned()->notNull()->defaultValue(1),
            'created' => $this->integer()->unsigned()->null(),
            'expired' => $this->integer()->unsigned()->null(),
            'days' => $this->smallInteger()->unsigned()->null(),
        ]);
        $this->createIndex('amxadmins_ind1', AmxAdmin::tableName(), 'username');
        $this->createIndex('amxadmins_ind2', AmxAdmin::tableName(), 'steamid');
        $this->createIndex('amxadmins_ind3', AmxAdmin::tableName(), 'ashow');
        $this->createIndex('amxadmins_ind4', AmxAdmin::tableName(), 'expired');
        $this->createIndex('amxadmins_ind5', AmxAdmin::tableName(), 'days');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(AmxAdmin::tableName());
    }
}
