<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\components\theme;

use Yii;
use yii\helpers\FileHelper;
use yii\web\{Application, AssetBundle, View};
use yii\base\{BootstrapInterface, InvalidConfigException};

/**
 * Class Component
 * @package app\components\theme
 *
 * @property-read string $baseUrl
 * @property null|string $userTheme
 * @property-read \app\components\theme\Theme[] $themes
 * @property-write \yii\web\View $assets
 * @property $current Theme
 */
class Factory extends \yii\base\Component implements BootstrapInterface
{
    const THEME_COOKIE_NAME = 'csb2_theme';

    public string $defaultTheme = 'default';

    private ?Theme $current = null;

    private ?string $basePath = null;

    private ?string $name = null;

    private ?array $_allThemes = null;

    /**
     * @var AssetBundle
     */
    private AssetBundle $bundle;

    /**
     * @var Application
     */
    private Application $app;

    /**
     * @inheritdoc
     * @param Application $app
     */
    public function bootstrap($app)
    {
        $this->app = $app;
        $app->on(Application::EVENT_BEFORE_ACTION, [$this, 'configureTheme']);
    }

    /**
     * Get current theme
     * @return Theme
     * @throws InvalidConfigException
     */
    public function getCurrent(): Theme
    {
        if($this->current === null) {
            $all = $this->getThemes();
            $this->current = $all[$this->getName()] ?? null;
            $this->current->setIsCurrent();
        }
        return $this->current;
    }

    /**
     * Configure yii theme object
     * @throws InvalidConfigException
     */
    public function configureTheme()
    {
        $themePath = $this->getBasePath();
        Yii::setAlias('@theme', $themePath);
        $view = $this->app->getView();
        if($view instanceof \yii\web\View && Yii::$app->controller->module->id !== 'admin') {
            $this->getCurrent();
            $this->setAssets($view);
            $view->theme = Yii::createObject([
                'class' => Theme::class,
                'pathMap' => [
                    '@app/views' => "$themePath/views",
                    '@app/modules' => $themePath,
                    '@app/widgets' => "$themePath/widgets",
                ]
            ]);
        }
        unset($view);
    }

    /**
     * Get base path to all themes
     * @return string
     */
    public function getBasePath(): string
    {
        if(!$this->basePath) {
            $this->basePath = Yii::getAlias("@themes/{$this->getName()}");
        }
        return $this->basePath;
    }

    /**
     * Get current theme name
     * @return string
     */
    public function getName(): ?string
    {
        if(!$this->name) {
            $this->name = $this->getUserTheme();
            if(!$this->name) {
                $this->name = $this->defaultTheme;
            }
        }
        return $this->name;
    }

    /**
     * Apply theme by name
     * @param $name
     * @throws InvalidConfigException
     */
    public function applyTheme($name)
    {
        if(!$this->isThemeExists($name)) {
            throw new InvalidConfigException("Theme \"{$name}\" does not exists");
        }
        $this->name = $name;
        $this->setUserTheme($name);

        $this->trigger(ThemeEvent::EVENT_THEME_CHANGED, new ThemeEvent([
            'theme' => $this->getCurrent()
        ]));
    }


    /**
     * Get all themes list
     * @return Theme[]
     * @throws InvalidConfigException
     */
    public function getThemes(): array
    {
        if($this->_allThemes === null) {
            $this->_allThemes = [];
            foreach(self::getList() as $name => $config) {
                $theme = $this->buildTheme($name, $config);
                $this->_allThemes[$name] = $theme;
            }
        }
        return $this->_allThemes;
    }

    private static ?array $all = null;
    public static function getList(bool $namesOnly = false): ?array
    {
        if (self::$all === null) {
            self::$all = [];
            foreach(FileHelper::findDirectories(Yii::getAlias('@themes'), ['recursive' => false]) as $theme) {
                if(is_dir($theme)) {
                    $name = basename($theme);
                    $configFile = $theme . DIRECTORY_SEPARATOR . 'theme.php';
                    if(!is_file($configFile)) {
                        Yii::warning("$configFile: config file not found, Ignore this theme");
                        continue;
                    }
                    self::$all[$name] = include($configFile);
                }
            }
        }
        if ($namesOnly) {
            return array_combine(
                array_keys(self::$all),
                array_column(self::$all, 'name')
            );
        }
        return self::$all;
    }

    /**
     * Checks if theme exists by name
     * @param string $name Name of theme whom we want to check
     * @return bool false if theme not exists and true if theme exist
     * @throws InvalidConfigException
     */
    public function isThemeExists(string $name): bool
    {
        return array_key_exists($name, $this->getThemes());
    }

    /**
     * Get base url to current theme
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->bundle->baseUrl;
    }

    /**
     * Get name of user selected theme
     * @return string|null
     * TODO: сделать дефолтную тему, выбор из админки и из настроек пользователя
     */
    private function getUserTheme(): ?string
    {
        return $this->app->getRequest()->getCookies()->getValue(self::THEME_COOKIE_NAME);
    }

    /**
     * Write name of selected theme to client's storage
     * @param string $name The name of the theme to be written to the storage
     * TODO: сделать дефолтную тему, выбор из админки и из настроек пользователя
     */
    private function setUserTheme(string $name)
    {
        $this->app->getResponse()->getCookies()->add(new \yii\web\Cookie([
            'name' => self::THEME_COOKIE_NAME,
            'value' => $name,
            'expire' => time() + (86400 * 30)
        ]));
    }

    /**
     * @param View $view
     * @throws InvalidConfigException
     */
    private function setAssets(View $view): void
    {
        $currentTheme = $this->getCurrent();
        $themeAssets = $currentTheme->getAssets();
        $this->bundle = Asset::register($view);
        if (!empty($themeAssets['css'])) {
            $this->bundle->css = $themeAssets['css'];
        }
        if (!empty($themeAssets['js'])) {
            $this->bundle->js = $themeAssets['js'];
        }
    }

    /**
     * @param string $themeName
     * @param array $themeData
     * @return Theme
     * @throws InvalidConfigException
     */
    private function buildTheme(string $themeName, array $themeData): Theme
    {
        $theme = new Theme();
        $theme->setDirectory($themeName);
        $theme->setName($themeData['name']);
        $theme->setFullPath(Yii::getAlias('@themes') . DIRECTORY_SEPARATOR . $themeName);
        $theme->setPreview($this->formatPreview($theme));
        $theme->setName($themeData['name'] ?? null);
        $theme->setDescription($themeData['description'] ?? null);
        $theme->setVersion($themeData['version'] ?? null);
        $theme->setLink($themeData['link'] ?? null);
        if($themeData['name'] === $this->getName()) {
            $theme->setIsCurrent();
        }
        if (!empty($themeData['themeConfig']['assets'])) {
            $theme->setAssets($themeData['themeConfig']['assets']);
        }
        if(!empty($themeData['authors'])) {
            if(!is_array($themeData['authors'])) {
                throw new InvalidConfigException("Authors must be an array");
            }
            foreach($themeData['authors'] as $authorData) {
                $this->buildAuthor($theme, $authorData);
            }
        }
        return $theme;
    }

    /**
     * @param Theme $theme
     * @param array $authorData
     * @return void
     */
    private function buildAuthor(Theme $theme, array $authorData)
    {
        $author = new Author();
        $author->setNickName($authorData['nickName']);
        $author->setFirstName($authorData['firstName'] ?? null);
        $author->setLastName($authorData['lastName'] ?? null);
        $author->setLastName($authorData['email'] ?? null);
        $author->setContacts($authorData['contacts'] ?? null);
        $author->setRole($authorData['role'] ?? null);
        $theme->addAuthor($author);
    }

    /**
     * Format preview string to url
     * @param Theme $theme
     * @return null|string
     * @throws InvalidConfigException
     */
    private function formatPreview(Theme $theme): ?string
    {
        $files = FileHelper::findFiles($theme->getFullPath(), [
            'filter' => function($path) {
                $ext = pathinfo($path, PATHINFO_EXTENSION);
                if($ext) {
                    return in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif']);
                }
                return null;
            }
        ]);
        if(!empty($files[0])) {
            return Yii::$app->getAssetManager()->publish($files[0])[1];
        }
        return null;
    }
}
