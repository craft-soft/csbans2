<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\File;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%files}}`.
 */
class m230417_050430_create_files_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(File::tableName(), [
            'id' => $this->primaryKey(),
            'upload_time' => $this->integer(10)->unsigned()->notNull(),
            'down_count' => $this->smallInteger(4)->unsigned()->defaultValue(0),
            'bid' => $this->integer()->unsigned()->notNull(),
            'demo_file' => $this->string(100)->notNull(),
            'demo_real' => $this->string(100)->notNull(),
            'file_size' => $this->integer()->unsigned()->notNull(),
            'comment' => $this->text()->null(),
            'name' => $this->string(64)->null(),
            'email' => $this->string(128)->null(),
            'addr' => $this->string(15)->null(),
            'moderated' => $this->boolean()->defaultValue(0)
        ]);
        $this->createIndex('files_ind1', File::tableName(), 'bid');
        $this->createIndex('files_ind2', File::tableName(), 'moderated');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(File::tableName());
    }
}
