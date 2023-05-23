<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\controllers;

use app\components\User;
use app\modules\admin\{models\WebadminProfile, services\WebadminAvatarService};
use app\rbac\Permissions;
use Yii;
use yii\filters\VerbFilter;
use yii\web\{Controller, ForbiddenHttpException, Request, Session, UploadedFile};
use yii\widgets\ActiveForm;

class ProfileController extends Controller
{
    public function behaviors(): array
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete-avatar' => ['delete', 'ajax'],
                ]
            ],
        ];
    }

    public function actionIndex(User $user, Request $request, Session $session, ?int $id = null)
    {
        $baseId = $id;
        $profile = $this->findProfile($user, $id);
        $avatar = new WebadminAvatarService($profile);
        if ($profile->load($request->post())) {
            $profile->avatar = UploadedFile::getInstance($profile, 'avatar');
            if ($request->post('ajax') === 'profile-form') {
                return $this->asJson(ActiveForm::validate($profile));
            }
            if ($profile->save()) {
                if ($profile->avatar) {
                    $avatar->uploadAvatar($profile->avatar);
                }
                $session->setFlash('success', \Yii::t('admin/profile', 'PROFILE_SAVED'));
                if (!$request->getIsPjax()) {
                    return $this->refresh();
                }
            }
        }
        if (!$request->getIsPjax()) {
            if (!$id) {
                $this->getView()->title = Yii::t('admin/profile', 'PAGE_TITLE');
                $this->getView()->params['breadcrumbs'] = [
                    $this->getView()->title,
                ];
            } else {
                $this->getView()->title = Yii::t('admin/profile', 'PAGE_TITLE_ADMIN', ['username' => $profile->admin->username]);
                $this->getView()->params['breadcrumbs'] = [
                    [
                        'url' => ['/admin/webadmins/index'],
                        'label' => Yii::t('admin/webadmins', 'BREADCRUMBS_INDEX')
                    ],
                    $this->getView()->title,
                ];
            }
        }
        return $this->render('index.twig', [
            'profile' => $profile,
            'avatar' => $avatar->getUrl(),
            'hasAvatar' => $avatar->hasAvatar(),
            'baseId' => $baseId,
            'identity' => $user->getIdentity(),
            'pageTitle' => $id ?
                Yii::t('admin/profile', 'PAGE_TITLE_ADMIN', ['username' => $profile->admin->username])
                : Yii::t('admin/profile', 'PAGE_TITLE')
        ]);
    }

    public function actionDeleteAvatar(User $user, Session $session, ?int $id = null): bool
    {
        $profile = $this->findProfile($user, $id);
        $avatar = new WebadminAvatarService($profile);
        if ($avatar->deleteAvatar()) {
            $session->setFlash('success', \Yii::t('admin/profile', 'AVATAR_DELETED'));
            return true;
        }
        $session->setFlash('error', \Yii::t('admin/profile', 'AVATAR_NOT_DELETED'));
        return false;
    }

    private function findProfile(User $user, ?int $id = null): WebadminProfile
    {
        if (!$id) {
            $id = $user->getId();
        } else if ($id !== $user->getId() && !$user->can(Permissions::PERMISSION_WEBADMINS_EDIT)) {
            throw new ForbiddenHttpException();
        }
        $profile = WebadminProfile::findOne(['admin_id' => $id]);
        if (!$profile) {
            $profile = new WebadminProfile();
            $profile->admin_id = $id;
        }
        return $profile;
    }
}
