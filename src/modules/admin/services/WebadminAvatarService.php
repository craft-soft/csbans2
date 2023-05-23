<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\admin\services;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use yii\web\UploadedFile;
use app\models\WebadminProfile;
use yii\helpers\{FileHelper, Inflector};

class WebadminAvatarService
{
    private const AVATARS_BASE_PATH = '@app/data/webadminsAvatars';

    private const DEFAULT_AVATAR_FILE = 'default_avatar.svg';

    private WebadminProfile $profile;

    /**
     * @param WebadminProfile $profile
     */
    public function __construct(WebadminProfile $profile)
    {
        $this->profile = $profile;
    }

    public function uploadAvatar(UploadedFile $file): bool
    {
        $this->deleteAvatar();
        $this->profile->avatar_name = "{$this->profile->admin->username}.{$file->getExtension()}";
        $path = $this->getPath();
        FileHelper::createDirectory(dirname($path));
        try {
            $imagine = new Imagine();
            $image = $imagine->open($file->tempName);
            $size = $image->getSize();
            if ($size->getWidth() > 640) {
                $ratio = $size->getWidth() / $size->getHeight();
                $image->resize(new Box(640, 640 / $ratio));
            }
            $result = $image->save($path) && $this->profile->save(false, ['avatar_name']);
            FileHelper::unlink($file->tempName);
            return $result;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function getUrl(): string
    {
        $path = $this->getPath();
        if (!$path) {
            return self::defaultAvatarUr();
        }
        return \Yii::$app->getAssetManager()->publish($path)[1];
    }

    public static function defaultAvatarUr(): string
    {
        $path = \Yii::getAlias('@theme/assets/images/default_avatar.svg');
        if (!is_file($path)) {
            $path = \Yii::getAlias(self::AVATARS_BASE_PATH . '/' . self::DEFAULT_AVATAR_FILE);
        }
        return \Yii::$app->getAssetManager()->publish($path)[1];
    }

    public function getPath(): ?string
    {
        if (!$this->profile->avatar_name) {
            return null;
        }
        return \Yii::getAlias(self::AVATARS_BASE_PATH . "/{$this->profile->avatar_name}");
    }

    public function deleteAvatar(): bool
    {
        $path = $this->getPath();
        if ($path && is_file($path)) {
            FileHelper::unlink($path);
        }
        $this->profile->avatar_name = null;
        return $this->profile->save();
    }

    public function hasAvatar(): bool
    {
        return $this->profile->avatar_name && is_file($this->getPath());
    }
}
