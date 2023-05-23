<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\grid;

use yii\db\ActiveRecord;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\View;

class IpModalColumn extends DataColumn
{
    /**
     * @param ActiveRecord$model
     * @param $key
     * @param $index
     * @return string
     * @throws \Exception
     */
    protected function renderDataCellContent($model, $key, $index): string
    {
        $content = parent::renderDataCellContent($model, $key, $index);
        return Html::a($content, '#', [
            'data-ip-modal' => true,
            'id' => "ip-modal-column-{$this->attribute}-$key"
        ]);
    }
}
