<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\bootstrap;

use yii\web\Application;
use yii\base\BootstrapInterface;

class BeforeRequestDispatcher implements BootstrapInterface
{
    /**
     * @param Application $app
     * @return void
     */
    public function bootstrap($app)
    {
        $app->on(Application::EVENT_BEFORE_REQUEST, function() use ($app) {
            $this->updateUserActionTime($app);
        });
    }

    private function updateUserActionTime(Application $application): void
    {
        $application->getUser()->updateAction();
    }
}
