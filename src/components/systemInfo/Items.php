<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\systemInfo;

class Items
{
    /**
     * @var Item[]
     */
    private array $items = [];

    public function addItem(Item $item)
    {
        $this->items[] = $item;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function hasError(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isHasError()) {
                return true;
            }
        }
        return false;
    }

    public function isCritical(): bool
    {
        foreach ($this->items as $item) {
            if ($item->isHasError() && $item->isCritical()) {
                return true;
            }
        }
        return false;
    }
}
