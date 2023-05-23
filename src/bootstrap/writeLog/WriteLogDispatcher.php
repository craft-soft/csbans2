<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap\writeLog;

use app\models\Log;
use yii\web\Application;
use app\events\ParamsSavedEvent;
use app\modules\admin\events\UnbanEvent;
use yii\base\{BootstrapInterface, Event};
use yii\db\{ActiveRecord, AfterSaveEvent};
use app\modules\admin\events\PlayerActionEvent;
use app\modules\admin\models\{Webadmin, AmxAdmin, AppParam, Link};
use app\modules\admin\models\{Ban, Comment, File, Reason, ReasonsSet, Server, AdminsServer};

class WriteLogDispatcher implements BootstrapInterface
{
    private const ACTIONS = [
        AmxAdmin::class => [
            'insert' => LogModel::ACTION_AMX_ADMIN_ADDED,
            'update' => LogModel::ACTION_AMX_ADMIN_UPDATED,
            'delete' => LogModel::ACTION_AMX_ADMIN_DELETED,
        ],
        Webadmin::class => [
            'insert' => LogModel::ACTION_WEB_ADMIN_ADDED,
            'update' => LogModel::ACTION_WEB_ADMIN_UPDATED,
            'delete' => LogModel::ACTION_WEB_ADMIN_DELETED,
        ],
        Link::class => [
            'insert' => LogModel::ACTION_LINK_ADDED,
            'update' => LogModel::ACTION_LINK_UPDATED,
            'delete' => LogModel::ACTION_LINK_DELETED,
        ],
        Reason::class => [
            'insert' => LogModel::ACTION_REASON_ADDED,
            'update' => LogModel::ACTION_REASON_UPDATED,
            'delete' => LogModel::ACTION_REASON_DELETED,
        ],
        ReasonsSet::class => [
            'insert' => LogModel::ACTION_REASONS_SET_ADDED,
            'update' => LogModel::ACTION_REASONS_SET_UPDATED,
            'delete' => LogModel::ACTION_REASONS_SET_DELETED,
        ],
        Server::class => [
            'insert' => LogModel::ACTION_SERVER_ADDED,
            'update' => LogModel::ACTION_SERVER_UPDATED,
            'delete' => LogModel::ACTION_SERVER_DELETED,
        ],
        AdminsServer::class => [
            'insert' => LogModel::ACTION_ADMIN_SERVER_ADDED,
            'update' => LogModel::ACTION_ADMIN_SERVER_UPDATED,
            'delete' => LogModel::ACTION_ADMIN_SERVER_DELETED,
        ],
        Ban::class => [
            'insert' => LogModel::ACTION_BAN_ADDED,
            'update' => LogModel::ACTION_BAN_UPDATED,
            'delete' => LogModel::ACTION_BAN_DELETED,
        ],
        File::class => [
            'update' => LogModel::ACTION_FILE_MODERATED,
            'delete' => LogModel::ACTION_FILE_DELETED,
        ],
        Comment::class => [
            'update' => LogModel::ACTION_COMMENT_MODERATED,
            'delete' => LogModel::ACTION_COMMENT_DELETED,
        ],
    ];

    private const FORMATS = [
        AmxAdmin::class => [
            'created' => 'datetime',
            'expired' => 'datetime',
            'ashow' => 'boolean',
        ],
        Webadmin::class => [
            'last_action' => 'datetime'
        ],
        Ban::class => [
            'ban_created' => 'datetime',
            'ban_length' => 'banLength',
            'ban_type' => 'banType',
        ],
        Comment::class => [
            'moderated' => 'boolean',
            'date' => 'datetime',
        ],
        File::class => [
            'moderated' => 'boolean',
            'upload_time' => 'datetime',
        ],
    ];

    private const PROTECTED_ATTRIBUTES = [
        Server::class => ['rcon'],
        Webadmin::class => ['password_input'],
        AmxAdmin::class => ['password'],
    ];

    /**
     * @var LogDataAttribute[]
     */
    private array $appParams = [];

    /**
     * @var Application|null
     */
    private ?Application $app = null;
    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        $baseModels = [
            Ban::class, Link::class, Reason::class,
            AmxAdmin::class, Webadmin::class, ReasonsSet::class,
            AdminsServer::class, Comment::class, File::class
        ];
        foreach ($baseModels as $baseModel) {
            Event::on($baseModel, ActiveRecord::EVENT_AFTER_INSERT, [$this, 'modelAddedOrUpdated']);
            Event::on($baseModel, ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'modelAddedOrUpdated']);
            Event::on($baseModel, ActiveRecord::EVENT_AFTER_DELETE, [$this, 'modelDeleted']);
        }
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $this->app = $app;
        Event::on(Ban::class, Ban::EVENT_UNBAN, [$this, 'handleUnban']);
        Event::on(Server::class, ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'modelAddedOrUpdated']);
        Event::on(Server::class, ActiveRecord::EVENT_AFTER_DELETE, [$this, 'modelDeleted']);
        Event::on(AppParam::class, ActiveRecord::EVENT_AFTER_UPDATE, [$this, 'appParamUpdated']);
        Event::on(ParamsSavedEvent::class, ParamsSavedEvent::EVENT_NAME, [$this, 'paramsUpdated']);
        Event::on(PlayerActionEvent::class, PlayerActionEvent::EVENT_NAME, [$this, 'onPlayerAction']);
    }

    public function onPlayerAction(PlayerActionEvent $event)
    {
        $actions = [
            'ban' => 'ACTION_PLAYER_BANNED',
            'kick' => 'ACTION_PLAYER_KICKED',
            'message' => 'ACTION_PLAYER_MESSAGE',
        ];
        $this->write($actions[$event->getAction()], [
            'message' => [
                'category' => 'admin/logs',
                'message' => "{$actions[$event->getAction()]}_MESSAGE",
                'params' => [
                    'player' => $event->getPlayerName(),
                    'message' => $event->getMessage(),
                    'reason' => $event->getMessage(),
                    'length' => [
                        'value' => $event->getLength(),
                        'format' => 'banLength'
                    ]
                ]
            ]
        ]);
    }

    public function handleUnban(UnbanEvent $event)
    {
        $this->write(Log::ACTION_BAN_UNBANNED, $this->logDataForModel($event->getBan())->toArray());
    }

    public function modelDeleted(Event $event): void
    {
        /** @var ActiveRecord $model */
        $model = $event->sender;
        $logData = $this->logDataForModel($model);
        $this->write(self::ACTIONS[get_class($model)]['delete'], $logData->toArray());
    }

    private function logDataForModel(ActiveRecord $model): LogData
    {
        $attributes = [];
        foreach ($model->getAttributes() as $attribute => $value) {
            if (!in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                $attributes[] = new LogDataAttribute($attribute, null, $value);
            }
        }
        return new LogData(get_class($model), $attributes);
    }

    public function appParamUpdated(AfterSaveEvent $event)
    {
        /** @var AppParam $model */
        $model = $event->sender;
        $changedAttributes = $event->changedAttributes;
        unset($changedAttributes['updated_at']);
        $key = array_key_first($changedAttributes);
        if ($key && $changedAttributes[$key] !== $model->getValue()) {
            $this->appParams[] = new LogDataAttribute(
                $model->key,
                $model->getValue(),
                $changedAttributes[$key],
            );
        }
    }

    public function paramsUpdated()
    {
        $logData = new LogData(AppParam::class, $this->appParams);
        $this->write(LogModel::ACTION_APP_PARAMS_UPDATED, $logData->toArray());
    }

    public function modelAddedOrUpdated(AfterSaveEvent $event)
    {
        $isNew = $event->name === ActiveRecord::EVENT_AFTER_INSERT;
        $action = self::ACTIONS[get_class($event->sender)][$isNew ? 'insert' : 'update'];
        $logData = new LogData(get_class($event->sender), $this->getAttributes($event));
        $this->write($action, $logData->toArray());
    }

    private function getAttributes(AfterSaveEvent $event): array
    {
        /** @var ActiveRecord $model */
        $model = $event->sender;
        $modelClass = get_class($model);
        $attributes = [];
        $isNew = $event->name === ActiveRecord::EVENT_AFTER_INSERT;
        if ($isNew) {
            $attributes[] = new LogDataAttribute('id', $model->getAttribute('id'));
            foreach ($model->getAttributes() as $attribute => $value) {
                if (!in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                    $attributes[] = new LogDataAttribute(
                        $attribute,
                        $this->protectValue($modelClass, $attribute, $value),
                        null,
                        $this->getFormat($model, $attribute)
                    );
                }
            }
        } else {
            foreach ($event->changedAttributes as $attribute => $value) {
                if (!in_array($attribute, ['created_at', 'updated_at', 'created_by', 'updated_by'])) {
                    $attributes[] = new LogDataAttribute(
                        $attribute,
                        $this->protectValue($modelClass, $attribute, $model->getAttribute($attribute)),
                        $this->protectValue($modelClass, $attribute, $value),
                        $this->getFormat($model, $attribute)
                    );
                }
            }
        }
        return $attributes;
    }

    /**
     * @param $modelClass
     * @param $attribute
     * @param $value
     * @return mixed
     */
    private function protectValue($modelClass, $attribute, $value)
    {
        if (array_key_exists($modelClass, self::PROTECTED_ATTRIBUTES) && in_array($attribute, self::PROTECTED_ATTRIBUTES[$modelClass])) {
            return '*****';
        }
        return $value;
    }

    private function getFormat(ActiveRecord $model, string $attribute): ?string
    {
        return self::FORMATS[get_class($model)][$attribute] ?? null;
    }

    /**
     * @param string $action
     * @param array|string $data
     * @return void
     * @throws \Throwable
     */
    private function write(string $action, $data): void
    {
        if (!$data) {
            return;
        }
        $model = new LogModel();
        $model->action = $action;
        $model->timestamp = time();
        $model->ip = $this->app->getRequest()->getUserIP();
        if (is_array($data)) {
            $data = json_encode($data);
        }
        $model->remarks = $data;
        if (!$this->app->getUser()->getIsGuest()) {
            $model->username = $this->app->getUser()->getIdentity()->username;
        }
        $model->save();
    }
}
