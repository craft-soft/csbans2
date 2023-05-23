<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

use yii\db\Migration;

/**
 * Class m230424_115340_create_motd_function
 */
class m230424_115340_create_motd_function extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $paramsTable = \app\models\AppParam::tableName();
        $serversTable = \app\models\Server::tableName();
        $sql = <<<SQL
DROP FUNCTION IF EXISTS SERVER_MOTD;
DROP TRIGGER IF EXISTS before_insert_to_serverinfo;
CREATE FUNCTION SERVER_MOTD()
 RETURNS VARCHAR(64)
 READS SQL DATA
 NOT DETERMINISTIC
 BEGIN
  DECLARE site_url VARCHAR(64);
  SELECT `value_string` INTO site_url FROM $paramsTable WHERE `key` = 'site_baseurl';
  RETURN IF(site_url IS NULL OR site_url = '', NULL, CONCAT(site_url, '/bans/motd?sid=%s&adm=%d&lang=%s'));
END;
CREATE TRIGGER before_insert_to_serverinfo
  BEFORE INSERT ON $serversTable
  FOR EACH ROW
  SET new.amxban_motd = SERVER_MOTD();
SQL;
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->getDb()->createCommand('DROP FUNCTION IF EXISTS SERVER_MOTD')->execute();
        $this->getDb()->createCommand('DROP TRIGGER IF EXISTS before_insert_to_serverinfo')->execute();
    }
}
