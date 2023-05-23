<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

use yii\base\Model;
use yii\grid\DataColumn;

class DateColumn extends DataColumn
{
    public $format = 'php:d.m.Y H:i:s';

    protected function renderFilterCellContent()
    {
        if (is_string($this->filter)) {
            return $this->filter;
        }

        if (
            $this->filter !== false &&
            $this->grid->filterModel instanceof Model
            && $this->filterAttribute !== null &&
            $this->grid->filterModel->isAttributeActive($this->filterAttribute)
        ) {
            return \kartik\date\DatePicker::widget([
                'type' => \kartik\date\DatePicker::TYPE_INPUT,
                'model' => $this->grid->filterModel,
                'attribute' => $this->attribute,
                'pickerButton' => false,
                'pluginOptions' => [
                    'autoclose' => true,
                    'clearBtn' => true,
                    'todayBtn' => false,
                    'endDate' => '0d',
                    'format' => 'dd.mm.yyyy'
                ],
            ]);
        }
        return parent::renderFilterCellContent();
    }

    protected function renderDataCellContent($model, $key, $index): ?string
    {
        return $this->grid->formatter->asDatetime($model->{$this->attribute}, $this->format);
    }
}
