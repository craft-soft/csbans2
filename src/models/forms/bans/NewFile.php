<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\models\forms\bans;

use Yii;
use app\models\File;
use yii\web\UploadedFile;
use yii\helpers\{ArrayHelper, FileHelper};

class NewFile extends NewComment
{
    /**
     * @var UploadedFile|string|null
     */
    public $file;

    public function rules(): array
    {
        return ArrayHelper::merge(parent::rules(), [
            ['file', 'file', 'extensions' => Yii::$app->appParams->demo_file_types, 'skipOnEmpty' => false]
        ]);
    }

    public function attributeLabels(): array
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'file' => \Yii::t('bans', 'NEW_COMMENT_ATTRIBUTE_FILE')
        ]);
    }

    public function beforeValidate(): bool
    {
        if (!parent::beforeValidate()) {
            return false;
        }
        $this->file = UploadedFile::getInstance($this, 'file');
        return true;
    }

    public function upload(int $banId, string $userIp, bool $needModeration = false): bool
    {
        if (!$this->validate()) {
            return false;
        }
        $model = new File();
        $model->bid = $banId;
        $model->addr = $userIp;
        $model->name = $this->name;
        $model->email = $this->email;
        $model->comment = $this->comment;
        $model->moderated = !$needModeration;
        $model->demo_real = \Yii::$app->getSecurity()->generateRandomString(12) . ".{$this->file->getExtension()}";
        $model->demo_file = $this->file->name;
        $model->file_size = $this->file->size;
        $model->upload_time = time();
        $path = Yii::getAlias($model->getFilePath());
        FileHelper::createDirectory(dirname($path));
        return $this->file->saveAs($path) && $model->save(false);
    }
}
