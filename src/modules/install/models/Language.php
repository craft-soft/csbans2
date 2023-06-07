<?php

declare(strict_types=1);

namespace app\modules\install\models;

use yii\base\Model;

class Language extends Model
{
    public ?string $language = null;

    public function rules(): array
    {
        return [
            ['language', 'required'],
            ['language', 'in', 'range' => array_keys(\Yii::$app->appParams->languages())],
        ];
    }
}
