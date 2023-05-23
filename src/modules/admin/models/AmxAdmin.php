<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use yii\base\ErrorException;
use yii\db\Expression;
use yii\helpers\Html;
use yii\validators\UniqueValidator;

/**
 * @inheritDoc
 *
 * @property null $accessFlags
 * @property bool $forever
 * @property null|string $expiredDate
 * @property array $serversList
 * @property-read null|array $allServers
 * @property-read array $serversModels
 * @property null $passwordField
 * @property mixed $accountType
 */
class AmxAdmin extends \app\models\AmxAdmin
{
    private array $_servers = [];

    private ?string $_password = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        $accountTypeId = Html::getInputId($this, 'accountType');
        $foreverId = Html::getInputId($this, 'forever');
        $accountTypeIp = self::ACCOUNT_FLAG_IP;
        $accountTypeSteam = self::ACCOUNT_FLAG_STEAMID;
        return [
            [['nickname'], 'required'],
            ['nickname', 'unique', 'message' => \Yii::t('admin/amxAdmins', 'VALIDATE_NICKNAME_UNIQUE')],
            ['username', 'unique', 'message' => \Yii::t('admin/amxAdmins', 'VALIDATE_STEAMID_UNIQUE')],
            ['steamid', 'validateSteamIdRequired', 'skipOnEmpty' => false],
            ['steamid', 'steamIdUniqueValidator'],
            ['username', 'steamid'],
            [
                'steamid',
                'steamid',
                'when' => function(AmxAdmin $model) {
                    return $model->isBySteamId();
                },
                'whenClient' => "function(attribute, value) {return $('#$accountTypeId').val() === '$accountTypeSteam';}",
            ],
            [
                'steamid',
                'ip',
                'ipv6' => false,
                'when' => function(AmxAdmin $model) {
                    return $model->isByIp();
                },
                'whenClient' => "function(attribute, value) {return $('#$accountTypeId').val() === '$accountTypeIp';}",
            ],
            [['icq', 'ashow', 'created', 'expired', 'days'], 'integer'],
            ['created', 'default', 'value' => time()],
            [['username', 'access', 'flags', 'steamid', 'nickname'], 'string', 'max' => 32],
            ['expiredDate', 'date', 'format' => 'php:d.m.Y'],
            [['password', 'passwordField'], 'string', 'max' => 60],
            [['accountType'], 'in', 'range' => array_keys(self::accountFlags())],
            ['accessFlags', 'required', 'message' => \Yii::t('admin/amxAdmins', 'VALIDATE_ACCESS_FLAGS_REQUIRED')],
            ['accessFlags', 'each', 'rule' => ['in', 'range' => array_keys(self::accessFlags())]],
            ['serversList', 'each', 'rule' => ['in', 'range' => array_keys($this->getAllServers())]],
            ['forever', 'boolean'],
            [
                'expiredDate',
                'required', 'when' => function(AmxAdmin $model) {
                    return !$model->getForever();
                },
                'whenClient' => "function() {return !$('#$foreverId').is(':checked')}",
                'message' => \Yii::t('admin/amxAdmins', 'VALIDATE_DAYS_REQUIRED')
            ],
//            ['days', 'default', 'value' => 0],
            [
                'passwordField',
                'required',
                'when' => function(AmxAdmin $model) {
                    return $model->isByNick() && $model->getIsNewRecord();
                },
                'whenClient' => 'function (attribute, value) {return false;}',
                'message' => \Yii::t('admin/amxAdmins', 'VALIDATE_PASSWORD_REQUIRED')
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeLabels(): array
    {
        $labels = parent::attributeLabels();
        return array_merge($labels, [
            'passwordField' => $labels['password']
        ]);
    }

    public function steamIdUniqueValidator()
    {
        $query = self::find()->where(['steamid' => $this->steamid]);
        if (!$this->getIsNewRecord()) {
            $query->andWhere(['<>', 'id', $this->id]);
        }
        if ($query->exists()) {
            $this->addError('steamid', \Yii::t('admin/amxAdmins', 'VALIDATE_AUTH_EXISTS', [
                'accountType' => $this->accountFlags()[$this->flags]
            ]));
        }
    }

    public function validateSteamIdRequired()
    {
        if (!$this->steamid) {
            $this->addError('steamid', \Yii::t(
                'admin/amxAdmins',
                'VALIDATE_STEAMID_REQUIRED',
                ['accountType' => $this->accountFlags()[$this->flags]]
            ));
        }
    }

    public function getPasswordField()
    {
        return $this->_password;
    }

    public function setPasswordField($value)
    {
        if ($value) {
            $this->_password = $value;
            $this->password = $value;
        }
    }

    public function getAccountType()
    {
        if (!$this->flags) {
            return null;
        }
        $flagsArray = str_split($this->flags);
        sort($flagsArray);
        return $flagsArray[0];
    }

    public function setAccountType($value)
    {
        $this->flags = $value;
    }

    public function setAccessFlags($value)
    {
        if ($value && is_array($value)) {
            $this->access = implode('', $value);
        }
    }

    public function isByNick(): bool
    {
        return $this->getAccountType() === self::ACCOUNT_FLAG_NICK;
    }

    public function isBySteamId(): bool
    {
        return $this->getAccountType() === self::ACCOUNT_FLAG_STEAMID;
    }

    public function isByIp(): bool
    {
        return $this->getAccountType() === self::ACCOUNT_FLAG_IP;
    }

    public function getForever(): bool
    {
        return !$this->days && !$this->expired;
    }

    public function setForever($value)
    {
        if ($value) {
            $this->days = 0;
            $this->expired = 0;
        }
    }

    public function getExpiredDate()
    {
        if (!$this->expired) {
            return null;
        }
        return date('d.m.Y', (int)$this->expired);
    }

    public function setExpiredDate($value)
    {
        if (!$value) {
            return;
        }
        $now = new \DateTime();
        $expiredDate = \DateTime::createFromFormat('d.m.Y', $value);
        $expiredDate->setTime((int)$now->format('G'), (int)$now->format('i'), (int)$now->format('s'));
        $this->expired = (int)$expiredDate->format('U');
        $diff = $now->diff($expiredDate);
        if (!$diff->invert) {
            $this->days = $diff->days;
        }
    }

    /**
     * Gets query for [[AdminsServers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdminsServers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(AdminsServer::class, ['admin_id' => 'id']);
    }

    /**
     * Gets query for [[Servers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getServers(): \yii\db\ActiveQuery
    {
        return $this->hasMany(Server::class, ['id' => 'server_id'])->via('adminsServers');
    }

    public function getServersList(): array
    {
        return $this->getServers()->select('id')->column();
    }

    public function setServersList($value): void
    {
        if ($value && is_array($value)) {
            $this->_servers = $value;
        }
    }

    public function afterFind()
    {
        parent::afterFind();
        if (!$this->days) {
            $this->days = null;
        }
    }

    private ?array $_allServers = null;
    public function getAllServers(): ?array
    {
        if ($this->_allServers === null) {
            $this->_allServers = Server::find()
                ->select(new Expression("CONCAT(`hostname`, ' (', `address`, ')')"))
                ->indexBy('id')
                ->column();
        }
        return $this->_allServers;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->unlinkAll('servers', true);
        if ($this->_servers) {
            $servers = Server::findAll(['id' => $this->_servers]);
            foreach ($servers as $server) {
                $this->link('servers', $server);
            }
        }
    }

    public function getServersModels(): array
    {
        $models = [];
        /** @var AdminsServer[] $allLinks */
        $allLinks = $this->getAdminsServers()->indexBy('server_id')->all();
        foreach ($this->getAllServers() as $id => $server) {
            if (isset($allLinks[$id])) {
                $link = $allLinks[$id];
                $link->enabled = true;
            } else {
                $link = new AdminsServer(['server_id' => $id]);
                $link->refresh();
            }
            $models[] = [
                'server' => $server,
                'link' => $link
            ];
        }
        return $models;
    }

    /**
     * @param AdminsServer[] $servers
     * @return void
     */
    public function addServers(array $servers)
    {
        $this->unlinkAll('servers', true);
        foreach ($servers as $server) {
            if ($server->enabled) {
                $server->admin_id = $this->id;
                $server->save();
            }
        }
    }
}
