<?php

namespace frontend\components;

use Yii;
use frontend\models\User;

/**
 * frontend 控制器的基类
 */
class Controller extends \common\components\WebController
{
    public function init()
    {
        parent::init();
    }
    
    public function beforeAction($action)
    {
        // if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') && !in_array($this->action->id, ['hint', 'login', 'register', 'forget', 'verify-code'])) {
        //     // $this->redirect(['site/hint']);
        // }
        // if (config('web_state', 1) == 1 && $this->action->id != 'upgrade') {
        //     return $this->redirect(['site/upgrade']);
        // }
            // $user = User::findModel('100001');
            // $user->login(false);
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            return true;
        }
    }
}
