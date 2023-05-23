<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use Yii;
use app\components\ipGeo\IpGeo;
use app\components\deviceDetect\DeviceDetect;

/**
 * This is the model class for table "{{%webadmin_auths}}".
 *
 * @property int $id
 * @property int $admin_id
 * @property int $date
 * @property string|null $ip
 * @property string $user_agent
 * @property string $session_id
 *
 * @property-read Webadmin $admin
 */
class WebadminAuth extends \yii\db\ActiveRecord
{
    public ?string $device = null;
    public string $location = '-';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%webadmin_auths}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['admin_id', 'date', 'user_agent', 'session_id'], 'required'],
            [['admin_id', 'date'], 'integer'],
            [['ip'], 'string', 'max' => 15],
            [['user_agent'], 'string', 'max' => 255],
            [['session_id'], 'string', 'max' => 64],
            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => Webadmin::class, 'targetAttribute' => ['admin_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_ID'),
            'admin_id' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_ADMIN_ID'),
            'date' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_DATE'),
            'ip' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_IP'),
            'user_agent' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_USERAGENT'),
            'session_id' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_SESSION_ID'),
            'device' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_DEVICE'),
            'location' => Yii::t('admin/webadmins', 'AUTH_ATTRIBUTE_LOCATION'),
        ];
    }
        /**
     * Gets query for [[Admin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Webadmin::class, ['id' => 'admin_id']);
    }

    public static function createForAdmin(
        \app\models\Webadmin $webadmin,
        ?string $ip,
        ?string $userAgent,
        ?string $sessionId
    ): bool
    {
        $model = new static();
        $model->admin_id = $webadmin->id;
        $model->date = time();
        $model->ip = $ip;
        $model->session_id = $sessionId;
        $model->user_agent = $userAgent;
        return $model->save();
    }

    private function parseUseragent()
    {
        if ($this->user_agent) {
            /** @var DeviceDetect $service */
            $service = Yii::$container->get(DeviceDetect::class);
            $device = $service->parse($this->user_agent);
            if ($device) {
                $parts = [
                    $device->getClient()->getIcon(),
                    $device->getOs()->getIcon(),
                    $device->getOs()->getFullName(),
                    $device->getClient()->getName(),
                    $device->getClient()->getVersion()
                ];
                $this->device = implode(' ', array_filter($parts));
            }
        }
        if ($this->ip) {
            /** @var IpGeo $service */
            $service = Yii::$container->get(IpGeo::class);
            $ipData = $service->getData($this->ip);
            if ($ipData) {
                $parts = [
                    $ipData->getCountry(),
                    $ipData->getRegionName(),
                    $ipData->getCity()
                ];
                $this->location = trim(implode(', ', array_filter($parts)));
            }
        }
    }

    public function afterFind()
    {
        parent::afterFind();$this->parseUseragent();
    }
}
