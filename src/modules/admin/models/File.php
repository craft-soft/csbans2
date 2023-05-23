<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

/**
 * @inheritDoc
 */
class File extends \app\models\File
{
    public const EVENT_APPROVE = 'file_approved';

    public function rules(): array
    {
        return [
            ['name', 'string', 'max' => 64],
            ['comment', 'string', 'max' => 1000],
            ['moderated', 'boolean'],
        ];
    }

    public function approve(): bool
    {
        $this->moderated = 1;
        if ($this->save(false)) {
            $this->trigger(self::EVENT_APPROVE);
            return true;
        }
        return false;
    }
}
