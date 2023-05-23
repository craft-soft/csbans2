<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query;

class Map
{
    private ?string $current;
    private ?string $next;
    private ?string $timeLeft;

    /**
     * @param string|null $current
     * @param string|null $next
     * @param string|null $timeLeft
     */
    public function __construct(?string $current, ?string $next, ?string $timeLeft)
    {
        $this->current = $current;
        $this->next = $next;
        $this->timeLeft = $timeLeft;
    }

    /**
     * @return string|null
     */
    public function getCurrent(): ?string
    {
        return $this->current;
    }

    /**
     * @return string|null
     */
    public function getNext(): ?string
    {
        return $this->next;
    }

    /**
     * @return string|null
     */
    public function getTimeLeft(): ?string
    {
        return $this->timeLeft;
    }
}
