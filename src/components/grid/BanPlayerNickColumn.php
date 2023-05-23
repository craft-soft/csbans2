<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\grid;

use app\models\Ban;
use yii\helpers\Html;
use yii\grid\DataColumn;
use app\components\ipGeo\IpGeo;
use yii\helpers\Url;

class BanPlayerNickColumn extends DataColumn
{
    /**
     * @param Ban $model
     * @param $key
     * @param $index
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    protected function renderDataCellContent($model, $key, $index): string
    {
        $content = parent::renderDataCellContent($model, $key, $index);
        /** @var IpGeo $ipGeo */
        $ipGeo = \Yii::$app->get('ipGeo');
        return Html::img(
            \Yii::$app->getAssetManager()->publish($ipGeo->defaultFlag())[1],
            [
                'data-ban-flag' => $model->bid
            ]
        ) .
        Html::tag('span', $content, [
            'class' => 'banlist-player-nick ms-2'
        ]);
    }
}
