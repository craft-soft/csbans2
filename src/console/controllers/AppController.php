<?php
/*
 * Copyright (c) 2017,2022-2023 Alex Urich <urichalex@mail.ru>
 * License: GNU LGPL 2 only, see file LICENSE
 */

namespace app\console\controllers;

use app\components\params\AppParams;
use app\models\Webadmin;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;
use yii\helpers\VarDumper;
use yii\rbac\ManagerInterface;
use yii\console\{Controller, ExitCode};
use app\rbac\{Permissions, RbacService};

class AppController extends Controller
{
    public function actionParams()
    {
        $reflection = new \ReflectionClass(Permissions::class);
        $params = [];
        foreach ($reflection->getConstants() as $constant => $value) {
            if (str_starts_with($constant, 'PERMISSION_')) {
                $params[$constant] = '';
            }
        }
        $a2s = VarDumper::export($params);
        $str = '<?php' . PHP_EOL . "return $a2s;\n";
        file_put_contents(\Yii::getAlias('@runtime/params.php'), $str);
    }

    /**
     * Добавление администратора
     * @param ManagerInterface $authManager
     * @return void
     * @throws \Exception
     */
    public function actionAddAdmin(ManagerInterface $authManager): void
    {
        $model = new Webadmin();
        $model->username = $this->prompt('Логин:', ['required' => true, 'validator' => function($value, &$error) {
            if (Webadmin::find()->where(['username' => $value])->exists()) {
                $error = 'Логин занят';
                return false;
            }
            return true;
        }]);
        $password = $this->prompt('Пароль:', ['required' => true]);
        $model->password = md5($password);
        $model->level = 1;
        if (!$model->save()) {
            print_r($model->getErrors());
        }
        $role = $authManager->getRole(Permissions::ROLE_ADMIN);
        if ($role) {
            $authManager->assign($role, $model->id);
        }
        $this->stdout("Done\n");
    }

    /**
     * Смена пароля администратора
     * @return int
     */
    public function actionAdminPassword(): int
    {
        /** @var Webadmin $admin */
        $admin = null;
        $this->prompt('ID или логин:', ['required' => true, 'validator' => function($value, &$error) use(&$admin) {
            if (!$admin = Webadmin::find()->where(['id' => $value])->orWhere(['username' => $value])->one()) {
                $error = 'Админ не найден';
                return false;
            }
            return true;
        }]);
        $newPassword = $this->prompt('Пароль:', ['required' => true]);
        $admin->password = md5($newPassword);
        if (!$admin->save(false, ['password'])) {
            print_r($admin->getErrors());
            return ExitCode::UNSPECIFIED_ERROR;
        }
        echo "Done\n";
        return ExitCode::OK;
    }

    public function actionGeneratePermissions(RbacService $rbacService)
    {
        $rbacService->addBaseRolesPermissions();
    }

    public function actionAssignAdmin(RbacService $rbacService)
    {
        /** @var Webadmin $admin */
        $admin = null;
        $this->prompt('ID или логин:', ['required' => true, 'validator' => function($value, &$error) use(&$admin) {
            if (!$admin = Webadmin::find()->where(['id' => $value])->orWhere(['username' => $value])->one()) {
                $error = 'Админ не найден';
                return false;
            }
            return true;
        }]);
        $role = $rbacService->getAuthManager()->getRole(Permissions::ROLE_ADMIN);
        $rbacService->assignRoleToAdmin($role, $admin);
    }
}
