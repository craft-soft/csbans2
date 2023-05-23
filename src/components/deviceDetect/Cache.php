<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\deviceDetect;

use DeviceDetector\Cache\CacheInterface;
use yii\caching\CacheInterface as YiiCacheInterface;

class Cache implements CacheInterface
{
    private YiiCacheInterface $yiiCache;

    /**
     * @param YiiCacheInterface $yiiCache
     */
    public function __construct(YiiCacheInterface $yiiCache)
    {
        $this->yiiCache = $yiiCache;
    }

    public function fetch(string $id)
    {
        return $this->yiiCache->get($id);
    }

    public function contains(string $id): bool
    {
        return (bool)$this->yiiCache->get($id);
    }

    public function save(string $id, $data, int $lifeTime = 0): bool
    {
        return $this->yiiCache->set($id, $data, $lifeTime);
    }

    public function delete(string $id): bool
    {
        return $this->yiiCache->delete($id);
    }

    public function flushAll(): bool
    {
        return $this->yiiCache->flush();
    }
}
