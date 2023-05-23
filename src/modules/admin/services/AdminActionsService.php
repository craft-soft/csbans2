<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\services;

use app\models\Ban;
use app\models\Comment;
use app\models\File;
use yii\caching\CacheInterface;
use yii\db\Connection;
use yii\helpers\FileHelper;

class AdminActionsService
{
    private const ACTION_CLEAR_CACHE = 'clearCache';
    private const ACTION_CLEAR_BANLIST = 'clearBanlist';
    private const ACTION_OPTIMIZE_DB = 'optimizeDb';

    private CacheInterface $cache;

    private Connection $db;

    /**
     * @param CacheInterface $cache
     * @param Connection $db
     */
    public function __construct(CacheInterface $cache, Connection $db)
    {
        $this->cache = $cache;
        $this->db = $db;
    }

    public function actionExist(string $action): bool
    {
        return in_array($action, [self::ACTION_OPTIMIZE_DB, self::ACTION_CLEAR_BANLIST, self::ACTION_CLEAR_CACHE]);
    }

    public function executeAction(string $action)
    {
        if (!$this->actionExist($action)) {
            throw new \InvalidArgumentException();
        }
        return $this->$action();
    }

    private function clearCache(): bool
    {
        return $this->cache->flush();
    }

    private function clearBanlist(): bool
    {
        if (!$this->db->createCommand()->truncateTable(Ban::tableName())->execute()) {
            return false;
        }
        if (!$this->db->createCommand()->truncateTable(Comment::tableName())->execute()) {
            return false;
        }
        if ($this->db->createCommand()->truncateTable(File::tableName())->execute()) {
            FileHelper::removeDirectory(\Yii::getAlias(File::STORAGE_PATH));
            return true;
        }
        return false;
    }

    public function optimizeDb(): int
    {
        $dbName = $this->db->createCommand('SELECT DATABASE()')->queryScalar();
        $tables = $this->db->createCommand("SHOW TABLES FROM [[$dbName]] LIKE '{$this->db->tablePrefix}%'")->queryColumn();
        $tablesString = '[[' . implode(']], [[', $tables) . ']]';
        return $this->db->createCommand("OPTIMIZE TABLES $tablesString")->execute();
    }
}
