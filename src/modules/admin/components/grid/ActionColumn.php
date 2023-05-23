<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

use yii\helpers\Html;

class ActionColumn extends \yii\grid\ActionColumn
{
    public array $containerOptions = [
        'class' => 'd-flex justify-content-between grid-actions'
    ];

    public $options = [
        'style' => 'min-width: 20px'
    ];

    public function renderDataCellContent($model, $key, $index)
    {
        return Html::tag('div', parent::renderDataCellContent($model, $key, $index), $this->containerOptions);
    }
}
