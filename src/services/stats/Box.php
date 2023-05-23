<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\services\stats;

class Box
{
    private string $label;

    /**
     * @var mixed
     */
    private $value;

    private string $color;

    private ?string $icon;

    private ?string $url;

    /**
     * @param string $label
     * @param mixed $value
     * @param string $color
     * @param string|null $icon
     * @param string|array|null $url
     */
    public function __construct(string $label, $value, string $color = 'light', ?string $icon = null, ?string $url = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->color = $color;
        $this->icon = $icon;
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    public function hasIcon(): bool
    {
        return $this->icon && strlen($this->icon) > 0;
    }

    public function hasLink(): bool
    {
        return $this->url !== null;
    }
}
