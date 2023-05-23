<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query;

class Player
{
    private ?string $name;
    private int $score;
    private int $time;

    /**
     * @param string|null $name
     * @param int $score
     * @param int $time
     */
    public function __construct(?string $name, int $score = 0, int $time = 0)
    {
        $this->name = $name;
        $this->score = $score;
        $this->time = $time;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getScore(): int
    {
        return $this->score;
    }

    /**
     * @return float|null
     */
    public function getTime(): ?float
    {
        return $this->time;
    }

    public function getFormattedTime()
    {
        if ($this->time > 0) {
            return sprintf(
                "%02d:%02d:%02d",
                floor($this->time / 3600),
                ($this->time / 60) % 60,
                $this->time % 60
            );
        }
        return $this->time;
    }
}
