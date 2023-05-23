<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Migration;
use app\models\{AppParam, Webadmin};

/**
 * Handles the creation of table `{{%configs}}`.
 */
class m230322_124502_create_configs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp(): void
    {
        $this->createTable(AppParam::tableName(), [
            'key' => $this->string(24)->notNull(),
            'type' => $this->tinyInteger(2)->unsigned()->notNull(),
            'block' => $this->string(16)->null(),
            'system' => $this->boolean()->defaultValue(false),
            'sort' => $this->tinyInteger(2)->unsigned()->defaultValue(99),
            'value_string' => $this->string()->null(),
            'value_text' => $this->text()->null(),
            'value_int' => $this->integer()->null(),
            'value_float' => $this->money()->null(),
            'value_bool' => $this->boolean()->null(),
            'label' => $this->string(64)->notNull(),
            'dropdown_options' => $this->json()->null(),
            'description' => $this->string(128)->null(),
            'after_update' => $this->json()->null(),
            'to_frontend' => $this->boolean()->defaultValue(false),
            'updated_at' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $this->addPrimaryKey('primary_key', AppParam::tableName(), 'key');
        $this->addForeignKey(
            'configs_ibfk1',
            AppParam::tableName(),
            'updated_by',
            Webadmin::tableName(),
            'id',
            'SET NULL',
            'CASCADE',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(AppParam::tableName());
    }
}
