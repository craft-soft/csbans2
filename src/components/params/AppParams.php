<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\params;

use app\models\Comment;
use app\models\File;
use app\models\Link;
use app\rbac\Permissions;
use yii\base\{Application, BootstrapInterface};
use yii\helpers\Html;
use yii\helpers\Url;

/**
 * Класс компонента конфигураций.
 *
 * @property string $site_name
 * @property string|null $site_baseurl
 * @property string|null $site_language
 * @property int $hide_old_bans
 * @property int $site_ban_period
 * @property int $max_offences
 * @property int $comments
 * @property int $bans_per_page
 * @property int $demo_upload_enabled
 * @property bool $bans_view_comments_count
 * @property bool $bans_view_files_count
 * @property bool $bans_view_kicks_count
 * @property string $demo_file_types
 * @property string $site_ban_reason
 * @property string $max_offences_reason
 * @property string $site_theme
 * @property string $start_page
 * @property int $ip_data_provider
 * @property int $server_query_provider
 * @property int $server_crypt_rcon
 * @property string $view_footer_left
 * @property string $view_footer_right
 * @property string $view_main_banner
 * @property bool $moderate_comments
 * @property bool $moderate_files
 * @property string $view_index_page_html
 * @property bool $ip_view_provider_cred
 */
class AppParams implements BootstrapInterface
{
    public const KEY_MAIN_SITE_NAME = 'site_name';
    public const KEY_MAIN_SITE_BASE_URL = 'site_baseurl';
    public const KEY_MAIN_SITE_LANGUAGE = 'site_language';

    public const KEY_VIEW_SITE_THEME = 'site_theme';
    public const KEY_VIEW_START_PAGE = 'start_page';
    public const KEY_VIEW_FOOTER_LEFT = 'view_footer_left';
    public const KEY_VIEW_FOOTER_RIGHT = 'view_footer_right';
    public const KEY_VIEW_MAIN_BANNER = 'view_main_banner';
    public const KEY_VIEW_VIEW_INDEX_PAGE_HTML = 'view_index_page_html';

    public const KEY_FILES_DEMO_UPLOAD_ENABLED = 'demo_upload_enabled';
    public const KEY_FILES_DEMO_FILE_TYPES = 'demo_file_types';
    public const KEY_BANS_PER_PAGE = 'bans_per_page';
    public const KEY_BANS_VIEW_COMMENTS_COUNT = 'bans_view_comments_count';
    public const KEY_BANS_VIEW_FILES_COUNT = 'bans_view_files_count';
    public const KEY_BANS_VIEW_KICKS_COUNT = 'bans_view_kicks_count';
    public const KEY_BANS_HIDE_OLD_BANS = 'hide_old_bans';
    public const KEY_BANS_SITE_BAN_PERIOD = 'site_ban_period';
    public const KEY_BANS_SITE_BAN_REASON = 'site_ban_reason';
    public const KEY_BANS_MAX_OFFENCES = 'max_offences';
    public const KEY_BANS_MAX_OFFENCES_REASON = 'max_offences_reason';
    public const KEY_BANS_COMMENTS = 'comments';
    public const KEY_BANS_MODERATE_COMMENTS = 'moderate_comments';
    public const KEY_BANS_MODERATE_FILES = 'moderate_files';
    public const KEY_IP_DATA_PROVIDER = 'ip_data_provider';
    public const KEY_IP_VIEW_PROVIDER_CRED = 'ip_view_provider_cred';
    public const KEY_SERVER_QUERY_PROVIDER = 'server_query_provider';
    public const KEY_SERVERS_DATA_INTERVAL = 'server_data_interval';
    public const KEY_EXTERNAL_YANDEX_MAPS_ENABLED = 'external_ya_maps_enabled';
    public const KEY_EXTERNAL_YANDEX_API_KEY = 'external_ya_api_key';

    public const BLOCK_MAIN = '';
    public const BLOCK_VIEW = 'view';
    public const BLOCK_FILES = 'files';
    public const BLOCK_BANS = 'bans';
    public const BLOCK_SERVER = 'server';
    public const BLOCK_EXTERNAL = 'external';

    public const VALUE_NONE = 0;
    public const VALUE_ALL = 1;
    public const VALUE_USERS = 2;

    public const VALUES = [
        self::VALUE_NONE => 'VALUE_NONE',
        self::VALUE_ALL => 'VALUE_ALL',
        self::VALUE_USERS => 'VALUE_USERS',
    ];

    public const BLOCKS = [
        self::BLOCK_MAIN => 'BLOCK_MAIN',
        self::BLOCK_VIEW => 'BLOCK_VIEW',
        self::BLOCK_FILES => 'BLOCK_FILES',
        self::BLOCK_BANS => 'BLOCK_BANS',
        self::BLOCK_SERVER => 'BLOCK_SERVER',
        self::BLOCK_EXTERNAL => 'BLOCK_EXTERNAL',
    ];

    /**
     * @var ParamsModelInterface|string
     */
    public string $modelClass;
    private array $params = [];

    private array $adminLinks = [];

    private ?Application $app = null;

    /**
     * @var ParamsModelInterface[]
     */
    private array $models = [];

    public function __get($name)
    {
        return $this->params[$name] ?? null;
    }

    public function __set($name, $value)
    {
        $this->models[$name]->setValue($value);
        $this->params[$name] = $value;
    }

    public function __isset($name): bool
    {
        return array_key_exists($name, $this->params);
    }

    /**
     * @inheritdoc
     */
    public function bootstrap($app): void
    {
        $this->app = $app;
        if ($this->isInstalled()) {
            $this->bootstrapConfig($app);
        }
    }

    private ?bool $isInstalled = null;
    public function isInstalled(): bool
    {
        if ($this->isInstalled === null) {
            try {
                if (!$this->app->getDb()) {
                    return false;
                }
                $this->app->getDb()->open();
                $this->isInstalled = $this->app->getDb()->getIsActive() && $this->app->getDb()->getTableSchema($this->modelClass::tableName(), true);
            } catch (\Throwable $exception) {
                $this->isInstalled = false;
            }
        }
        return $this->isInstalled;
    }

    /**
     * @param Application|\yii\web\Application $app
     * @return void
     */
    private function bootstrapConfig($app): void
    {
        $configs = $this->modelClass::getAll();
        foreach($configs as $config) {
            $key = $config->getKey();
            $value = $config->getValue();
            $this->models[$key] = $config;
            $this->params[$key] = $value;
            if ($key === self::KEY_MAIN_SITE_NAME) {
                $app->name = $value;
            }
        }
        $this->setLanguage($app);
    }

    /**
     * @param Application|\yii\web\Application $app
     * @return void
     */
    private function setLanguage($app)
    {
        if ($app instanceof \yii\web\Application) {
            $language = $app->getUser()->getLanguage();
            if (!$language) {
                $language = $this->site_language;
            }
            if ($language) {
                $app->language = $language;
            }
        }
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function forFrontend(): array
    {
        $params = [];
        foreach($this->models as $model) {
            if ($model->toFrontend()) {
                $params[$model->getKey()] = $model->getValue();
            }
        }
        return $params;
    }

    public function languages(): array
    {
        return [
            'ru' => 'Русский',
            'en' => 'English',
        ];
    }

    public function mainMenu(): array
    {
        if (!$this->isInstalled()) {
            return [];
        }
        $links = [];
        $query = Link::find()->orderBy(['sort' => SORT_ASC]);
        foreach ($query->all() as $link) {
            /** @var Link $link */
            $url = $link->url;
            $active = null;
            if (in_array($url, ['/bans', '/servers', '/admins'])) {
                $url = ["$url/index"];
                $active = \Yii::$app->controller->id === trim($link->url, '/');
            } else if ($url === '/') {
                if (\Yii::$app->appParams->start_page !== '/') {
                    continue;
                }
                $url = ['/default/index'];
            }
            $links[] = [
                'label' => $link->getTranslatedLabel(),
                'url' => $url,
                'active' => $active
            ];
        }
        return $links;
    }

    public function adminMenu(): array
    {
        $links = [
            [
                'label' => 'Gii',
                'url' => ['/gii/default/index'],
                'visible' => YII_ENV_DEV
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMINCENTER'),
                'url' => ['/admin/default/index'],
                'active' => \Yii::$app->requestedRoute === 'admin/default/index'
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_SYSTEM'),
                'url' => ['/admin/system/index'],
                'active' => \Yii::$app->controller->id === 'system',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBSETTINGS_VIEW)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_AMX_ADMINS'),
                'url' => ['/admin/amx-admins/index'],
                'active' => \Yii::$app->controller->id === 'amx-admins',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_AMXADMINS_VIEW)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_SERVERS'),
                'url' => ['/admin/servers/index'],
                'active' => \Yii::$app->controller->id === 'servers',
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_BANS'),
                'url' => ['/admin/bans/index'],
                'active' => \Yii::$app->controller->id === 'bans',
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_REASONS'),
                'url' => ['/admin/reasons/index'],
                'active' => \Yii::$app->controller->id === 'reasons',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_SERVERS_EDIT)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_WEB_ADMINS'),
                'url' => ['/admin/webadmins/index'],
                'active' => \Yii::$app->controller->id === 'webadmins',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBADMINS_VIEW)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_WEB_ADMIN_ROLES'),
                'url' => ['/admin/roles/index'],
                'active' => \Yii::$app->controller->id === 'roles',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBADMINS_EDIT)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_LINKS'),
                'url' => ['/admin/links/index'],
                'active' => \Yii::$app->controller->id === 'links',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBSETTINGS_EDIT)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_LOGS'),
                'url' => ['/admin/logs/index'],
                'active' => \Yii::$app->controller->id === 'logs',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBSETTINGS_VIEW)
            ],
            [
                'label' => \Yii::t('admin/mainMenu', 'ADMIN_PARAMS'),
                'url' => ['/admin/params/index'],
                'active' => \Yii::$app->controller->id === 'params',
                'visible' => \Yii::$app->getUser()->can(Permissions::PERMISSION_WEBSETTINGS_VIEW)
            ],
        ];
        if ($this->moderate_comments && \Yii::$app->getUser()->can(Permissions::PERMISSION_MODERATE_CONTENT)) {
            $newComments = Comment::find()->where(['moderated' => 0])->count();
            $label = \Yii::t('admin/mainMenu', 'MODERATE_COMMENTS');
            if ($newComments) {
                $label .= ' ' . Html::tag('span', $newComments, ['class' => 'nav-badge badge text-bg-secondary opacity-75']);
            }
            $links[] = [
                'label' => $label,
                'url' => ['/admin/comments/index'],
                'active' => \Yii::$app->controller->id === 'comments',
            ];
        }
        if ($this->moderate_files && \Yii::$app->getUser()->can(Permissions::PERMISSION_MODERATE_CONTENT)) {
            $newFiles = File::find()->where(['moderated' => 0])->count();
            $label = \Yii::t('admin/mainMenu', 'MODERATE_FILES');
            if ($newFiles) {
                $label .= ' ' . Html::tag('span', $newFiles, ['class' => 'nav-badge badge text-bg-secondary opacity-75']);
            }
            $links[] = [
                'label' => $label,
                'url' => ['/admin/files/index'],
                'active' => \Yii::$app->controller->id === 'files',
            ];
        }
        if ($this->adminLinks) {
            foreach ($this->adminLinks as $link) {
                $links[] = '<li class="nav-header">'.$link['label'].'</li>';
                foreach ($link['links'] as $l) {
                    $links[] = $l;
                }
            }
        }
        return $links;
    }

    public function addAdminMenuLinks(array $links): void
    {
        foreach ($links as $link) {
            $this->adminLinks[] = $link;
        }
    }
}
