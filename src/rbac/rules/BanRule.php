<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\rbac\rules;

use app\models\Ban;
use yii\base\InvalidConfigException;
use yii\rbac\Rule;

/**
 * @inheritDoc
 */
class BanRule extends Rule
{
    public $name = 'banRule';

    /**
     * @inheritDoc
     */
    public function execute($user, $item, $params): bool
    {
        $user = \Yii::$app->getUser();
        if ($user->getIsGuest()) {
            return false;
        }
        if (!empty($params['ban']) && $params['ban'] instanceof Ban) {
            return $params['ban']->admin_nick === $user->getIdentity()->username;
        }
        if (empty($params['banId'])) {
            throw new InvalidConfigException("\$params['banId'] or \$params['ban'] is required");
        }
        return Ban::find()
            ->where(['bid' => $params['banId'], 'admin_nick' => $user->getIdentity()->username])
            ->exists();
    }
}
