<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\components\grid;

use app\models\Ban;
use app\rbac\Permissions;
use yii\helpers\Html;

class BanActionsColumn extends ActionColumn
{
    public $template = '{unban}{reban}{view}{update}{delete}';

    public function init()
    {
        parent::init();
        $this->buttons['unban'] = [$this, 'renderUnbanButton'];
        $this->buttons['reban'] = [$this, 'renderRebanButton'];
        $this->visibleButtons['unban'] = function(Ban $model) {
            if ($model->isUnbanned()) {
                return false;
            }
            return \Yii::$app->getUser()->can(Permissions::PERMISSION_BANS_UNBAN, [
                'ban' => $model
            ]);
        };
        $this->visibleButtons['reban'] = function(Ban $model) {
            if (!$model->isUnbanned()) {
                return false;
            }
            return \Yii::$app->getUser()->can(Permissions::PERMISSION_BANS_ADD, [
                'ban' => $model
            ]);
        };
        $this->visibleButtons['delete'] = function(Ban $model) {
            return \Yii::$app->getUser()->can(Permissions::PERMISSION_BANS_DELETE, [
                'ban' => $model
            ]);
        };
        $this->visibleButtons['update'] = function(Ban $model) {
            return \Yii::$app->getUser()->can(Permissions::PERMISSION_BANS_EDIT, [
                'ban' => $model
            ]);
        };
    }

    public function renderUnbanButton($url, Ban $model): string
    {
        return Html::a(
            Html::tag('i', '', ['class' => 'fas fa-ban']),
            ['/admin/bans/unban', 'id' => $model->bid],
            [
                'data-pjax' => '0',
                'data-method' => 'post',
                'data-confirm' => \Yii::t('admin/bans', 'UNBAN_LINK_CONFIRM'),
                'title' => \Yii::t('admin/bans', 'ACTION_UNBAN')
            ]
        );
    }

    public function renderRebanButton($url, Ban $model): string
    {
        return Html::a(
            Html::tag('i', '', ['class' => 'fas fa-undo']),
            [
                'create',
                'reban' => [
                    'player_nick' => $model->player_nick,
                    'player_id' => $model->player_id,
                    'player_ip' => $model->player_ip,
                    'ban_type' => $model->ban_type,
                    'ban_reason' => $model->ban_reason,
                    'ban_length' => $model->ban_length,
                ],
            ],
            [
                'title' => \Yii::t('admin/bans', 'ACTION_REBAN'),
                'data-pjax' => '0'
            ]
        );
    }
}
