<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap;

use yii\base\{ActionEvent, BootstrapInterface, Controller};

class RemoveSpaces implements BootstrapInterface
{
    /**
     * @inheritDoc
     */
    public function bootstrap($app)
    {
        $app->on(Controller::EVENT_AFTER_ACTION, function(ActionEvent $event) {
            if (YII_ENV_PROD && $event->result) {
                if (gettype($event->result) === 'string' && preg_match('/>\s+</', $event->result)) {
                    $event->result = trim(preg_replace('/>\s+</', '><', $event->result));
                }
            }
        });
    }
}
