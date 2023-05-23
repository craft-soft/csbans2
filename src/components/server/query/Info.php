<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query;

class Info
{
    private ?Map $map = null;

    private int $totalPlayers = 0;
    private int $maxPlayers = 0;
    private bool $online = false;
    private bool $secure = false;
    private ?string $hostname = null;
    private ?string $description = null;
    private ?string $contacts = null;
    private ?string $os = null;
    private string $game = 'cstrike';

    /**
     * @var Player[]
     */
    private array $players = [];

    /**
     * @return Map|null
     */
    public function getMap(): ?Map
    {
        return $this->map;
    }

    /**
     * @param Map $map
     * @return Info
     */
    public function setMap(Map $map): Info
    {
        $this->map = $map;
        return $this;
    }

    public function addPlayer(Player $player): Info
    {
        $this->players[] = $player;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalPlayers(): int
    {
        return $this->totalPlayers;
    }

    /**
     * @param int $totalPlayers
     * @return Info
     */
    public function setTotalPlayers(int $totalPlayers): Info
    {
        $this->totalPlayers = $totalPlayers;
        return $this;
    }

    /**
     * @return int
     */
    public function getMaxPlayers(): int
    {
        return $this->maxPlayers;
    }

    /**
     * @param int $maxPlayers
     * @return Info
     */
    public function setMaxPlayers(int $maxPlayers): Info
    {
        $this->maxPlayers = $maxPlayers;
        return $this;
    }

    /**
     * @return bool
     */
    public function isLinux(): bool
    {
        return $this->os === 'linux';
    }

    /**
     * @return bool
     */
    public function isOnline(): bool
    {
        return $this->online;
    }

    /**
     * @param bool $online
     * @return Info
     */
    public function setOnline(bool $online): Info
    {
        $this->online = $online;
        return $this;
    }

    /**
     * @return bool
     */
    public function isSecure(): bool
    {
        return $this->secure;
    }

    /**
     * @param bool $secure
     * @return Info
     */
    public function setSecure(bool $secure): Info
    {
        $this->secure = $secure;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    /**
     * @param string|null $hostname
     * @return Info
     */
    public function setHostname(?string $hostname): Info
    {
        $this->hostname = $hostname;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     * @return Info
     */
    public function setDescription(?string $description): Info
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContacts(): ?string
    {
        return $this->contacts;
    }

    /**
     * @param string|null $contacts
     * @return Info
     */
    public function setContacts(?string $contacts): Info
    {
        $this->contacts = $contacts;
        return $this;
    }

    /**
     * @return string
     */
    public function getGame(): string
    {
        return $this->game;
    }

    /**
     * @param string $game
     * @return Info
     */
    public function setGame(string $game): Info
    {
        $this->game = $game;
        return $this;
    }

    /**
     * @return array
     */
    public function getPlayers(): array
    {
        return $this->players;
    }

    /**
     * @return string|null
     */
    public function getOs(): ?string
    {
        return $this->os;
    }

    /**
     * @param string|null $os
     * @return Info
     */
    public function setOs(?string $os): Info
    {
        $this->os = $os;
        return $this;
    }

    public function toArray(): array
    {
        $result = [
            'map' => [
                'current' => null,
                'next' => null,
                'timeLeft' => null
            ],
            'onlinePlayers' => '',
            'players' => [],
        ];
        if ($this->online) {
            $result['onlinePlayers'] = "$this->totalPlayers/$this->maxPlayers";
        }
        if ($this->map) {
            $result['map']['current'] = $this->map->getCurrent();
            $result['map']['next'] = $this->map->getNext();
            $result['map']['timeLeft'] = $this->map->getTimeLeft();
        }
        foreach ($this as $key => $val) {
            if (!in_array($key, ['map', 'players'])) {
                $result[$key] = $val;
            }
        }
        foreach ($this->players as $player) {
            $result['players'][] = [
                'name' => $player->getName(),
                'score' => $player->getScore(),
                'formattedTime' => $player->getFormattedTime(),
                'time' => $player->getTime()
            ];
        }
        return $result;
    }
}
