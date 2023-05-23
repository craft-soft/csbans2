<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components;

use Yii;
use app\models\Ban;
use yii\helpers\Html;

class Formatter extends \yii\i18n\Formatter
{
    public function asBanLength($value): ?string
    {
        if (!is_numeric($value)) {
            return $this->nullDisplay;
        }
        $length = (int)$value;
        if (array_key_exists($length, Ban::BAN_TIMES)) {
            return Yii::t('bans', Ban::BAN_TIMES[$length]);
        }
        return Yii::$app->getFormatter()->asDuration($value * 60);
    }

    public function asExpiredDate($value): ?string
    {
        if (!$value) {
            return Yii::t('app', 'EXPIRED_NEVER');
        }
        return $this->asDatetime($value);
    }

    public function asBanType($value): string
    {
        if (!$value) {
            return $this->nullDisplay;
        }
        return Ban::TYPES[$value] ?? $value;
    }

    public function asSteamid($value): ?string
    {
        if (!$value) {
            return $this->nullDisplay;
        }
        try {
            $steam = new \SteamID($value);
        } catch (\Throwable $e) {
            return $value;
        }
        return Html::a($value, "https://steamcommunity.com/profiles/{$steam->ConvertToUInt64()}", ['target' => '_blank']);
    }

    public function asServerAddress($value): ?string
    {
        if (!$value) {
            return $this->nullDisplay;
        }
        return Html::a($value, "steam://connect/$value");
    }

    public function asIpModal($value): string
    {
        if (!$value) {
            return $this->nullDisplay;
        }
        return Html::a($value, '#', [
            'data' => [
                'ip-modal' => true,
                'ip' => $value
            ],
        ]);
    }

    public function asServerStatus($value): string
    {
        $key = $value ? 'ONLINE_DATA_STATUS_ONLINE' : 'ONLINE_DATA_STATUS_OFFLINE';
        return Yii::t('servers', $key);
    }
}
