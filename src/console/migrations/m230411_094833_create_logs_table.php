<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\Log;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%logs}}`.
 */
class m230411_094833_create_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Log::tableName(), [
            'id' => $this->primaryKey(),
            'timestamp' => $this->integer()->unsigned()->notNull(),
            'ip' => $this->string(15)->null(),
            'username' => $this->string(32)->null(),
            'action' => $this->string(64)->null(),
            'remarks' => $this->text()->null()
        ]);
        $this->addForeignKey(
            'logs_ibfk1',
            Log::tableName(),
            'username',
            \app\models\Webadmin::tableName(),
            'username',
            'CASCADE',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Log::tableName());
    }
}
