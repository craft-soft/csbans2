<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\systemInfo;

class Item
{
    private string $label;

    /** @var mixed */
    private $value;

    private ?string $errorMessage;

    private bool $hasError;

    /**
     * @param string $label
     * @param mixed $value
     * @param bool $hasError
     * @param string|null $errorMessage
     */
    public function __construct(string $label, $value, bool $hasError = false, ?string $errorMessage = null)
    {
        $this->label = $label;
        $this->value = $value;
        $this->errorMessage = $errorMessage;
        $this->hasError = $hasError;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @return string|null
     */
    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    /**
     * @return bool
     */
    public function isHasError(): bool
    {
        return $this->hasError;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }
}
