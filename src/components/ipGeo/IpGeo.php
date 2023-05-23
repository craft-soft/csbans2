<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo;

use app\components\ipGeo\providers\{BaseProvider, DbIpLite, GeoIp2Lite, IpApi, IpToLocation};

class IpGeo
{
    private const PROVIDER_GEO_LITE2 = 1;
    private const PROVIDER_GEO_IP2 = 2;
    private const PROVIDER_IP_TO_LOCATION = 3;

    private const PROVIDERS_MAP = [
        self::PROVIDER_GEO_LITE2 => DbIpLite::class,
        self::PROVIDER_GEO_IP2 => GeoIp2Lite::class,
        self::PROVIDER_IP_TO_LOCATION => IpToLocation::class,
    ];

    private int $providerKey;
    private string $lang;

    /**
     * @param string $lang
     * @param int $provider
     */
    public function __construct(
        string $lang,
        int $provider = self::PROVIDER_GEO_LITE2
    ) {
        $this->lang = $lang;
        $this->providerKey = $provider;
    }

    /**
     * @param string $ip
     * @return IpData
     */
    public function getData(string $ip): ?IpData
    {
        try {
            return $this->getProvider()->getData($ip);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private ?BaseProvider $provider = null;
    /**
     * @return BaseProvider
     */
    private function getProvider(): BaseProvider
    {
        if ($this->provider === null) {
            $class = self::PROVIDERS_MAP[$this->providerKey];
            $this->provider = new $class($this->lang);
        }
        return $this->provider;
    }

    public function allProviders(): array
    {
        $providers = [];
        foreach (self::PROVIDERS_MAP as $key => $class) {
            /** @var BaseProvider $class */
            $provider = new $class($this->lang);
            $providers[$key] = $provider->getName();
        }
        return $providers;
    }

    public function defaultFlag(): string
    {
        return __DIR__ . '/flags/clear.png';
    }

    public function getCredentials(): ?string
    {
        $credentialsUrl = $this->getProvider()->getCredentials();
        if (!$credentialsUrl) {
            return null;
        }
        return \Yii::t('tools', 'GEO_IP_PROVIDER_CREDENTIALS', [
            'providerUrl' => $credentialsUrl,
            'providerName' => $this->getProvider()->getName()
        ]);
    }
}
