<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

use yii\helpers\Html;
use yii\grid\DataColumn;
use app\modules\admin\models\{Comment, File};

class ContentBanColumn extends DataColumn
{
    /**
     * @param File|Comment $model
     * @param $key
     * @param $index
     * @return string
     */
    protected function renderDataCellContent($model, $key, $index): string
    {
        return Html::a(
            $model->ban->player_nick,
            ['/admin/bans/view', 'id' => $model->ban->bid],
            ['data-pjax' => '0']
        );
    }
}
