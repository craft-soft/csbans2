<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\services\serverRcon;

use app\modules\admin\services\serverRcon\exceptions\BadRconPasswordException;
use app\modules\admin\services\serverRcon\exceptions\NoPasswordSetException;
use Knik\GRcon\{GRcon, GRconAbstract, Interfaces\ProtocolAdapterInterface, Protocols\GoldSourceAdapter};

class ServerRconService
{
    private const ADAPTERS = [
        'cstrike' => GoldSourceAdapter::class
    ];

    private string $rcon;
    private string $ip;
    private string $gameType;
    private int $port;

    private ?GRconAbstract $client = null;

    /**
     * @param string $ip
     * @param int $port
     * @param string $gameType
     * @param string $rcon
     */
    public function __construct(string $ip, int $port, string $gameType, string $rcon)
    {
        $this->rcon = $rcon;
        $this->gameType = $gameType;
        $this->ip = $ip;
        $this->port = $port;
    }

    public function send(string $command): ?string
    {
        $result = $this->getClient()->execute($command);
        if (stripos($result, 'No password set for this server.') !== false) {
            throw new NoPasswordSetException();
        }
        if ($result === 'Bad rcon_password.') {
            throw new BadRconPasswordException();
        }
        return $result;
    }

    private function getClient(): ?GRconAbstract
    {
        if ($this->client === null) {
            /** @var ProtocolAdapterInterface $adapterClass */
            $adapterClass = self::ADAPTERS[$this->gameType];
            $this->client = new GRcon(new $adapterClass([
                'host' => $this->ip,
                'port' => $this->port,
                'password' => $this->rcon
            ]));
        }
        return $this->client;
    }
}
