<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\components\params\AppParams;
use app\events\ParamsSavedEvent;
use app\modules\admin\models\AppParam;
use app\rbac\Permissions;
use Yii;
use yii\base\Event;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\web\{Controller, Request, User};
use yii\widgets\ActiveForm;

class ParamsController extends Controller
{
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'permissions' => [Permissions::PERMISSION_WEBSETTINGS_VIEW]
                    ],
                ]
            ]
        ];
    }

    /**
     * @param Request $request
     * @param User $user
     * @param string|null $block
     * @return string|\yii\web\Response
     * @throws \yii\db\Exception
     */
    public function actionIndex(Request $request, User $user, ?string $block = null)
    {
        $canEdit = $user->can(Permissions::PERMISSION_WEBSETTINGS_EDIT);
        $models = AppParam::getList($block);
        if ($request->getIsPost() && Model::loadMultiple($models, $request->post()) && $canEdit) {
            if ($request->post('ajax') === 'params-form') {
                return $this->asJson(ActiveForm::validateMultiple($models));
            }
            $transaction = AppParam::getDb()->beginTransaction();
            $hasError = false;
            foreach ($models as $model) {
                // Не сохранять модель если это пароль, так как на форме значение удаляется
                if ($model->isPassword() && !$model->valueField) {
                    continue;
                }
                if (!$model->save()) {
                    $hasError = true;
                }
            }
            if (!$hasError) {
                $transaction->commit();
                Event::trigger(ParamsSavedEvent::class, ParamsSavedEvent::EVENT_NAME, new ParamsSavedEvent($models));
                \Yii::$app->getSession()->setFlash('success', Yii::t('admin/params', 'PARAMS_SAVED'));
            } else {
                $transaction->rollBack();
                \Yii::$app->getSession()->setFlash('error', Yii::t('admin/params', 'PARAMS_NOT_SAVED'));
            }
        }
        $blocks = [];
        foreach (AppParams::BLOCKS as $blockName => $label) {
            $url = ['index'];
            if ($blockName !== AppParams::BLOCK_MAIN) {
                $url['block'] = $blockName;
            }
            $blocks[] = [
                'label' => \Yii::t('admin/params', $label),
                'url' => $url,
                'active' => ($blockName === AppParams::BLOCK_MAIN && !$block) || ($block && $block === $blockName)
            ];
        }
        if (!$request->getIsPjax()) {
            $this->getView()->title = Yii::t('admin/params', 'PAGE_TITLE_INDEX');
            $this->getView()->params['breadcrumbs'] = [
                Yii::t('admin/params', 'BREADCRUMBS_INDEX')
            ];
        }
        return $this->render($canEdit ? 'form.twig' : 'view.twig', [
            'models' => $models,
            'blocks' => $blocks
        ]);
    }
}
