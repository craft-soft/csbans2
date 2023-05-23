<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\components\systemInfo;

use app\models\Ban;
use app\models\Comment;
use app\models\File;
use Yii;
use yii\httpclient\Client;
use yii\i18n\Formatter;

class SystemInfo
{
    private array $composerConfig;
    private Formatter $formatter;

    public function __construct(Formatter $formatter)
    {
        $this->formatter = $formatter;
        $file = \Yii::getAlias('@root/composer.json');
        $json = file_get_contents($file);
        $config = json_decode($json, true);
        if (json_last_error() ===JSON_ERROR_NONE) {
            $this->composerConfig = $config;
        }
    }

    public function siteVersion()
    {
        return $this->composerConfig['version'];
    }

    public function getVersion(): array
    {
        $current = $this->siteVersion();
        $latest = $this->getLatestVersion();
        return [
            'current' => $current,
            'latest' => $latest,
            'needUpdate' => $latest && version_compare($current, $latest, '<')
        ];
    }

    public function appPhpVersion()
    {
        return preg_replace('/[^\d\.]/', '', $this->composerConfig['require']['php']);
    }

    public function getLatestVersion(): ?string
    {
        try {
            $path = parse_url($this->composerConfig['homepage'], PHP_URL_PATH);
            if (!$path) {
                return null;
            }
            [$organization, $repo] = explode('/', ltrim($path, '/'));
            $client = new Client();
            $request = $client->createRequest();
            $request->setMethod('GET')
                ->setFullUrl("https://api.github.com/repos/$organization/$repo/tags")
                ->addHeaders([
                    'Accept' => 'application/vnd.github.v3+json',
                    'user-agent' => 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/111.0'
                ]);
            $response = $request->send();
            $content = $response->getContent();
        } catch (\Throwable $e) {
            return null;
        }
        $json = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }
        if (isset($json[0]['name']) && preg_match('([\d.]+)', $json[0]['name'])) {
            return ltrim($json[0]['name'], 'v');
        }
        return null;
    }

    public function systemVariables(): Items
    {
        $mysqlVersion = Yii::$app->getDb()->getServerVersion();
        $displayErrors = (bool)ini_get('display_errors');
        $appPhpVersion = $this->appPhpVersion();
        $variables = new Items();
        $variables->addItem(new Item(
            Yii::t('app', 'SYSTEM_PHP_VERSION'),
            PHP_VERSION,
            version_compare(PHP_VERSION, $appPhpVersion, '<'),
            Yii::t('app', 'SYSTEM_VERSION_NOT_SUPPORTED', ['version' => $appPhpVersion])
        ));
        $variables->addItem(new Item(
            Yii::t('app', 'SYSTEM_MYSQL_SERVER'),
            $mysqlVersion,
            version_compare($mysqlVersion, '5.7', '<'),
            Yii::t('app', 'SYSTEM_VERSION_NOT_SUPPORTED', ['version' => '5.7'])
        ));
        $variables->addItem(
            new Item(
                Yii::t('app', 'SYSTEM_DB_SIZE'),
                $this->formatter->asShortSize($this->getDbSize())
            )
        );
        $variables->addItem(new Item( Yii::t('app', 'SYSTEM_WEB_SERVER'), $_SERVER['SERVER_SOFTWARE'] ?? null));
        $variables->addItem(new Item(
            'display_errors',
            Yii::t('app', $displayErrors ? 'VALUE_ENABLED' : 'VALUE_DISABLED'),
            $displayErrors
        ));
        $variables->addItem(new Item('memory_limit', $this->formatter->asShortSize($this->phpSizeToBytes(ini_get('memory_limit')))));
        $variables->addItem(new Item('post_max_size', $this->formatter->asShortSize($this->phpSizeToBytes(ini_get('post_max_size')))));
        $variables->addItem(new Item('upload_max_filesize', $this->formatter->asShortSize($this->phpSizeToBytes(ini_get('upload_max_filesize')))));
        return $variables;
    }

    public function getDbSize()
    {
        $db = Yii::$app->getDb();
        $dbName = $db->createCommand('SELECT DATABASE()')->queryScalar();
        $query = $db->createCommand("SHOW TABLE STATUS FROM [[$dbName]]")->queryAll();
        $dbSize = 0;
        foreach($query as $row) {
            $dbSize += $row["Data_length"] + $row["Index_length"];
        }
        return $dbSize;
    }

    public function systemModules(): Items
    {
        $items = new Items();
        foreach ($this->composerConfig['require'] as $module => $version) {
            if (str_starts_with($module, 'ext-')) {
                $moduleName = str_replace('ext-', '', $module);
                $value = extension_loaded($moduleName);
                $items->addItem(new Item($moduleName, $this->formatter->asBoolean($value), !$value));
            }
        }
        return $items;
    }

    /**
     * Convert sizes from php.ini to bytes
     *  @noinspection PhpMissingBreakStatementInspection
     */
    private function phpSizeToBytes(string $val)
    {
        $val = trim($val);
        $last = strtolower($val[strlen($val)-1]);
        if (is_numeric($last)) {
            return $val;
        }
        $val = substr($val, 0, -1);
        switch($last) {
            case 'g':
                $val *= 1024;
            case 'm':
                $val *= 1024;
            case 'k':
                $val *= 1024;
        }
        return $val;
    }
}
