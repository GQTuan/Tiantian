<?php

namespace admin\components;

use Yii;


class Controller extends \common\components\WebController
{
    public $layout = 'frame';

    public function afterAction($action, $result)
    {
        $actions = ['user-info', 'index', 'login', 'profile', 'verify-code'];
        if (!in_array($this->action->id, $actions) && !user()->isGuest) {
            switch (u()->power) {
                case \common\models\AdminUser::POWER_SETTLE: // 结算会员
                    $deposit = config('web_settle', 1000);
                    break;
                case \common\models\AdminUser::POWER_OPERATE: // 运营中心
                    $deposit = config('web_operate', 1000);
                    break;
                case \common\models\AdminUser::POWER_MEMBER: // 微会员
                    $deposit = config('web_member', 1000);
                    break;    
                default:
                    break;
            }
            if(u()->power < \common\models\AdminUser::POWER_ADMIN && u()->power > \common\models\AdminUser::POWER_RING) {
                    $retail = \common\models\Retail::find()->where(['admin_id' => u()->id])->one();
                   // $userCharge = \common\models\UserCharge::find()->where(['user_id' => u()->id, 'charge_state' => 2])->select('SUM(amount) amount')->one()->amount;
			//test($userCharge, $deposit, u()->id);
                    if($retail->deposit <= $deposit) {
                        return $this->redirect(['site/user-info']);
                    }
                }
            }
        $result = parent::afterAction($action, $result);

        return $result;
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['error', 'delete', 'upload', 'captcha', 'ajax-update', 'delete-all']
                    ],
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if ($action->controller->id === 'site') {
                                return true;
                            }
                            $actionName = $action->controller->id . '/' . lcfirst(str_replace('action', '', $action->actionMethod));
                            return u()->can($actionName);
                        }
                    ],
                ],
                'denyCallback' => function ($rule, $action) {
                    if (req()->isAjax) {
                        return self::error('您没有操作权限~!');
                    } else {
                        self::throwHttpException('您没有操作权限~!');
                    }
                }
            ],
        ];
    }
}
