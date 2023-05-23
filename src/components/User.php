<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components;

use app\components\params\AppParams;
use app\models\Webadmin;
use app\modules\admin\models\WebadminAuth;
use app\modules\admin\services\WebadminAvatarService;
use yii\web\Cookie;

/**
 * @inheritDoc
 *
 * @property-read null $name
 * @property-read Webadmin $identity
 */
class User extends \yii\web\User
{
    public function getName(): ?string
    {
        if ($this->getIsGuest()) {
            return null;
        }
        return $this->getIdentity()->username;
    }

    /**
     * @inheritDoc
     */
    protected function afterLogin($identity, $cookieBased, $duration)
    {
        parent::afterLogin($identity, $cookieBased, $duration);
        $this->getIdentity()->resetTry();
        WebadminAuth::createForAdmin(
            $this->getIdentity(),
            \Yii::$app->getRequest()->getUserIP(),
            \Yii::$app->getRequest()->getUserAgent(),
            \Yii::$app->getSession()->getId(),
        );
    }

    public function isAdmin(): bool
    {
        return (bool)\Yii::$app->getAuthManager()->getPermissionsByUser($this->getId());
    }

    public function avatar(): string
    {
        if ($this->getIsGuest()) {
            return WebadminAvatarService::defaultAvatarUr();
        }
        return (new WebadminAvatarService($this->getIdentity()->profile()))->getUrl();
    }

    public function getLanguage(): ?string
    {
        if ($this->getIsGuest()) {
            return \Yii::$app->getRequest()->getCookies()->getValue('__language');
        }
        if ($this->getIdentity()->profile) {
            return $this->getIdentity()->profile->language ?: null;
        }
        return null;
    }

    public function setLanguage(string $language)
    {
        if ($this->getIsGuest()) {
            \Yii::$app->getResponse()->getCookies()->add(new Cookie([
                'name' => '__language',
                'value' => $language
            ]));
        }
    }

    public function canAddComments(): bool
    {
        $param = (int)\Yii::$app->appParams->comments;
        if ($param === AppParams::VALUE_NONE) {
            return false;
        }
        if ($this->getIsGuest()) {
            return $param === AppParams::VALUE_ALL;
        }
        return in_array($param, [AppParams::VALUE_ALL, AppParams::VALUE_USERS]);
    }

    public function canAddFiles(): bool
    {
        $param = (int)\Yii::$app->appParams->demo_upload_enabled;
        if ($param === AppParams::VALUE_NONE) {
            return false;
        }
        if ($this->getIsGuest()) {
            return $param === AppParams::VALUE_ALL;
        }
        return in_array($param, [AppParams::VALUE_ALL, AppParams::VALUE_USERS]);
    }

    public function updateAction()
    {
        if (!$this->getIsGuest()) {
            $identity = $this->getIdentity();
            $identity->last_action = time();
            $identity->save(false, ['last_action']);
        }
    }

    /**
     * @param bool $autoRenew
     * @return bool|Webadmin|null
     * @throws \Throwable
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public function getIdentity($autoRenew = true)
    {
        if (!\Yii::$app->appParams->isInstalled()) {
            return null;
        }
        return parent::getIdentity($autoRenew);
    }
}
