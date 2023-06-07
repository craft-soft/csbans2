<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\di\Instance;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @inheritDoc
 */
class AppParam extends \app\models\AppParam
{
    /**
     * @var mixed
     */
    public $valueField;

    public function behaviors(): array
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => false
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => false
            ],
        ];
    }

    public static function getList(?string $block = null): array
    {
        return static::find()
            ->where(['block' => $block])
            ->andWhere([
                'or',
                ['system' => null],
                ['system' => ''],
                ['system' => '0'],
            ])
            ->orderBy(['sort' => SORT_DESC])
            ->all();
    }

    public function rules(): array
    {
        $rules = [
            [['updated_by', 'updated_at'], 'integer'],
            ['value', function() {
                $type = (int)$this->type;
                if ($type === self::TYPE_DROPDOWN && !array_key_exists($this->valueField, $this->dropdown_options)) {
                    $this->addError('valueField', \Yii::t('admin/params', 'VALIDATE_WRONG_DROPDOWN_VALUE'));
                }
            }],
        ];
        if ($this->type === self::TYPE_BOOLEAN) {
            $rules[] = ['valueField', 'boolean'];
        } else if ($this->type === self::TYPE_INTEGER) {
            $rules[] = ['valueField', 'integer'];
        } else if ($this->type === self::TYPE_FLOAT) {
            $rules[] = ['valueField', 'number'];
        } else  {
            $rules[] = ['valueField', 'string'];
        }
        return $rules;
    }

    public function beforeSave($insert): bool
    {
        $field = self::FIELDS_MAP[(int)$this->type] ?? 'value_string';
        $this->setAttribute($field, $this->valueField);
        return parent::beforeSave($insert);
    }

    public function afterFind(): void
    {
        parent::afterFind();
        $this->valueField = $this->getValue();
        if ($this->dropdown_options) {
            if (is_scalar($this->dropdown_options)) {
                $this->dropdown_options = json_decode($this->dropdown_options, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $this->dropdown_options = [];
                }
            }
            if (!empty($this->dropdown_options['callable'])) {
                $class = $this->dropdown_options['callable']['method'][0];
                if (!class_exists($class)) {
                    throw new InvalidConfigException("Class $class does not exists");
                }
                $method = $this->dropdown_options['callable']['method'][1];
                $instance = \Yii::$container->get($class);
                $options = call_user_func_array([$instance, $method], $this->dropdown_options['callable']['params'] ?? []);
            } else {
                $options = [];
                foreach($this->dropdown_options as $key => $value) {
                    $options[$key] = \Yii::t('admin/params', $value);
                }
            }
            $this->dropdown_options = $options;
        }
    }

    public function isPassword(): bool
    {
        return (int)$this->type === self::TYPE_PASSWORD;
    }

    /**
     * @param ActiveForm $form
     * @param int $index
     * @return string|\yii\widgets\ActiveField
     * @throws \Exception
     */
    public function getField(ActiveForm $form, int $index)
    {
        $field = $form->field($this, "[$index]valueField");
        $field->label(\Yii::t('admin/params', $this->label));
        switch((int)$this->type) {
            case self::TYPE_LABEL:
                return Html::tag('h3', \Yii::t('admin/params', $this->label));
            case self::TYPE_SEPARATOR:
                return Html::tag('hr');
            case self::TYPE_INTEGER:
            case self::TYPE_FLOAT:
                $field->input('number');
                break;
            case self::TYPE_BOOLEAN:
                $field->checkbox();
                break;
            case self::TYPE_DROPDOWN:
                $field->dropDownList($this->dropdown_options);
                break;
            case self::TYPE_TEXT:
                $field->widget(\mihaildev\ckeditor\CKEditor::class, [
                    'editorOptions' => [
                        'preset' => 'full',
                        'height' => 400,
                        'removeButtons' => 'Subscript,Superscript,Flash,PageBreak,Iframe',
                    ],
                    'options' => [
                        'rows' => 10
                    ]
                ]);
                break;
            case self::TYPE_PASSWORD:
                $field->passwordInput(['value' => '']);
                break;
            default:
                $field->textInput();
        }
        if ($this->description) {
            $field->hint(\Yii::t('admin/params', $this->description));
        }
        return $field;
    }

    public function getViewValue()
    {
        switch ((int)$this->type) {
            case self::TYPE_BOOLEAN:
                return \Yii::$app->getFormatter()->asBoolean($this->value_bool);
            case self::TYPE_DROPDOWN:
                return \Yii::t('admin/params', $this->dropdown_options[$this->value_string]);
            default:
                return $this->getValue();
        }
    }

    public static function valueForLog(AppParam $model, $value): ?string
    {
        switch ((int)$model->type) {
            case self::TYPE_BOOLEAN:
                return \Yii::$app->getFormatter()->asBoolean($value);
            case self::TYPE_DROPDOWN:
                return \Yii::t('admin/params', $model->dropdown_options[$value]);
            default:
                return $value;
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        if (!$insert && $this->after_update) {
            $callable = $this->after_update['method'];
            $class = \Yii::$container->get($callable[0]);
            call_user_func_array([$class, $callable[1]], $this->after_update['params'] ?? []);
        }
    }
}
