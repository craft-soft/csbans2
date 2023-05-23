<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\validators;

use Yii;
use yii\validators\Validator;

class SteamIDValidator extends Validator
{
    private const PATTERN = '/^(STEAM|VALVE)_[0-9]:[0-9]:[0-9]{1,15}$/';

    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('app', 'VALIDATE_INVALID_STEAMID');
        }
    }

    protected function validateValue($value): ?array
    {
        if ($value && !preg_match(self::PATTERN, $value)) {
            return [$this->message, [
                'steamid' => $value
            ]];
        }
        return null;
    }

    public function clientValidateAttribute($model, $attribute, $view): string
    {
        $message = json_encode($this->message, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $pattern = self::PATTERN;
        return <<<JS
if (value && !value.match($pattern)) {
    messages.push($message);
}
JS;
    }
}
