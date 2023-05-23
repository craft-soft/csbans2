<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\deviceDetect;

class Os
{
    private ?string $name;
    private ?string $platform;
    private ?string $family;
    private ?string $version;

    /**
     * @param string|null $name
     * @param string|null $family
     * @param string|null $platform
     * @param string|null $version
     */
    public function __construct(?string $name, ?string $family, ?string $platform, ?string $version)
    {
        $this->name = $name;
        $this->family = $family;
        $this->platform = $platform;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    public function getFullName(): string
    {
        $parts = [
            $this->name,
            $this->version,
            $this->platform
        ];
        return implode(' ', array_filter($parts));
    }

    /**
     * @return string
     */
    public function getPlatform(): string
    {
        return $this->platform;
    }

    /**
     * @return string|null
     */
    public function getFamily(): ?string
    {
        return $this->family;
    }

    /**
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getIcon(): string
    {
        if (stripos($this->family, 'linux') !== false) {
            return $this->getLinuxIcon();
        }
        if (stripos($this->family, 'windows') !== false) {
            return '<i class="fab fa-windows"></i>';
        }
        if (preg_match('/(mac|ios)/i', $this->platform)) {
            return '<i class="fab fa-apple"></i>';
        }
        return '';
    }

    private function getLinuxIcon(): string
    {
        if (stripos($this->name, 'debian')) {
            $icon = 'debian';
        } else if (stripos($this->name, 'ubuntu')) {
            $icon = 'ubuntu';
        } else if (stripos($this->name, 'suse')) {
            $icon = 'suse';
        } else if (stripos($this->name, 'redhat')) {
            $icon = 'redhat';
        } else if (stripos($this->name, 'centos')) {
            $icon = 'centos';
        } else {
            $icon = 'linux';
        }
        return "<i class=\"fab fa-$icon\" title=\"$this->name\"></i>";
    }
}
