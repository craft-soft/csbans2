<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\modules\install\controllers;

use app\components\params\AppParams;
use app\components\systemInfo\SystemInfo;
use app\modules\install\{models\Install,
    models\Language,
    services\exceptions\AddAdminException,
    services\exceptions\CreateConfigException,
    services\exceptions\DbConnectException,
    services\exceptions\GeneratePermissionsException,
    services\exceptions\PermissionsException,
    services\InstallService};
use app\modules\install\services\exceptions\{MigrationsException};
use yii\web\{BadRequestHttpException, Controller, NotFoundHttpException, Request, Session};
use yii\widgets\ActiveForm;

/**
 * Default controller for the `install` module
 */
class InstallController extends Controller
{
    private const SESSION_LANG_KEY = 'install_language';

    public function actionIndex(AppParams $appParams, Request $request, SystemInfo $systemInfo, Session $session)
    {
        $langFromSession = $session->get(self::SESSION_LANG_KEY);
        if ($langFromSession) {
            \Yii::$app->language = $langFromSession;
        }
        $step = $request->get('step');
        if ($appParams->isInstalled() && !$step && !$request->getIsAjax()) {
            throw new NotFoundHttpException();
        }
        $this->getView()->params['withoutNoty'] = true;
        $language = new Language();
        $language->language = \Yii::$app->language;
        $langLoaded = false;
        if ($language->load($request->post())) {
            $langLoaded = true;
            if ($request->post('ajax') === 'language-form') {
                return $this->asJson(ActiveForm::validate($language));
            }
            if ($language->validate()) {
                $session->set(self::SESSION_LANG_KEY, $language->language);
                \Yii::$app->language = $language->language;
            }
        }
        $l = \Yii::$app->language;
        $systemModules = $systemInfo->systemModules();
        $systemVariables = $systemInfo->systemVariables(true);
        $canInstall = !$systemVariables->isCritical() && !$systemModules->isCritical();
        $model = new Install();
        if ($request->getIsAjax() && $model->load($request->post()) && $canInstall) {
            return $this->handleInstall($request, $model, $session, $step);
        }
        if (!$langLoaded && !$request->getIsPjax()) {
            return $this->render('step1.twig',[
                'langs' => $appParams->languages(),
                'model' => $language
            ]);
        }
        return $this->render('step2.twig', [
            'model' => $model,
            'firstStep' => InstallService::STEP_CONFIG,
            'modules' => $systemModules->getItems(),
            'variables' => $systemVariables->getItems(),
            'canInstall' => $canInstall,
            'steps' => [
                InstallService::STEP_CONFIG => \Yii::t('install', 'STEP_CONFIG'),
                InstallService::STEP_MIGRATIONS => \Yii::t('install', 'STEP_MIGRATIONS'),
                InstallService::STEP_PERMISSIONS=> \Yii::t('install', 'STEP_PERMISSIONS'),
                InstallService::STEP_ADMIN => \Yii::t('install', 'STEP_ADMIN'),
                InstallService::STEP_DOWNLOAD_DB_IP_CITY => \Yii::t('install', 'STEP_DOWNLOAD_DB_IP_CITY'),
                InstallService::STEP_DOWNLOAD_GEOLITE_CITY => \Yii::t('install', 'STEP_DOWNLOAD_GEOLITE_CITY'),
                InstallService::STEP_DOWNLOAD_IPLOCATION => \Yii::t('install', 'STEP_DOWNLOAD_IPLOCATION'),
            ],
        ]);
    }

    private function handleInstall(Request $request, Install $model, Session $session, ?string $step = null): \yii\web\Response
    {
        $service = new InstallService($model, $request->getHostInfo(), $session->get(self::SESSION_LANG_KEY));
        if ($request->post('ajax') === 'install-form') {
            return $this->asJson(ActiveForm::validate($model));
        }
        if ($step && $model->validate()) {
            try {
                $nextStep = $service->runStep($step);
                $isDone = $service->isDone();
                if ($isDone) {
                    $session->remove(self::SESSION_LANG_KEY);
                }
                return $this->asJson([
                    'nextStep' => $nextStep,
                    'done' => $isDone
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
}
