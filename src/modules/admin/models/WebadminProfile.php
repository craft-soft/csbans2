<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\models;

use app\models\Webadmin;
use yii\web\UploadedFile;

/**
 * @inheritDoc
 */
class WebadminProfile extends \app\models\WebadminProfile
{
    /**
     * @var null|string|UploadedFile
     */
    public $avatar = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['admin_id'], 'required'],
            [['admin_id'], 'integer'],
            [['avatar'], 'image', 'maxSize' => 2 * 1024 * 1024],
            [['first_name'], 'string', 'max' => 24],
            [['last_name'], 'string', 'max' => 32],
            [['avatar_name'], 'string', 'max' => 40],
            [['admin_id'], 'unique'],
            [['language'], 'in', 'range' => array_keys(\Yii::$app->appParams->languages())],
            [['admin_id'], 'exist', 'skipOnError' => true, 'targetClass' => Webadmin::class, 'targetAttribute' => ['admin_id' => 'id']],
        ];
    }
}
