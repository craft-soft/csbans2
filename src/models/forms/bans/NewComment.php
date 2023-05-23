<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models\forms\bans;

use yii\base\Model;
use app\models\Comment;

class NewComment extends Model
{
    public ?string $name = null;

    public ?string $email = null;

    public ?string $comment = null;

    public function rules(): array
    {
        return [
            [['name', 'email', 'comment'], 'required'],
            ['name', 'string', 'max' => 64],
            ['email', 'string', 'max' => 128],
            ['email', 'email'],
            ['comment', 'string', 'max' => 1000],
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'name' => \Yii::t('bans', 'NEW_COMMENT_ATTRIBUTE_NAME'),
            'email' => \Yii::t('bans', 'NEW_COMMENT_ATTRIBUTE_EMAIL'),
            'comment' => \Yii::t('bans', 'NEW_COMMENT_ATTRIBUTE_COMMENT'),
        ];
    }

    public function save(int $banId, string $userIp, bool $needModeration = false): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $model = new Comment();
        $model->name = $this->name;
        $model->email = $this->email;
        $model->comment = $this->comment;
        $model->date = time();
        $model->addr = $userIp;
        $model->moderated = !$needModeration;
        $model->bid = $banId;
        return $model->save(false);
    }
}
