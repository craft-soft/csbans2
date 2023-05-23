<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\events;

use yii\base\Event;
use yii\db\ActiveRecord;

class ParamsSavedEvent extends Event
{
    public const EVENT_NAME = 'appParamsSaved';

    /**
     * @var ActiveRecord[]
     */
    private array $models;

    /**
     * @param ActiveRecord[] $models
     */
    public function __construct(array $models, array $config = [])
    {
        $this->models = $models;
        parent::__construct($config);
    }

    /**
     * @return ActiveRecord[]
     */
    public function getModels(): array
    {
        return $this->models;
    }
}
