<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query\providers;

use app\components\server\query\Info;
use app\components\server\query\Map;
use app\components\server\query\Player;
use GameQ\GameQ;

class GameQProvider extends BaseProvider
{
    private const TYPES_MAP = [
        'cstrike' => 'cs16',
    ];

    public function getInfo(): Info
    {
        $gameq = new GameQ();
        $address = "{$this->getIp()}:{$this->getPort()}";
        $gameq->addServer([
            'type' => self::TYPES_MAP[$this->getGameType()] ?? $this->getGameType(),
            'host' => $address,
            'id' => $address
        ]);
        $gameq->setOption('timeout', 1);
        return $this->fromArray($gameq->process()[$address]);
    }

    private function fromArray(array $response): Info
    {
        $info = new Info();
        $this->setMap($info, $response);
        $this->setPlayers($info, $response);
        $info->setGame($response['game_dir'] ?? $this->getGameType())
            ->setOnline(!empty($response['gq_online']))
            ->setContacts($response['sv_contact'] ?? null)
            ->setHostname($response['gq_hostname'] ??$response['hostname'] ?? null)
            ->setSecure(!empty($response['secure']))
            ->setMaxPlayers(array_key_exists('max_players', $response) ? (int)$response['max_players'] : 0)
            ->setTotalPlayers(array_key_exists('num_players', $response) ? (int)$response['num_players'] : 0)
            ->setDescription($response['gq_gametype'] ?? $response['game_descr'] ?? null);
        if (!empty($response['os'])) {
            $info->setOs(strtolower($response['os']) === 'l' ? 'linux' : 'windows');
        }
        return $info;
    }

    private function setPlayers(Info $info, array $response): void
    {
        if (!empty($response['players']) && is_array($response['players'])) {
            foreach ($response['players'] as $player) {
                $info->addPlayer(new Player(
                    $player['name'] ?? null,
                    $player['score'] ? (int)$player['score'] : 0,
                    $player['time'] ? (int)$player['time'] : 0,
                ));
            }
        }
    }

    private function setMap(Info $info, array $response): void
    {
        $info->setMap(new Map(
            $response['map'] ?? null,
            $this->getValue($response, ['nextmap', 'amx_nextmap']),
            $this->getValue($response, ['timeleft', 'mp_timeleft', 'amx_timeleft']),
        ));
    }

    private function getValue(array $response, array $values): ?string
    {
        foreach ($values as $value) {
            if (!empty($response[$value])) {
                return $response[$value];
            }
        }
        return null;
    }
}
