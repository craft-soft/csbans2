<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\deviceDetect;

class Client
{
    public const TYPE_DESKTOP = 1;
    public const TYPE_TABLET = 2;
    public const TYPE_MOBILE = 3;

    private ?string $name;

    private ?string $version;

    private int $type = self::TYPE_DESKTOP;

    /**
     * @param string|null $name
     * @param string|null $version
     */
    public function __construct(?string $name, ?string $version)
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * @param int $type
     * @return Client
     */
    public function setType(int $type): Client
    {
        $this->type = $type;
        return $this;
    }

    public function getIcon(): string
    {
        switch($this->type) {
            case self::TYPE_DESKTOP:
                return '<i class="fas fa-desktop"></i>';
            case self::TYPE_TABLET:
                return '<i class="fas fa-tablet-alt"></i>';
            case self::TYPE_MOBILE:
                return '<i class="fas fa-mobile-alt"></i>';
            default:
                return '';
        }
    }
}
