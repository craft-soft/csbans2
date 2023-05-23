<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\models;

use yii\db\{ActiveQuery, ActiveRecord};
use app\components\params\AppParams;
use app\components\params\ParamsModelInterface;
use yii\caching\DbDependency;
use yii\helpers\Url;

/**
 * @property integer $id
 * @property integer $type Parameter type (dropdown, textarea, etc)
 * @property string $key Parameter key (Yii::$app->config->key)
 * @property string $block The block in which this parameter will be located in the admin panel
 * @property string $value_string String parameter value
 * @property string $value_text Text parameter value
 * @property string $value_int Integer parameter value
 * @property string $value_float Float parameter value
 * @property string $value_bool Boolean parameter value
 * @property string $label Parameter name (For display in the grid)
 * @property string $description Description of the parameter (will be displayed in the form of a hint)
 * @property array $dropdown_options Array with options for the dropdown list
 * @property array|null $after_update Callable to be executed after parameter update
 * @property string $updated_at Update time
 * @property int|null $updated_by Updater
 * @property int $to_frontend
 * @property Admin $creator
 * @property Admin $updater
 * @property-read null|bool|string|float|int $value
 * @property int $sort [tinyint unsigned]
 */
class AppParam extends ActiveRecord implements ParamsModelInterface
{
    /**
     * @var integer Boolean
     */
    public const TYPE_BOOLEAN  = 1;

    /**
     * @var integer Integer
     */
    public const TYPE_INTEGER  = 2;

    /**
     * @var integer String
     */
    public const TYPE_STRING   = 3;

    /**
     * @var integer Text
     */
    public const TYPE_TEXT     = 4;

    /**
     * @var integer Dropdown
     */
    public const TYPE_DROPDOWN = 5;

    /**
     * @var integer Password
     */
    public const TYPE_PASSWORD = 6;

    /**
     * @var integer Float
     */
    public const TYPE_FLOAT = 7;

    public const TYPE_SEPARATOR = 8;
    public const TYPE_LABEL = 9;

    protected const FIELDS_MAP = [
        self::TYPE_BOOLEAN => 'value_bool',
        self::TYPE_INTEGER => 'value_int',
        self::TYPE_STRING => 'value_string',
        self::TYPE_TEXT => 'value_text',
        self::TYPE_DROPDOWN => 'value_string',
        self::TYPE_PASSWORD => 'value_string',
        self::TYPE_FLOAT => 'value_float',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName(): string
    {
        return '{{%configs}}';
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $this->type = (int)$this->type;
        switch ($this->type) {
            case self::TYPE_BOOLEAN:
                $this->value_bool = (bool)$this->value_bool;
                break;
            case self::TYPE_INTEGER:
                $this->value_int = (int)$this->value_int;
                break;
            case self::TYPE_FLOAT:
                $this->value_float = (float)$this->value_float;
                break;
        }
    }

    public function setValue($value)
    {
        $field = self::FIELDS_MAP[(int)$this->type] ?? 'value_string';
        $this->{$field} = $value;
        $this->updated_at = time();
        $this->updated_by = \Yii::$app->getUser()->getId();
        $this->save(false);
    }

    /**
     * Gets query for [[Updater]].
     *
     * @return ActiveQuery
     */
    public function getUpdater(): ActiveQuery
    {
        return $this->hasOne(Webadmin::class, ['id' => 'updated_by']);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue()
    {
        switch ((int)$this->type) {
            case self::TYPE_BOOLEAN:
                return (bool)$this->value_bool;
            case self::TYPE_INTEGER:
                return (int)$this->value_int;
            case self::TYPE_TEXT:
                return $this->value_text;
            case self::TYPE_FLOAT:
                return (float)$this->value_float;
            default:
                return $this->value_string;
        }
    }

    public static function getAll(): array
    {
        $dependency = new DbDependency([
            'sql' => 'SELECT MAX([[updated_at]]) FROM ' . self::tableName()
        ]);
        return self::getDb()->cache(fn() => self::find()->all(), 86400, $dependency);
    }

    public function toFrontend(): bool
    {
        return (bool)$this->to_frontend;
    }
}
