<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\server\query;

use app\assets\AppAsset;
use yii\web\AssetManager;
use yii\data\ArrayDataProvider;
use app\components\{Formatter, theme\Asset};

class ResultToView
{
    private Info $info;

    private Formatter $formatter;
    private AssetManager $assetManager;

    /**
     * @param Info $info
     * @param Formatter $formatter
     * @param AssetManager $assetManager
     */
    public function __construct(Info $info, Formatter $formatter, AssetManager $assetManager)
    {
        $this->info = $info;
        $this->formatter = $formatter;
        $this->assetManager = $assetManager;
    }

    /**
     * The name of the field in the player's array to sort by.
     * If you specify a minus before the field name, sorting will occur in reverse order. Without a minus - in direct
     * @param string|null $playersSort
     * @return array
     * @throws \yii\base\InvalidConfigException
     */
    public function format(?string $playersSort = null): array
    {
        $result = $this->info->toArray();
        $result['status'] = $this->formatter->asServerStatus($this->info->isOnline());
        $result['map']['image'] = $this->assetManager->publish($this->getMapImage())[1];
        if (!$this->info->isOnline()) {
            return $result;
        }
        $osName = $this->info->getOs();
        $result['os'] = $osName ? ucfirst($osName) : '';
        $result['osIcon'] = $this->getOsIcon($osName);
        $result['gameIcon'] = $this->getGameIcon();
        $result['vacIcon'] = $this->getVacIcon();
        $result['players'] = $this->getPlayers($result['players'], $playersSort);
        return $result;
    }

    private function getPlayers(array $players, ?string $sort = null): array
    {
        $config = [
            'allModels' => $players,
            'sort' => [
                'defaultOrder' => ['score' => SORT_DESC],
                'attributes' => [
                    'name',
                    'score',
                    'time'
                ],
            ],
        ];
        if ($sort) {
            $config['sort']['params'] = ['sort' => $sort];
        }
        $dataProvider = new ArrayDataProvider($config);
        $orders = [];
        foreach ($dataProvider->getSort()->getOrders() as $field => $direction) {
            $orders[$field] = $direction === SORT_ASC ? 'asc' : 'desc';
        }
        return [
            'players' => $dataProvider->getModels(),
            'orders' => $orders
        ];
    }

    private function getUrl(string $pathMethod, string $pathPostfix): ?string
    {
        $path = $this->pathWithExtension("{$this->getPath($pathMethod)}/$pathPostfix");
        if ($path) {
            $url = $this->assetManager->publish($path);
            if (!empty($url[1])) {
                return $url[1];
            }
        }
        return null;
    }

    private function getOsIcon(?string $osName): ?string
    {
        if (!$osName) {
            return null;
        }
        return $this->getUrl('getOsIconsPath', $osName);
    }

    private function getGameIcon(): ?string
    {
        return $this->getUrl('getGamesIconsPath', $this->info->getGame());
    }

    private function getVacIcon(): ?string
    {
        return $this->getUrl('getSecureIconsPath', $this->info->isSecure() ? 'vac' : 'no_vac');
    }

    private function getMapImage(): string
    {
        $basePath = $this->getPath('getMapsImagesPath');
        if (!$this->info->isOnline()) {
            return $this->noresponseMapImage();
        }
        $mapPath = $this->pathWithExtension("$basePath/{$this->info->getGame()}/{$this->info->getMap()->getCurrent()}");
        if ($mapPath) {
            return $mapPath;
        }
        return $this->defaultMapImage();
    }

    public function noresponseMapImage(): string
    {
        return $this->pathWithExtension("{$this->getPath('getMapsImagesPath')}/noresponse");
    }

    public function defaultMapImage(): string
    {
        return $this->pathWithExtension("{$this->getPath('getMapsImagesPath')}/noimage");
    }

    private function pathWithExtension(string $path): ?string
    {
        foreach (['svg', 'png', 'jpg', 'gif'] as $extension) {
            $fullPath = "$path.$extension";
            if (is_file($fullPath)) {
                return $fullPath;
            }
        }
        return null;
    }

    private function getPath(string $method): ?string
    {
        /** @var Asset $themeBundle */
        $themeBundle = $this->assetManager->getBundle(Asset::class);
        $path = $themeBundle->{$method}();
        if (!$path) {
            /** @var AppAsset $appAsset */
            $appAsset = $this->assetManager->getBundle(AppAsset::class);
            $path = $appAsset->{$method}();
        }
        return $path;
    }
}
