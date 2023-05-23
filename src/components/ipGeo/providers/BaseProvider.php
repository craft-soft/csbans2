<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\ipGeo\providers;

use app\components\ipGeo\IpData;

abstract class BaseProvider
{
    private string $lang;

    protected string $name = '';

    protected ?string $credentialUrl = null;

    /**
     * @param string $lang
     */
    public function __construct(string $lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return string
     */
    protected function getLang(): string
    {
        return $this->lang;
    }

    abstract public function getData(string $ip): IpData;

    public function getCredentials(): ?string
    {
        return $this->credentialUrl;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
