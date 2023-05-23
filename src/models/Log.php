<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "{{%logs}}".
 *
 * @property int $id
 * @property int $timestamp
 * @property string|null $ip
 * @property string|null $username
 * @property string|null $action
 * @property string|null $remarks
 *
 * @property-read string $actionView
 * @property-read Webadmin $admin
 * @property-read null[]|array|string[] $data
 */
class Log extends \yii\db\ActiveRecord
{
    public const ACTION_AMX_ADMIN_ADDED = 'ACTION_AMX_ADMIN_ADDED';
    public const ACTION_AMX_ADMIN_UPDATED = 'ACTION_AMX_ADMIN_UPDATED';
    public const ACTION_AMX_ADMIN_DELETED = 'ACTION_AMX_ADMIN_DELETED';
    public const ACTION_WEB_ADMIN_ADDED = 'ACTION_WEB_ADMIN_ADDED';
    public const ACTION_WEB_ADMIN_UPDATED = 'ACTION_WEB_ADMIN_UPDATED';
    public const ACTION_WEB_ADMIN_DELETED = 'ACTION_WEB_ADMIN_DELETED';
    public const ACTION_LINK_ADDED = 'ACTION_LINK_ADDED';
    public const ACTION_LINK_UPDATED = 'ACTION_LINK_UPDATED';
    public const ACTION_LINK_DELETED = 'ACTION_LINK_DELETED';
    public const ACTION_REASON_ADDED = 'ACTION_REASON_ADDED';
    public const ACTION_REASON_UPDATED = 'ACTION_REASON_UPDATED';
    public const ACTION_REASON_DELETED = 'ACTION_REASON_DELETED';
    public const ACTION_REASONS_SET_ADDED = 'ACTION_REASONS_SET_ADDED';
    public const ACTION_REASONS_SET_UPDATED = 'ACTION_REASONS_SET_UPDATED';
    public const ACTION_REASONS_SET_DELETED = 'ACTION_REASONS_SET_DELETED';
    public const ACTION_SERVER_ADDED = 'ACTION_SERVER_ADDED';
    public const ACTION_SERVER_UPDATED = 'ACTION_SERVER_UPDATED';
    public const ACTION_SERVER_DELETED = 'ACTION_SERVER_DELETED';
    public const ACTION_ADMIN_SERVER_ADDED = 'ACTION_ADMIN_SERVER_ADDED';
    public const ACTION_ADMIN_SERVER_UPDATED = 'ACTION_ADMIN_SERVER_UPDATED';
    public const ACTION_ADMIN_SERVER_DELETED = 'ACTION_ADMIN_SERVER_DELETED';
    public const ACTION_BAN_ADDED = 'ACTION_BAN_ADDED';
    public const ACTION_BAN_UPDATED = 'ACTION_BAN_UPDATED';
    public const ACTION_BAN_DELETED = 'ACTION_BAN_DELETED';
    public const ACTION_APP_PARAMS_UPDATED = 'ACTION_APP_PARAMS_UPDATED';
    public const ACTION_BAN_UNBANNED = 'ACTION_BAN_UNBANNED';
    public const ACTION_FILE_MODERATED = 'ACTION_FILE_MODERATED';
    public const ACTION_FILE_DELETED = 'ACTION_FILE_DELETED';
    public const ACTION_COMMENT_MODERATED = 'ACTION_COMMENT_MODERATED';
    public const ACTION_COMMENT_DELETED = 'ACTION_COMMENT_DELETED';

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => Yii::t('admin/logs', 'ATTRIBUTE_ID'),
            'timestamp' => Yii::t('admin/logs', 'ATTRIBUTE_TIMESTAMP'),
            'ip' => Yii::t('admin/logs', 'ATTRIBUTE_IP'),
            'username' => Yii::t('admin/logs', 'ATTRIBUTE_USERNAME'),
            'action' => Yii::t('admin/logs', 'ATTRIBUTE_ACTION'),
            'actionView' => Yii::t('admin/logs', 'ATTRIBUTE_ACTION'),
            'remarks' => Yii::t('admin/logs', 'ATTRIBUTE_REMARKS'),
        ];
    }
        /**
     * Gets query for [[Admin]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAdmin(): \yii\db\ActiveQuery
    {
        return $this->hasOne(Webadmin::class, ['username' => 'username']);
    }

    public function getActionView(): string
    {
        return Yii::t('admin/logs', $this->action);
    }

    public function isRemark(): bool
    {
        $result = json_decode($this->remarks, true);
        return json_last_error() !== JSON_ERROR_NONE || !empty($result['message']);
    }

    public function getData()
    {
        if (!$this->remarks) {
            return [];
        }
        $data = json_decode($this->remarks, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            // This is a just message (e.g. from the server)
            return $this->remarks;
        }
        if (!empty($data['message'])) {
            // This is formatted message
            return $this->formatMessage($data);
        }
        $class = $data['modelClass'];
        if ($class === \app\modules\admin\models\AppParam::class) {
            return $this->dataForParams($data['attributes']);
        }
        /** @var ActiveRecord $model */
        $model = new $class;
        $allData = [];
        foreach ($data['attributes'] as $attribute) {
            $data = [
                'label' => $model->getAttributeLabel($attribute['attribute']),
                'newValue' => $this->getDataValue('value', $attribute),
                'oldValue' => $this->getDataValue('oldValue', $attribute),
            ];
            $allData[] = $data;
        }
        return $allData;
    }

    private function formatMessage(array $data): string
    {
        $params = [];
        foreach ($data['message']['params'] as $param => $value) {
            if (is_array($value)) {
                $params[$param] = Yii::$app->getFormatter()->format($value['value'], $value['format']);
            } else {
                $params[$param] = $value;
            }
        }
        return Yii::t($data['message']['category'], $data['message']['message'], $params);
    }

    private function getDataValue(string $type, array $data)
    {
        $value = $data[$type] ?? null;
        if ($data['format']) {
            try {
                if ($data['format'] === 'boolean') {
                    $value = (int)$value;
                }
                return Yii::$app->getFormatter()->format($value, $data['format']);
            } catch (\Throwable $e) {}
        }
        return $value;
    }

    private function dataForParams(array $attributes): array
    {
        $ids = array_column($attributes, 'attribute');
        $models = \app\modules\admin\models\AppParam::find()->where(['key' => $ids])->indexBy('key')->all();
        $allData = [];
        foreach ($attributes as $attribute) {
            if (isset($models[$attribute['attribute']])) {
                /** @var \app\modules\admin\models\AppParam $model */
                $model = $models[$attribute['attribute']];
                $data = [
                    'label' => Yii::t('admin/params', $model->label),
                    'newValue' => null,
                    'oldValue' => null,
                ];
                if (!empty($attribute['value']) && is_scalar($attribute['value'])) {
                    $data['newValue'] = \app\modules\admin\models\AppParam::valueForLog($models[$attribute['attribute']], $attribute['value']);
                }
                if (!empty($attribute['oldValue']) && is_scalar($attribute['oldValue'])) {
                    $data['oldValue'] = \app\modules\admin\models\AppParam::valueForLog($models[$attribute['attribute']], $attribute['oldValue']);
                }
                $allData[] = $data;
            }
        }
        return $allData;
    }
}
