<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\install\controllers;

use app\components\params\AppParams;
use app\modules\install\{models\Install,
    services\exceptions\AddAdminException,
    services\exceptions\CreateConfigException,
    services\exceptions\DbConnectException,
    services\exceptions\GeneratePermissionsException,
    services\exceptions\PermissionsException,
    services\InstallService};
use app\modules\install\services\exceptions\{MigrationsException};
use yii\web\{BadRequestHttpException, Controller, NotFoundHttpException, Request};
use yii\widgets\ActiveForm;

/**
 * Default controller for the `install` module
 */
class DefaultController extends Controller
{
    public function actionInstall(AppParams $appParams, Request $request)
    {
        $step = $request->get('step');
        if ($appParams->isInstalled() && !$step && !$request->getIsAjax()) {
            throw new NotFoundHttpException();
        }
        $model = new Install();
        $service = new InstallService($model);
        if ($request->getIsAjax() && $model->load($request->post())) {
            if ($request->post('ajax') === 'install-form') {
                return $this->asJson(ActiveForm::validate($model));
            }
            if ($step && $model->validate()) {
                try {
                    $nextStep = $service->runStep($step);
                    return $this->asJson([
                        'nextStep' => $nextStep,
                        'done' => $service->isDone()
                    ]);
                } catch (PermissionsException $e) {
                    $error = \Yii::t('install', 'ERROR_PERMISSIONS', [
                        'directories' => '<ul><li>' . implode('</li><li>', $e->getDirectories()) . '</li></ul>'
                    ]);
                } catch (DbConnectException $e) {
                    $error = $e->getMessage() ?: \Yii::t('install', 'ERROR_CONNECTION');
                } catch (CreateConfigException $e) {
                    $error = \Yii::t('install', 'ERROR_CONFIG');
                } catch (MigrationsException $e) {
                    $error = \Yii::t('install', 'ERROR_MIGRATIONS');
                } catch (GeneratePermissionsException $e) {
                    $error = \Yii::t('install', 'ERROR_GENERATE_PERMISSIONS');
                } catch (AddAdminException $e) {
                    $error = \Yii::t('install', 'ERROR_ADD_ADMIN');
                } catch (\Throwable $e) {
                    $error = \Yii::t('install', 'ERROR_UNKNOWN_ERROR');
                }
                return $this->asJson([
                    'error' => $error
                ]);
            }
            throw new BadRequestHttpException();
        }
        return $this->render('install.twig', [
            'model' => $model,
            'firstStep' => InstallService::STEP_CONFIG,
            'steps' => [
                InstallService::STEP_CONFIG => \Yii::t('install', 'STEP_CONFIG'),
                InstallService::STEP_MIGRATIONS => \Yii::t('install', 'STEP_MIGRATIONS'),
                InstallService::STEP_PERMISSIONS=> \Yii::t('install', 'STEP_PERMISSIONS'),
                InstallService::STEP_ADMIN => \Yii::t('install', 'STEP_ADMIN'),
                InstallService::STEP_DOWNLOAD_IP_GEO_DATA => \Yii::t('install', 'STEP_DOWNLOAD_IP_GEO_DATA'),
            ],
        ]);
    }
}
