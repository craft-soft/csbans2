<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\services\serverRcon;

class PlayerAction
{
    public const TYPE_BAN = 'ban';
    public const TYPE_KICK = 'kick';
    public const TYPE_MESSAGE = 'message';

    private string $type;

    private string $player;

    /**
     * If the action is a ban, then there is a reason for the ban.
     * If the action is a message, here is the text of the message
     * @var string|null
     */
    private ?string $message;

    /**
     * For "ban" action only.
     * Ban time in minutes
     * @var int
     */
    private int $length;

    /**
     * @param string $type
     * @param string $player
     * @param string|null $message
     * @param int $length
     */
    public function __construct(string $type, string $player, ?string $message = null, int $length = 0)
    {
        $this->type = $type;
        $this->player = $player;
        $this->message = $message;
        $this->length = $length;
    }

    private function isBan(): bool
    {
        return $this->type === self::TYPE_BAN;
    }

    private function isKick(): bool
    {
        return $this->type === self::TYPE_KICK;
    }

    private function isMessage(): bool
    {
        return $this->type === self::TYPE_MESSAGE;
    }

    public function toString(): string
    {
        if ($this->isBan()) {
            return "amx_ban $this->length $this->player $this->message";
        }
        if ($this->isKick()) {
            return "amx_kick $this->player";
        }
        if ($this->isMessage()) {
            return "amx_psay $this->player $this->message";
        }
        throw new \ErrorException("Invalid type $this->type");
    }
}
