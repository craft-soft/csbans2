<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\install;

use yii\web\Application;
use yii\base\BootstrapInterface;
use app\components\params\AppParams;

/**
 * install module definition class
 */
class Module extends \yii\base\Module implements BootstrapInterface
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'app\modules\install\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        \Yii::$app->i18n->translations['install'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'forceTranslation' => true,
            'basePath' => '@app/modules/install/messages'
        ];
    }

    /**
     * @param Application $app
     * @return void
     */
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function() use ($app) {
            $this->checkNeedInstallOrUpdate($app);
        });
    }

    private function checkNeedInstallOrUpdate(Application $application): void
    {
        /** @var AppParams $params */
        $params = $application->get('appParams');
        if (!$params->isInstalled()) {
            $application->catchAll = ['install/default/install'];
        }
    }
}
