<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\components\params;

use yii\db\ActiveQuery;

/**
 * @property mixed $value
 * @method static string tableName()
 */
interface ParamsModelInterface
{
    public function getValue();
    public function setValue($value);
    public function getKey(): string;
    public function toFrontend(): bool;

    /**
     * @return ParamsModelInterface[]
     */
    public static function getAll(): array;
}
