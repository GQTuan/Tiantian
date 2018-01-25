<?php

namespace admin\controllers;

use Yii;
use common\helpers\Hui;
use common\helpers\Html;
use common\helpers\Inflector;
use common\helpers\FileHelper;
use common\helpers\ArrayHelper;
use admin\models\AdminMenu;
use admin\models\AdminUser;
use admin\models\Retail;
use admin\models\RetailWithdraw;
use admin\models\User;
use admin\models\Order;
use common\modules\rbac\models\AuthItem;
use common\helpers\StringHelper;

/**
 * @author ChisWill
 */
class AdminController extends \admin\components\Controller
{
    /**
     * @authname 管理员列表
     */
    public function actionList()
    {
        $query = (new AdminUser)->search()
            ->andWhere(['state' => AdminUser::STATE_VALID])
            ->andWhere(['<=', 'power', u()->power]);
        $html = $query->getTable([
            'id' => ['search' => true],
            'username' => ['search' => true],
            'realname' => ['search' => true, 'type' => 'text'],
            'roles' => ['header' => '角色', 'value' => function ($user) {
                $roles = [];
                foreach ($user->roles as $role) {
                    $roles[] = Html::likeSpan($role->item_name);
                }
                return implode('，', $roles);
            }],
            'state' => ['search' => 'select'],
            u()->power <= AdminUser::POWER_ADMIN ?:['type' => ['edit' => 'saveAdmin', 'delete' => 'ajaxDeleteAdmin']]
        ], [
            'addBtn' => ['saveAdmin' => '创建用户']
        ]);

        return $this->render('list', compact('html'));
    }

    /**
     * @authname 创建/修改管理员
     */
    public function actionSaveAdmin($id = null)
    {
        $user = AdminUser::findModel($id);
        $this->checkAccess($user);
        $user->tmpPassword = $user->password;
        // 避免在页面上显示密码
        $user->password = null;
        // 获取当前的所有角色
        $roles = AuthItem::getRoleQuery()->map('name', 'name');
        // 填充当前用户拥有的角色
        $authItem = new AuthItem;
        $authItem->roles = AdminUser::roles($id);

        if ($user->load()) {
            if ($user->saveAdmin()) {
                $user->power = AdminUser::POWER_ADMIN;
                $user->update(false);
                return self::success();
            } else {
                return self::error($user);
            }
        }

        return $this->render('saveAdmin', compact('user', 'authItem', 'roles'));
    }

    /**
     * @authname 删除管理员
     */
    public function actionAjaxDeleteAdmin()
    {
        $id = post('id');
        $this->checkAccess(AdminUser::findModel($id));

        if ($id != u('id')) {
            return parent::actionDelete();
        } else {
            return self::error('不能删除自己');
        }
    }

    /**
     * @authname 角色列表
     */
    public function actionRoleList()
    {
        $query = AuthItem::getRoleQuery();
        $roles = $query->all();
        $categoryMap = AdminMenu::categoryMap();
        $html = $query->getTable([
            'name' => ['header' => '角色名称（规则）', 'width' => '15%', 'value' => function ($role) {
                $html = $role->name;
                if ($role->rule_name) {
                    $html .= '<br>（' . Html::finishSpan($role->rule_name) . '）';
                }
                return $html;
            }],
            ['header' => '拥有的权限', 'value' => function ($role) use ($roles, $categoryMap) {
                $childRoles = $childPermissions = [];
                $html = '';
                foreach ($role->children as $child) {
                    if (array_key_exists($child['child'], $roles)) {
                        $childRoles[] = $child;
                    } else {
                        $childPermissions[] = $child;
                    }
                }
                if ($childRoles) {
                    $html .= Html::likeSpan('角色') . '：';
                    $d = '';
                    foreach ($childRoles as $childRole) {
                        $html .= $d . $childRole['child'];
                        $d = '，';
                    }
                }
                if ($childPermissions) {
                    if ($childRoles) {
                        $html .= '<br>';
                    }
                    ArrayHelper::multisort($childPermissions, 'child');
                    $permissionGroup = [];
                    foreach ($childPermissions as $childPermission) {
                        $controller = explode('-', Inflector::camel2id($childPermission->child))[1];
                        $permissionGroup[$controller][] = $childPermission;
                    }
                    foreach ($permissionGroup as $controller => $permissions) {
                        $html .= Html::successSpan(ArrayHelper::getValue($categoryMap, $controller, '常规')) . '：';
                        $d = '';
                        foreach ($permissions as $permission) {
                            $html .= $d . Html::span($permission->childItem['description'], ['data-key' => $permission->child]);
                            $d = '，';
                        }
                        $html .= '<br>';
                    }
                }
                return $html;
            }],
            ['type' => ['edit' => 'editRole', 'delete' => 'ajaxDeleteRole']]
        ], [
            'addBtn' => ['createRole' => '创建角色']
        ]);

        return $this->render('roleList', compact('html'));
    }

    /**
     * @authname 创建角色
     */
    public function actionCreateRole()
    {
        $title = '创建角色';
        // 获取权限对象
        $auth = Yii::$app->authManager;
        // 获取当前的所有角色
        $roles = AuthItem::getRoleQuery()->map('name', 'name');
        // 获取当前的所有权限
        $permissions = AuthItem::getGroupPermissionData();
        // 获取模型
        $model = new AuthItem(['scenario' => 'createRole']);

        if ($model->load()) {
            $model->description = FileHelper::getCurrentApp();
            if ($model->validate()) {
                $post = post('AuthItem', []);
                // 添加角色
                $role = $auth->createRole($model->name);
                $role->description = $model->description;
                $role->ruleName = $model->rule_name;
                $auth->add($role);
                // 获取角色和权限
                $roles = ArrayHelper::getValue($post, 'roles') ?: [];
                // 添加子角色
                foreach ($roles as $roleName) {
                    $childRole = $auth->getRole($roleName);
                    $auth->addChild($role, $childRole);
                }
                $permissions = ArrayHelper::getValue($post, 'permissions') ?: [];
                // 添加权限
                foreach ($permissions as $permissionName) {
                    $permission = $auth->getPermission($permissionName);
                    $auth->addChild($role, $permission);
                }
                return self::success();
            } else {
                return self::error($model);
            }
        }

        return $this->render('role', compact('title', 'roles', 'permissions', 'model'));
    }

    /**
     * @authname 编辑角色
     */
    public function actionEditRole($id)
    {
        $name = $id;
        $title = '编辑角色';
        // 获取权限对象
        $auth = Yii::$app->authManager;
        // 获取当前的所有角色
        $roles = AuthItem::getRoleQuery()->map('name', 'name');
        // 获取当前的所有权限
        $permissions = AuthItem::getGroupPermissionData();
        // 获取当前的角色
        $role = $auth->getRole($name);
        if (!$role) {
            throw new \yii\base\InvalidParamException('不存在该角色！');
        }
        // 获取模型
        $model = new AuthItem;
        $model->name = $model->oldRoleName = $role->name;
        $model->description = $role->description;
        $model->rule_name = $role->ruleName;
        $model->scenario = 'updateRole';
        $children = $auth->getChildren($role->name);
        foreach ($children as $child) {
            if ($child->type == AuthItem::TYPE_ROLE) {
                $model->roles[] = $child->name;
            } elseif ($child->type == AuthItem::TYPE_PERMISSION) {
                $model->permissions[] = $child->name;
            }
        }
        // 过滤掉不能添加为子集的角色
        AuthItem::filterLoopRoles($roles, $role);

        if ($model->load()) {
            if ($model->validate()) {
                $post = post('AuthItem', []);
                // 更改角色名
                $role->name = $model->name;
                $role->ruleName = $model->rule_name;
                $auth->update($name, $role);
                // 更改子角色以及权限
                $items = ['role', 'permission'];
                $methods = ['add', 'remove'];
                foreach ($items as $item) {
                    $postName = $item . 's';
                    $updateItems = ArrayHelper::getValue($post, $postName) ?: [];
                    list($add, $remove) = ArrayHelper::diff($model->$postName, $updateItems);
                    foreach ($methods as $method) {
                        foreach ($$method as $itemName) {
                            $getMethod = 'get' . ucfirst($item);
                            $authItem = $auth->$getMethod($itemName);
                            $updateMethod = $method . 'Child';
                            $auth->$updateMethod($role, $authItem);
                        }
                    }
                }
                return self::success();
            } else {
                return self::error($model);
            }
        }

        return $this->render('role', compact('title', 'roles', 'permissions', 'model'));
    }

    /**
     * @authname 查看角色权限
     */
    public function actionAjaxRoleInfo()
    {
        $roleList = get('roleList');
        $auth = Yii::$app->authManager;
        $roles = [];
        foreach ($roleList as $key => $role) {
            $roles = array_merge($roles, array_keys($auth->getPermissionsByRole($role)));
        }
        $roles = array_unique($roles);

        return self::success($roles);
    }

    /**
     * @authname 删除角色
     */
    public function actionAjaxDeleteRole()
    {
        $name = post('name');
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($name);

        if ($role && $auth->remove($role)) {
            return self::success('删除成功！');
        } else {
            return self::error('删除失败！');
        }
    }

    /**
     * @authname 权限列表
     */
    public function actionPermissionList()
    {
        $query = AuthItem::getAuthItemQuery();
        $categoryMap = AdminMenu::categoryMap();
        $html = $query->getTable([
            'name' => ['header' => '动作', 'value' => function ($item) {
                $name = Inflector::camel2id($item->name);
                $namePieces = explode('-', $name);
                unset($namePieces[0], $namePieces[1]);
                return lcfirst(Inflector::id2camel(implode('-', $namePieces)));
            }],
            'description' => ['type' => 'text', 'value' => function ($item) {
                return Hui::textInput(null, $item->description);
            }],
            'rule_name' => ['type' => 'text', 'value' => function ($item) {
                return Hui::textInput(null, $item->rule_name, ['placeholder' => '规则名或是规则类名']);
            }]
        ], [
            'beforeRow' => function ($item) use (&$controllerName, $categoryMap) {
                $name = Inflector::camel2id($item->name);
                $namePieces = explode('-', $name);
                if ($controllerName != $namePieces[1]) {
                    $controllerName = $namePieces[1];
                    return Html::tag('tr', Html::tag('th', ArrayHelper::getValue($categoryMap, $controllerName, '常规'), ['colspan' => 3, 'class' => 'text-c']));
                }
            },
            'isSort' => false,
            'paging' => false,
            'addBtn' => ['addPermission' => '添加权限'],
            'ajaxUpdateAction' => 'ajaxUpdatePermission'
        ]);

        return $this->render('permissionList', compact('html'));
    }

    /**
     * @authname 添加权限
     */
    public function actionAddPermission()
    {
        // 获取已经保存的权限信息
        $permissionMap = AuthItem::getPermissionQuery()->map('name', 'description');
        $models = [];
        // 获取文件中所有权限
        $actions = AuthItem::getFileActionList('admin', ['site']);
        foreach ($actions as $action => $description) {
            // 过滤已经保存的权限
            if (!array_key_exists($action, $permissionMap)) {
                $model = new AuthItem;
                $model->name = $action;
                $model->description = $description;
                $models[] = $model;
            }
        }

        if (req()->isPost) {
            if (AuthItem::loadMultiple($models, post()) && AuthItem::validateMultiple($models)) {
                $auth = Yii::$app->authManager;
                foreach ($models as $index => $model) {
                    $permission = $auth->createPermission($model->name);
                    $permission->description = $model->description;
                    $permission->ruleName = $model->rule_name;
                    $auth->add($permission);
                }
                return self::success();
            } else {
                $errors = [];
                foreach ($models as $key => $model) {
                    if ($model->hasErrors()) {
                        $index = $key + 1;
                        $errors[] = "第{$index}行，" . current($model->getFirstErrors());
                    }
                }
                return self::error($errors);
            }
        } else {
            $i = -1;
            $html = self::getTable($models, [
                ['header' => '序号', 'value' => function () use (&$i) {
                    return ++$i + 1;
                }],
                'name' => ['header' => '动作', 'value' => function ($model) use (&$i, &$action) {
                    $namePieces = explode('-', Inflector::camel2id($model->name));
                    unset($namePieces[0]);
                    return array_shift($namePieces) . '：' . ($action = lcfirst(Inflector::id2camel(implode('-', $namePieces)))) .
                           Html::hiddenInput("AuthItem[$i][name]", $model->name);
                }],
                'description' => ['header' => '描述', 'value' => function ($model) use (&$i, &$action) {
                    return Hui::textInput("AuthItem[$i][description]", $model->description ?: $action);
                }],
                'rule_name' => ['header' => '规则', 'value' => function ($model) use (&$i) {
                    return Hui::textInput("AuthItem[$i][rule_name]", $model->rule_name, ['placeholder' => '规则名或是规则类名']);
                }]
            ], [
                'ajaxLayout' => '{items}'
            ]);
        }

        return $this->render('addPermission', compact('html'));
    }

    /**
     * @authname 修改权限
     */
    public function actionAjaxUpdatePermission()
    {
        $auth = Yii::$app->authManager;
        $params = post('params');

        try {
            $authItem = AuthItem::findOne($params['key']);
            $authItem->$params['field'] = $params['value'];
            if ($authItem->validate()) {
                $permission = $auth->createPermission($params['key']);
                $permission->ruleName = $authItem->rule_name;
                $permission->description = $authItem->description;
                $auth->update($params['key'], $permission);
                return self::success();
            } else {
                return self::error($authItem);
            }
        } catch (\Exception $e) {
            throwex($e);
        }
    }

    private function checkAccess($user)
    {
        if ($user->power > u()->power) {
            throwex('你不能对其操作！');
        }
    }

    /**
     * @authname 创建/修改用户关联
     */
    public function actionCreateAdmin($id = null)
    {
        $user = AdminUser::findModel($id);
        $this->checkAccess($user);
        $user->tmpPassword = $user->password;
        // 避免在页面上显示密码
        $user->password = null;

        if ($user->load()) {
            $arr = AdminUser::find()->where(['state' => AdminUser::STATE_VALID, 'power' => $user->power + 1])->map('id', 'username');
            if (empty($arr)) {
                return error('此用户类型还没有上级，请先创建它上级用户！');
            }
            if (!preg_match('/^1[34578]\d{9}$/', $user->mobile)) {
                return error('您输入的不是一个手机号！');
            }
            $retail = new Retail();
            $retail->pass = $user->password;
            if ($user->power == AdminUser::POWER_RING) {
                $retail->code = StringHelper::random(8, 'n');
            } else {
                if ($user->power == AdminUser::POWER_SETTLE) {
                    $user->pid = 2;
                }
                $retail->code = StringHelper::random(10, 'a');
            }
            $power = $user->power;
            if (empty($user->pid)) {
                $user->pid = u()->id;
            }
            // test($user->attributes, $retail->attributes);
            if ($user->saveAdmin()) {
                $retail->account = $user->username;
                $retail->admin_id = $user->id;
                $retail->tel = $user->mobile;
                $retail->realname = $retail->company_name = $user->realname;
                $retail->save();
                $user->power = $power;
                $user->update();

                $auth = Yii::$app->authManager;
                $name = (new AdminUser)->getPowerValue($user->power);
                $role = $auth->getRole($name);
                $auth->assign($role, $user->id);

                return self::success();
            } else {
                return self::error($user);
            }
        }

        return $this->render('createAdmin', compact('user'));
    }

    /**
     * @authname ajax获取此类型上级用户
     */
    public function actionAjaxSubUser()
    {
        $power = post('power', AdminUser::POWER_SETTLE) + 1;
        if ($power == AdminUser::POWER_ADMIN || u()->power == $power) {
            return success('');
        }
        $arr = AdminUser::find()->where(['state' => AdminUser::STATE_VALID, 'power' => $power])->map('id', 'username');
        if (empty($arr)) {
            return error('此用户类型还没有上级，请先创建它上级用户！');
        }
        $name = (new AdminUser)->getPowerValue($power)?:u()->username;
        return success($this->renderPartial('_form', compact('arr', 'name')));
    }

    /**
     * @authname 结算会员
     */
    public function actionSettle()
    {
        $query = (new AdminUser)->search()->with(['retail'])
            ->andWhere(['state' => AdminUser::STATE_VALID, 'power' => AdminUser::POWER_SETTLE])->adminPower();
        $html = $query->getTable([
            'id' => ['search' => true],
            'username' => ['search' => true],
            'realname' => ['search' => true, 'type' => 'text'],
            'mobile' => ['search' => true, 'type' => 'text'],
            'retail.point',
            'retail.deposit',
            'retail.total_fee',
            'created_at',
            // u()->power <= AdminUser::POWER_ADMIN ?:['type' => ['edit' => 'saveAdmin', 'delete' => 'ajaxDeleteAdmin']]
            ['header' => '操作', 'width' => '80px', 'value' => function ($row) {
                $string = Hui::primaryBtn('修改返点', ['editPoint', 'id' => $row->id], ['class' => 'editBtn']);
                 if (u()->power >= AdminUser::POWER_ADMIN) {
                     $string .= Hui::primaryBtn('保证金充值', ['depositWithdraw', 'id' => $row->id], ['class' => 'depositWithdraw']);
                 }
                return $string . Hui::primaryBtn('修改密码', ['editAdminPass', 'id' => $row->id], ['class' => 'editBtnPassword']);
            }]
        ], [
            u()->power <= AdminUser::POWER_SETTLE ?:'addBtn' => ['createAdmin' => '创建用户']
        ]);

        return $this->render('settle', compact('html'));
    }

    /**
     * @authname 运营中心
     */
    public function actionOperate()
    {
        $query = (new AdminUser)->search()->joinWith(['retail', 'parent'])
            ->andWhere(['adminUser.state' => AdminUser::STATE_VALID, 'adminUser.power' => AdminUser::POWER_OPERATE])->adminPower();
        $html = $query->getTable([
            'id',
            'username' => ['header' => '运营中心'],
            'realname' => ['search' => true, 'type' => 'text'],
            'mobile' => ['search' => true, 'type' => 'text'],
            'parent.username' => ['header' => '上级结算中心'],
            'retail.point',
            'retail.deposit',
            'retail.total_fee',
            'created_at',
            ['header' => '操作', 'width' => '80px', 'value' => function ($row) {
                $string = Hui::primaryBtn('修改返点', ['editPoint', 'id' => $row->id], ['class' => 'editBtn']);
                 if (u()->power >= AdminUser::POWER_ADMIN) {
                     $string .= Hui::primaryBtn('保证金充值', ['depositWithdraw', 'id' => $row->id], ['class' => 'depositWithdraw']);
                 }
                return $string . Hui::primaryBtn('修改密码', ['editAdminPass', 'id' => $row->id], ['class' => 'editBtnPassword']);
            }]
        ], [
            'searchColumns' => [
                'id',
                'username' => ['header' => '运营中心'],
                'parent.username' => ['header' => '上级结算中心'],
            ],
            u()->power <= AdminUser::POWER_OPERATE ?:'addBtn' => ['createAdmin' => '创建用户']
        ]);

        return $this->render('operate', compact('html'));
    }

    /**
     * @authname 微会员
     */
    public function actionMember()
    {
        $query = (new AdminUser)->search()->joinWith(['retail', 'parent'])
            ->andWhere(['adminUser.state' => AdminUser::STATE_VALID, 'adminUser.power' => AdminUser::POWER_MEMBER])->adminPower();
        $html = $query->getTable([
            'id',
            'username' => ['header' => '微会员'],
            'realname' => ['search' => true, 'type' => 'text'],
            'mobile' => ['search' => true, 'type' => 'text'],
            'parent.username' => ['header' => '上级运营中心'],
            'retail.point',
            'retail.deposit',
            'retail.total_fee',
            'created_at',
            ['header' => '操作', 'width' => '80px', 'value' => function ($row) {
                $string = Hui::primaryBtn('修改返点', ['editPoint', 'id' => $row->id], ['class' => 'editBtn']);
                 if (u()->power >= AdminUser::POWER_ADMIN) {
                     $string .= Hui::primaryBtn('保证金充值', ['depositWithdraw', 'id' => $row->id], ['class' => 'depositWithdraw']);
                 }
                return $string . Hui::primaryBtn('修改密码', ['editAdminPass', 'id' => $row->id], ['class' => 'editBtnPassword']);
            }]
        ], [
            'searchColumns' => [
                'id',
                'username' => ['header' => '微会员'],
                'parent.username' => ['header' => '上级运营中心'],
            ],
            u()->power <= AdminUser::POWER_MEMBER ?:'addBtn' => ['createAdmin' => '创建用户']
        ]);

        return $this->render('member', compact('html'));
    }

    /**
     * @authname 微圈
     */
    public function actionRing()
    {
        $query = (new AdminUser)->search()->joinWith(['retail', 'parent'])
            ->andWhere(['adminUser.state' => AdminUser::STATE_VALID, 'adminUser.power' => AdminUser::POWER_RING])->adminPower();
        $html = $query->getTable([
            'id',
            'username' => ['header' => '微圈'],
            'realname' => ['search' => true, 'type' => 'text'],
            'mobile' => ['search' => true, 'type' => 'text'],
            'parent.username' => ['header' => '上级微会员'],
            // ['header' => '头寸统计', 'value' => function ($row) {
            //     return Order::ringUserProfit($row->id);
            // }],
            'retail.point',
            'retail.total_fee',
            'retail.code',
            'created_at',
            ['header' => '操作', 'width' => '80px', 'value' => function ($row) {
                $string = Hui::primaryBtn('修改返点', ['editPoint', 'id' => $row->id], ['class' => 'editBtn']);
                if (u()->power >= AdminUser::POWER_ADMIN) {
                    $string .= Hui::primaryBtn('手续费操作', ['feeWithdraw', 'id' => $row->id], ['class' => 'feeWithdraw']);
                }
                return $string . Hui::primaryBtn('修改密码', ['editAdminPass', 'id' => $row->id], ['class' => 'editBtnPassword']);
            }]
        ], [
            'searchColumns' => [
                'id',
                'username' => ['header' => '微圈'],
                'parent.username' => ['header' => '上级微会员'],
            ],
            u()->power <= AdminUser::POWER_RING ?:'addBtn' => ['createAdmin' => '创建用户']
        ]);

        return $this->render('ring', compact('html'));
    }

    /**
     * @authname 修改组织框架各级密码
     */
    public function actionEditAdminPass() 
    {
        $adminUser = AdminUser::findModel(get('id'));
        $adminUser->password = post('password');
        if ($adminUser->validate()) {
            $adminUser->hashPassword()->update(false);
            return success();
        } else {
            return error($adminUser);
        }
    }

    /**
     * @authname 组织架构成员的返点修改
     */
    public function actionEditPoint() 
    {
        $adminUser = AdminUser::find()->with(['retail'])->where(['id' => get('id')])->one();
        $adminUser->retail->point = post('point');
        if ($adminUser->retail->point < 0 || $adminUser->retail->point > 100 || is_int($adminUser->retail->point)) {
            return error('返点数异常(设置返点为正整数)！');
        }

        $upAdminUser = AdminUser::find()->with(['retail'])->where(['id' => $adminUser->pid])->one();
        if ($adminUser->retail->point > $upAdminUser->retail->point) {
            return error('返点数不能大于您的上级(最大值' . $upAdminUser->retail->point . ')！');
        }

        if ($adminUser->power == AdminUser::POWER_RING) {
            // if (u()->power < AdminUser::POWER_ADMIN) {
            //     return error('您无权设置微圈的返点！');
            // }
            $user = User::find()->joinWith(['userExtend'])->where(['admin_id' => $adminUser->id, 'is_manager' => User::IS_MANAGER_YES])->orderBy('userExtend.point DESC')->one();
            if (!empty($user)) {
                if ($adminUser->retail->point < $user->userExtend->point) {
                    return error('返点数不能小于您的经纪人最大设置数(最小值' . $user->userExtend->point . ')！');
                }
            }
        } else {
            $downAdminUser = AdminUser::find()->joinWith(['retail'])->where(['pid' => $adminUser->id])->orderBy('retail.point DESC')->one();
            if (!empty($downAdminUser)) {
                if ($adminUser->retail->point < $downAdminUser->retail->point) {
                    return error('返点数不能小于您的下级最大设置数(最小值' . $downAdminUser->retail->point . ')！');
                }
            }
        }

        if ($adminUser->retail->validate()) {
            $adminUser->retail->update(false);
            return success();
        } else {
            return error($adminUser->retail);
        }
    }

    /**
     * @authname 手续费操作
     */
    public function actionFeeWithdraw() 
    {
        $adminUser = AdminUser::find()->with(['retail'])->where(['id' => get('id')])->one();
        $fee = post('fee');
        if (!is_numeric($fee)) {
                return error('非法参数！');
        }
        // if ($adminUser->retail->total_fee < $fee) {
        //     return error('提现金额不能大于手续费总额！');
        // }
        // if ($adminUser->power == AdminUser::POWER_MEMBER && $adminUser->retail->deposit < config('web_member_deposit', 100000)) {
        //     return error('微会员提现手续费，自身保证金不能低于'.config('web_member_deposit', 100000).'元！');
        // }
        $adminUser->retail->total_fee += $fee;
        // if ($adminUser->retail->validate()) {
        $adminUser->retail->update(false);
        return success();
        // } else {
            // return error($adminUser->retail);
        // }
    }

    /**
     * @authname 保证金修改
     */
    public function actionDepositWithdraw() 
    {
        $adminUser = AdminUser::find()->with(['retail'])->where(['id' => get('id')])->one();
        $deposit = post('deposit');
        if (!is_numeric($deposit)) {
            return error('保证金输入值非法！');
        }
        $retailWithdraw = new RetailWithdraw();
        $retailWithdraw->admin_id = $adminUser->id;
        $retailWithdraw->amount = $deposit;
        $retailWithdraw->type = RetailWithdraw::TYPE_DEPOSIT;

        $adminUser->retail->deposit += $deposit;
        if ($adminUser->retail->validate()) {
            $retailWithdraw->save();
            $adminUser->retail->update(false);
            return success();
        } else {
            return error($adminUser->retail);
        }
    }

    /**
     * @authname 我的管理员列表
     */
    public function actionMyAdminuserList() 
    {
        $query = (new AdminUser)->search()
            ->andWhere(['state' => AdminUser::STATE_VALID])
            ->andWhere(['power' => AdminUser::POWER_ADMIN]);
        $html = $query->getTable([
            'id' => ['search' => true],
            'username' => ['search' => true],
            'realname' => ['search' => true, 'type' => 'text'],
            'roles' => ['header' => '角色', 'value' => function ($user) {
                $roles = [];
                foreach ($user->roles as $role) {
                    $roles[] = Html::likeSpan($role->item_name);
                }
                return implode('，', $roles);
            }],
            'mobile' => ['type' => 'text'],
            'state' => ['search' => 'select'],
            u()->power <= AdminUser::POWER_ADMIN ?:['type' => ['edit' => 'saveAdmin', 'delete' => 'ajaxDeleteAdmin']]
        ], [
            'addBtn' => u()->power <= AdminUser::POWER_ADMIN ?'':['saveAdmin' => '创建管理员']
        ]
        );

        return $this->render('list', compact('html'));
    }
}
