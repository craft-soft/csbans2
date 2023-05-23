<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models\forms;

use yii\base\Model;
use app\modules\admin\services\serverRcon\PlayerAction;

class PlayerActionForm extends Model
{
    /**
     * @var mixed
     */
    public $action = null;
    /**
     * @var mixed
     */
    public $player = null;
    /**
     * @var mixed
     */
    public $length = 0;
    /**
     * @var mixed
     */
    public $message = null;

    public function formName(): string
    {
        return '';
    }

    public function rules(): array
    {
        return [
            [['action'], 'required', 'message' => \Yii::t('admin/servers', 'ONLINE_ACTION_ACTION_REQUIRED')],
            [['player'], 'required', 'message' => \Yii::t('admin/servers', 'ONLINE_ACTION_PLAYER_REQUIRED')],
            [['length'], 'number'],
            [
                ['action'],
                'in',
                'range' => [
                    PlayerAction::TYPE_BAN,
                    PlayerAction::TYPE_KICK,
                    PlayerAction::TYPE_MESSAGE,
                ],
                'message' => \Yii::t('admin/servers', 'ONLINE_ACTION_INVALID_ACTION')
            ],
            [['message'], function() {
                if (in_array($this->action, [PlayerAction::TYPE_BAN, PlayerAction::TYPE_MESSAGE]) && !$this->message) {
                    $this->addError('message', \Yii::t(
                        'admin/servers',
                        $this->action === PlayerAction::TYPE_BAN ? 'ONLINE_ACTION_REASON_REQUIRED' : 'ONLINE_ACTION_MESSAGE_REQUIRED'
                    ));
                }
            }]
        ];
    }
}
