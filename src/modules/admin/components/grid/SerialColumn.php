<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

class SerialColumn extends \yii\grid\SerialColumn
{
    public bool $reversed = false;

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index): int
    {
        if (!$this->reversed) {
            return parent::renderDataCellContent($model, $key, $index);
        }
        $pagination = $this->grid->dataProvider->getPagination();
        if ($pagination !== false) {
            return $pagination->totalCount - $pagination->getOffset() - $index;
        }
        return $this->grid->dataProvider->getTotalCount() - $index;
    }
}
