<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\Comment;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%comments}}`.
 */
class m230417_053452_create_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Comment::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'name' => $this->string(64)->notNull(),
            'comment' => $this->text()->notNull(),
            'email' => $this->string(128)->notNull(),
            'addr' => $this->string(15)->null(),
            'date' => $this->integer(10)->unsigned()->notNull(),
            'bid' => $this->integer()->unsigned()->notNull(),
            'moderated' => $this->boolean()->defaultValue(0)
        ]);
        $this->createIndex('comments_ind1', Comment::tableName(), 'bid');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Comment::tableName());
    }
}
