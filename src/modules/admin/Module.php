<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\admin;

use app\modules\admin\assets\AdminAsset;
use Yii;
use yii\filters\AccessControl;

/**
 * admin module definition class
 */
class Module extends \app\components\Module
{
    public $layout = '@app/modules/admin/views/layouts/admin.twig';

    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\admin\controllers';

    public function behaviors(): array
    {
        if (\Yii::$app instanceof \yii\web\Application) {
            return [
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ]
                ]
            ];
        }
        return [];
    }

    public function init()
    {
        AdminAsset::register(\Yii::$app->getView());
    }
}
