<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use yii\web\Controller;
use app\services\stats\StatsService;

class DefaultController extends Controller
{
    public function actionIndex(?string $period = null): string
    {
        if (!$period) {
            $period = date('d.m.Y');
        }
        $service = new StatsService();
        $service->setPeriod($period);
        return $this->render('index.twig', [
            'bansBoxes' => $service->getBoxes(),
            'infoBoxes' => $service->getInfo()
        ]);
    }
}
