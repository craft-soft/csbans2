<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\components\ipGeo\IpGeo;
use app\rbac\Permissions;
use yii\filters\{AccessControl, VerbFilter};
use yii\web\{BadRequestHttpException, Controller, Request, Response};

class ToolsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'ip-info' => ['post', 'ajax'],
                ]
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_IP_VIEW],
                    ],
                ],
            ],
        ];
    }

    public function actionIpInfo(Request $request, Response $response, IpGeo $ipGeo): ?array
    {
        $response->format = Response::FORMAT_JSON;
        $ip = $request->post('ip');
        if (!$ip) {
            throw new BadRequestHttpException();
        }
        $data = $ipGeo->getData($ip);
        if (!$data) {
            return [
                'error' => \Yii::t('tools', 'IP_MODAL_NO_DATA', [
                    'ip' => $ip
                ])
            ];
        }
        $result = [
            'label' => \Yii::t('tools', 'IP_MODAL_LABEL', [
                'ip' => $ip
            ]),
            'info' => [],
        ];
        if ($data->getCountry()) {
            $result['info'][] = [
                'label' => \Yii::t('ipInfo', 'LABEL_COUNTRY'),
                'value' => $data->getCountry(),
            ];
        }
        if ($data->getRegionName()) {
            $result['info'][] = [
                'label' => \Yii::t('ipInfo', 'LABEL_REGION_NAME'),
                'value' => $data->getRegionName(),
            ];
        }
        if ($data->getCity()) {
            $result['info'][] = [
                'label' => \Yii::t('ipInfo', 'LABEL_CITY'),
                'value' => $data->getCity(),
            ];
        }
        $timeZone = $data->getTimezone();
        if ($timeZone) {
            $date = new \DateTime();
            if($timeZone !== \Yii::$app->getTimeZone()) {
                $date->setTimezone(new \DateTimeZone($timeZone));
            }
            $result['info'][] = [
                'label' => \Yii::t('ipInfo', 'LABEL_DATETIME'),
                'value' => $date->format('d.m.Y H:i:s'),
            ];
        }
        $currentDate = new \DateTime();
        $currentDate->getTimezone()->getName();
        if ($data->getLat() && $data->getLon()) {
            $result['coords'] = [
                'lat' => $data->getLat(),
                'lon' => $data->getLon(),
            ];
        }
        return $result;
    }
}
