<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\install\services\exceptions;

use Throwable;

class PermissionsException extends \Exception
{
    private array $directories;

    public function __construct(array $directories, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->directories = $directories;
    }

    /**
     * @return array
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }
}
