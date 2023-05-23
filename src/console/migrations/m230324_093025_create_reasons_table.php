<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Migration;
use app\modules\admin\models\{Reason, ReasonsSet, ReasonsToSet};

/**
 * Handles the creation of table `{{%reasons}}`.
 */
class m230324_093025_create_reasons_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(Reason::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'reason' => $this->string(64)->notNull(),
            'static_bantime' => $this->integer()->unsigned()->defaultValue(0)
        ]);
        $this->createTable(ReasonsSet::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'setname' => $this->string(64)->notNull()
        ]);
        $this->createTable(ReasonsToSet::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'setid' => $this->integer()->unsigned()->notNull(),
            'reasonid' => $this->integer()->unsigned()->notNull(),
        ]);
        $this->addForeignKey(
            'reasons_to_set_fk1',
            ReasonsToSet::tableName(),
            'setid',
            ReasonsSet::tableName(),
            'id',
            'CASCADE',
            'CASCADE',
        );
        $this->addForeignKey(
            'reasons_to_set_fk2',
            ReasonsToSet::tableName(),
            'reasonid',
            Reason::tableName(),
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
        $this->dropTable(ReasonsToSet::tableName());
        $this->dropTable(ReasonsSet::tableName());
        $this->dropTable(Reason::tableName());
    }
}
