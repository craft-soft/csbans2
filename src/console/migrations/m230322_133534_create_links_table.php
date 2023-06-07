<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use app\models\Link;
use yii\db\Migration;

/**
 * Handles the creation of table `{{%links}}`.
 */
class m230322_133534_create_links_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Link::tableName(), [
            'id' => $this->primaryKey(),
            'label' => $this->string(64)->notNull(),
            'url' => $this->string()->notNull(),
            'sort' => $this->smallInteger(2)->notNull()->defaultValue(1),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
        ]);
        $time = time();
        $columns = [
            'label',
            'url',
            'sort',
            'created_at',
            'updated_at',
        ];
        $rows = [
            [
                'MAIN',
                '/',
                1,
                $time,
                $time
            ],
            [
                'BANS',
                '/bans',
                2,
                $time,
                $time
            ],
            [
                'ADMINS',
                '/admins',
                3,
                $time,
                $time
            ],
            [
                'SERVERS',
                '/servers',
                4,
                $time,
                $time
            ],
        ];
        $this->batchInsert(Link::tableName(), $columns, $rows);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(Link::tableName());
    }
}
