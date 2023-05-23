<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap\writeLog;

class LogData
{
    private string $modelClass;

    /**
     * @var LogDataAttribute[]
     */
    private array $data;

    /**
     * @param string $modelClass
     * @param array $data
     */
    public function __construct(string $modelClass, array $data)
    {
        $this->modelClass = $modelClass;
        $this->data = $data;
    }

    public function toArray(): ?array
    {
        $items = [];
        foreach ($this->data as $item) {
            $attributes = $item->toArray();
            if ($attributes) {
                $items[] = $item->toArray();
            }
        }
        if (!$items) {
            return null;
        }
        return [
            'modelClass' => $this->modelClass,
            'attributes' => $items
        ];
    }
}
