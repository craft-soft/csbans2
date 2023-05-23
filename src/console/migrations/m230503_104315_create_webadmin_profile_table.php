<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Migration;
use app\models\{Webadmin, WebadminProfile};

/**
 * Handles the creation of table `{{%webadmin_profile}}`.
 */
class m230503_104315_create_webadmin_profile_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(WebadminProfile::tableName(), [
            'admin_id' => $this->integer(),
            'first_name' => $this->string(24)->null(),
            'last_name' => $this->string(32)->null(),
            'avatar_name' => $this->string(40)->null(),
            'language' => $this->string(6)->null()
        ]);
        $this->addPrimaryKey('webadmin_profile_pk1', WebadminProfile::tableName(), 'admin_id');
        $this->addForeignKey(
            'webadmin_profile_ibfk1',
            WebadminProfile::tableName(),
            'admin_id',
            Webadmin::tableName(),
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(WebadminProfile::tableName());
    }
}
