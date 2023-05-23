<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

\yii\validators\Validator::$builtInValidators['steamid'] = \app\components\validators\SteamIDValidator::class;
