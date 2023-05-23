<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

use yii\helpers\Html;
use app\modules\admin\models\{Comment, File};

class ContentActionColumn extends ActionColumn
{
    public $template = '{approve}{download}{update}{delete}';

    public $visibleButtons = ['view' => false];

    public $options = [
        'style' => 'min-width: 100px'
    ];

    public function init()
    {
        parent::init();
        $this->buttons['approve'] = [$this, 'renderApproveButton'];
        $this->buttons['download'] = [$this, 'renderDownloadButton'];
    }

    /**
     * @param $url
     * @param File|Comment $model
     * @return string
     */
    public function renderApproveButton($url,$model): string
    {
        if ($model->moderated) {
            return '';
        }
        return Html::a(
            Html::tag('i', '', ['class' => 'fa-solid fa-check']),
            ['approve', 'id' => $model->id],
            ['data-pjax' => 0]
        );
    }

    /**
     * @param $url
     * @param File|Comment $model
     * @return string
     */
    public function renderDownloadButton($url,$model): string
    {
        if (!$model instanceof File) {
            return '';
        }
        return Html::a(
            Html::tag('i', '', ['class' => 'fa-solid fa-download']),
            ['/bans/file', 'id' => $model->id],
            ['data-pjax' => 0]
        );
    }
}
