<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\events;

use yii\base\Event;

class PlayerActionEvent extends Event
{
    public const EVENT_NAME = 'playerOnlineActionEvent';

    /**
     * @var string Action name (ban, kick, message)
     */
    private string $action;

    /**
     * @var string The name of the player in the game
     */
    private string $playerName;

    /**
     * @var string|null For the Ban action - the reason for the ban, for the Message action - the text of the message
     */
    private ?string $message;

    /**
     * @var int Player ban time length in minutes
     */
    private int $length;

    /**
     * @param string $action
     * @param string $playerName
     * @param string|null $message
     * @param int $length
     */
    public function __construct(string $action, string $playerName, ?string $message, int $length)
    {
        $this->action = $action;
        $this->playerName = $playerName;
        $this->message = $message;
        $this->length = $length;
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * @return string
     */
    public function getPlayerName(): string
    {
        return $this->playerName;
    }

    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getLength(): int
    {
        return $this->length;
    }
}
