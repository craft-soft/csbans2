<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

declare(strict_types=1);

namespace app\modules\install\services;

use app\models\Webadmin;
use app\modules\install\models\Install;
use app\modules\install\services\exceptions\{InvalidStepException, MigrationsException};
use app\modules\install\services\exceptions\AddAdminException;
use app\modules\install\services\exceptions\CreateConfigException;
use app\modules\install\services\exceptions\DbConnectException;
use app\modules\install\services\exceptions\GeneratePermissionsException;
use app\modules\install\services\exceptions\PermissionsException;
use app\rbac\{Permissions, RbacService};
use yii\console\{Application, ExitCode, Request};
use yii\base\ErrorException;
use yii\db\Connection;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;

class InstallService
{
    private const DB_CONFIG_PATH = '@app/config/db.php';

    public const STEP_CONFIG = 'config';
    public const STEP_MIGRATIONS = 'migrations';
    public const STEP_PERMISSIONS = 'permissions';
    public const STEP_ADMIN = 'admin';
    public const STEP_DOWNLOAD_IP_GEO_DATA = 'ipgeo_data';

    public const STEPS = [
        self::STEP_CONFIG => [
            'method' => 'writeConfig',
            'checkMethod' => 'checkInstall'
        ],
        self::STEP_MIGRATIONS => [
            'method' => 'runMigrations',
            'checkMethod' => 'checkConfig'
        ],
        self::STEP_PERMISSIONS => [
            'method' => 'generatePermissions',
            'checkMethod' => 'checkMigrations'
        ],
        self::STEP_ADMIN => [
            'method' => 'addAdmin',
            'checkMethod' => 'checkHasPermissions'
        ],
        self::STEP_DOWNLOAD_IP_GEO_DATA => [
            'method' => 'downloadIpGeoData',
        ],
    ];

    private Install $model;

    private bool $done = false;

    /**
     * @param Install $model
     */
    public function __construct(Install $model)
    {
        $this->model = $model;
    }

    public function runStep(string $step): ?string
    {
        if (!in_array($step, array_keys(self::STEPS))) {
            throw new InvalidStepException();
        }
        $stepData = self::STEPS[$step] ?? null;
        if (!$stepData) {
            throw new \ErrorException("Invalid step: $step");
        }
        try {
            // Checks previous step has been executed
            if (!empty($stepData['checkMethod'])) {
                $this->{$stepData['checkMethod']}();
            }
            // Execute current step
            return $this->{$stepData['method']}();
        } catch(\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function checkHasPermissions(): void
    {
        if (!$this->checkMigrations()->getTableSchema('{{%auth_item}}', true)) {
            throw new ErrorException('Previous step not executed');
        }
    }

    private function checkConfig()
    {
        if (!file_exists(\Yii::getAlias(self::DB_CONFIG_PATH))) {
            throw new ErrorException('Previous step not executed');
        }
        $this->checkDbConnect();
    }

    private function checkMigrations(): Connection
    {
        $this->checkConfig();
        $connection = $this->checkDbConnect();
        if (!$connection->getIsActive() && count($connection->getSchema()->getTableNames()) < 10) {
            throw new ErrorException('Previous step not executed');
        }
        return $connection;
    }

    public function checkInstall(): void
    {
        \Yii::$app->getCache()->flush();
        $this->checkDbConnect();
        $this->checkPermissions();
        $this->checkCreateConfig();
    }

    private function checkDbConnect(): Connection
    {
        try {
            $connection = new Connection();
            $connection->dsn = "mysql:host={$this->model->dbHost};port={$this->model->dbPort};dbname={$this->model->dbName}";
            $connection->username = $this->model->dbUser;
            $connection->password = $this->model->dbPassword;
            $connection->tablePrefix = $this->model->dbPrefix;
            $connection->enableSchemaCache = false;
            $connection->open();
            if (!$connection->isActive) {
                throw new DbConnectException();
            }
            return $connection;
        } catch (\Throwable $e) {
            throw new DbConnectException($e->getMessage());
        }
    }

    private function checkCreateConfig()
    {
        $file = \Yii::getAlias('@app/config/test');
        try {
            touch($file);
            unlink($file);
        } catch (\Throwable $e) {
            throw new CreateConfigException();
        }
    }

    private function checkPermissions()
    {
        $directories = [
            '@app/runtime',
            '@app/config',
            '@app/data/files',
            '@app/data/webadminsAvatars',
            '@webroot/assets'
        ];
        $notWritableDirectories = [];
        foreach ($directories as $directory) {
            $path = \Yii::getAlias($directory);
            if (!is_writeable($path)) {
                try {
                    chmod($path, 0755);
                    if (!is_writeable($path)) {
                        $notWritableDirectories[] = $path;
                    }
                } catch (\Throwable $e) {
                    $notWritableDirectories[] = $path;
                }
            }
        }
        if (count($notWritableDirectories)) {
            throw new PermissionsException($notWritableDirectories);
        }
    }

    private function rollback()
    {
        $this->downMigrations();
        $configPath = \Yii::getAlias(self::DB_CONFIG_PATH);
        if (is_file($configPath)) {
            unlink($configPath);
        }
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function writeConfig(): string
    {
        $config = [
            'dbHost' => $this->model->dbHost,
            'dbPort' => $this->model->dbPort,
            'dbUser' => $this->model->dbUser,
            'dbName' => $this->model->dbName,
            'dbPrefix' => $this->model->dbPrefix,
            'dbPassword' => $this->model->dbPassword ?: '',
        ];
        $configPath = \Yii::getAlias(self::DB_CONFIG_PATH);
        $string = '<?php' . PHP_EOL . PHP_EOL
            . 'return ' . VarDumper::export($config)
            . ';' . PHP_EOL;
        file_put_contents($configPath, $string);
        return self::STEP_MIGRATIONS;
    }

    /**
     * The console application is launched. It sends the results to the console.
     * To suppress this behavior, we intercept the entire output in the buffer.
     * So when creating a console application, the $app variable is overwritten by the console application.
     * We save the web application to a variable and return it back after executing the console
     * @param string $route
     * @param array $params
     * @return bool
     */
    private function runConsole(string $route, array $params = []): bool
    {
        $oldApp = \Yii::$app;
        ob_start();
        $consoleConfig = \yii\helpers\ArrayHelper::merge(
            require \Yii::getAlias('@app/config/main.php'),
            require \Yii::getAlias('@app/config/console.php'),
        );
        try {
            $console = new Application($consoleConfig);
            $request = new Request();
            $request->setParams(array_merge([$route, '--interactive=0', '--color=0'], $params));
            $response = $console->handleRequest($request);
            return $response->exitStatus === ExitCode::OK;
        } finally {
            ob_end_clean();
            \Yii::$app = $oldApp;
        }
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function runMigrations(): string
    {
        if (!$this->runConsole('migrate')) {
            throw new MigrationsException();
        }
        return self::STEP_PERMISSIONS;
    }

    private function downMigrations(): void
    {
        try {
            $this->checkDbConnect();
            $this->runConsole('migrate/down', ['all']);
        } catch (\Throwable $e) {}
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function generatePermissions(): string
    {
        try {
            $service = \Yii::$container->get(RbacService::class);
            $service->getAuthManager()->cache = null;
            $service->addBaseRolesPermissions();
            return self::STEP_ADMIN;
        } catch (\Throwable $e) {
            throw new GeneratePermissionsException();
        }
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function addAdmin(): string
    {
        $model = new Webadmin();
        $model->username = $this->model->adminName;
        $model->email = $this->model->adminEmail;
        $model->hashPassword($this->model->adminPassword);
        if (!$model->save()) {
            throw new AddAdminException();
        }
        try {
            $am = \Yii::$app->getAuthManager();
            $role = $am->getRole(Permissions::ROLE_ADMIN);
            if ($role) {
                $assign = $am->assign($role, $model->id);
                if ($assign->userId !== $model->id) {
                    throw new \ErrorException();
                }
            }
            return self::STEP_DOWNLOAD_IP_GEO_DATA;
        } catch (\Throwable $e) {
            throw new AddAdminException();
        }
    }

    private function downloadIpGeoData()
    {
        $dataDir = \Yii::getAlias('@app/components/ipGeo/providers/data');
        $content = file_get_contents('https://craft-soft.ru/upload/csbans2/data/ipgeoData.zip');
        $tmpFile = \Yii::getAlias('@runtime/ipgeoData.zip');
        if ($content) {
            file_put_contents($tmpFile, $content);
            if (is_file($tmpFile)) {
                $zip = new \ZipArchive();
                if ($zip->open($tmpFile)) {
                    $zip->extractTo($dataDir);
                    $zip->close();
                    FileHelper::unlink($tmpFile);
                }
            }
        }
        $this->done = true;
    }

    /**
     * @return bool
     */
    public function isDone(): bool
    {
        return $this->done;
    }
}