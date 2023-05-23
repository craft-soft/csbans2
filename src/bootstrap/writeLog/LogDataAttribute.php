<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap\writeLog;

class LogDataAttribute
{
    private string $attribute;

    /**
     * @var mixed
     */
    private $oldValue;

    /**
     * @var mixed
     */
    private $newValue;

    private ?string $format;

    /**
     * @param string $attribute
     * @param mixed|null $newValue
     * @param mixed|null $oldValue
     * @param string|null $format
     */
    public function __construct(
        string $attribute,
        $newValue = null,
        $oldValue = null,
        ?string $format = null
    )
    {
        $this->attribute = $attribute;
        $this->format = $format;
        $this->oldValue = $oldValue;
        $this->newValue = $newValue;
    }

    /**
     * @return string
     */
    public function getAttribute(): string
    {
        return $this->attribute;
    }

    /**
     * @return string|null
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function toArray(): ?array
    {
        if (!$this->newValue && !$this->oldValue) {
            return null;
        }

        return [
            'attribute' => $this->attribute,
            'value' => $this->newValue,
            'oldValue' => $this->oldValue && $this->oldValue !== $this->newValue ? $this->oldValue : null,
            'format' => $this->format,
        ];
    }
}
