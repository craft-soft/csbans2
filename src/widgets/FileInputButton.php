<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\widgets;

use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;
use yii\helpers\Html;

class FileInputButton extends InputWidget
{
    /**
     * @var string|null|false
     */
    public $label = null;

    public array $labelOptions = [];

    public function run()
    {
        $this->field->template = "{input}\n{hint}\n{error}";
        $this->field->options['class'] = '';
        if ($this->label === null && $this->hasModel()) {
            $label = $this->model->getAttributeLabel($this->attribute);
        } else if ($this->label === false) {
            $label = '';
        } else {
            $label = $this->label;
        }
        $label = Html::tag('span', $label);
        $field = $this->hasModel() ? $this->forModel() : $this->withoutModel();
        $this->labelOptions['id'] = $this->options['id'] . '-label';
        $label = Html::label(
            $label . PHP_EOL . $field,
            null,
            $this->labelOptions
        );
        return Html::tag('div', $label, ['class' => 'col-12']);
    }

    private function forModel(): string
    {
        return Html::activeFileInput($this->model, $this->attribute, $this->inputOptions());
    }

    private function withoutModel(): string
    {
        return Html::fileInput($this->name, null, $this->inputOptions());
    }

    private function inputOptions(): array
    {
        $options = $this->options;
        $options['hidden'] = true;
        return $options;
    }
}
