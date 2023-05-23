<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\web;

use Yii;
use yii\base\ViewContextInterface;
use yii\base\ViewNotFoundException;
use yii\helpers\ArrayHelper;
use app\modules\admin\Module;
use yii\helpers\FileHelper;
use yii\helpers\Html;
use yii\twig\ViewRenderer;

/**
 * @inheritDoc
 * @property-read array $breadcrumbs
 */
class View extends \yii\web\View
{
    public function init()
    {
        parent::init();
        $jsonParams = json_encode(Yii::$app->appParams->forFrontend());
        $this->registerJs("appParams.configure($jsonParams);", self::POS_HEAD);
    }

    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        if (Yii::$app->controller->module instanceof Module) {
            $breadcrumbs[] = [
                'url' => ['/admin/default/index'],
                'label' => Yii::t('admin/main', 'ADMIN_CENTER_BREADCRUMB_LABEL')
            ];
        }
        return ArrayHelper::merge($breadcrumbs, $this->params['breadcrumbs'] ?? []);
    }

    public function setTitle(string $title)
    {
        $this->title = Yii::$app->name . " - $title";
    }

    public function renderDynamicContent(string $content): string
    {
        $cacheFileAlias = '@runtime/cache/view/' . Yii::$app->getSecurity()->generateRandomString(12) . ".twig";
        $cacheFile = \Yii::getAlias($cacheFileAlias);
        FileHelper::createDirectory(dirname($cacheFile));
        file_put_contents($cacheFile, Html::decode($content));
        return $this->render($cacheFileAlias);
    }
}
