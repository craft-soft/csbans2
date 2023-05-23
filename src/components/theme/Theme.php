<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\components\theme;

class Theme extends \yii\base\Theme
{
    private bool $isCurrent = false;

    private ?string $directory = null;
    private ?string $fullPath = null;
    private ?string $name = null;
    private ?string $description = null;
    private ?string $preview = null;
    private ?string $version = null;
    private ?string $link = null;

    /**
     * @var array Массив с ассетами.
     * Указывать относительный путь, относительно папки assets в корне папки темы
     * Можно так же указывать параметры подключения и зависимости
     * Более подробно смотрите тут https://www.yiiframework.com/doc/api/2.0/yii-web-assetbundle
     */
    private array $assets = [
        'js' => [],
        'css' => [],
        'depends' => []
    ];

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->assets;
    }

    /**
     * @param array $assets
     * @return Theme
     */
    public function setAssets(array $assets): Theme
    {
        $this->assets = $assets;
        return $this;
    }

    /**
     * @var Author[]
     */
    private array $authors = [];

    public $pathMap = [
        '@app/views' => '@root/themes/default',
        '@app/modules' => '@root/themes/default',
    ];

    /**
     * @return bool
     */
    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return mixed
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Name of theme
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Theme author information
     * @return Author[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    /**
     * Text description of theme
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Formatted URL address of theme's preview image
     * If preview image not configured on theme, null be returned
     * @return string|null
     */
    public function getPreview(): ?string
    {
        return $this->preview;
    }

    /**
     * Theme's version information
     * @return string|null
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Theme's link url
     * @return null|string
     */
    public function getLink(): ?string
    {
        return $this->link;
    }

    /**
     * @return Theme
     */
    public function setIsCurrent(): Theme
    {
        $this->isCurrent = true;
        return $this;
    }

    /**
     * @param string|null $name
     * @return Theme
     */
    public function setName(?string $name): Theme
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param array $authors
     * @return Theme
     */
    public function setAuthors(array $authors): Theme
    {
        $this->authors = $authors;
        return $this;
    }

    /**
     * @param string|null $description
     * @return Theme
     */
    public function setDescription(?string $description): Theme
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @param string|null $preview
     * @return Theme
     */
    public function setPreview(?string $preview): Theme
    {
        $this->preview = $preview;
        return $this;
    }

    /**
     * @param string|null $version
     * @return Theme
     */
    public function setVersion(?string $version): Theme
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @param string|null $link
     * @return Theme
     */
    public function setLink(?string $link): Theme
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @param string|null $directory
     * @return Theme
     */
    public function setDirectory(?string $directory): Theme
    {
        $this->directory = $directory;
        return $this;
    }

    /**
     * @param ?string $fullPath
     * @return Theme
     */
    public function setFullPath(?string $fullPath): Theme
    {
        $this->fullPath = $fullPath;
        return $this;
    }

    public function addAuthor(Author $author)
    {
        $this->authors[] = $author;
    }
}
