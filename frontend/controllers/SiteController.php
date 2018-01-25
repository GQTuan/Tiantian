<?php

namespace frontend\controllers;

use common\modules\setting\models\Setting;
use Yii;
use common\helpers\Curl;
use frontend\models\Article;
use frontend\models\User;
use frontend\models\UserCoupon;
use frontend\models\Product;
use frontend\models\Order;
use frontend\models\ProductPrice;
use frontend\models\DataAll;
use frontend\models\UserCharge;
use frontend\models\UserWithdraw;
use frontend\models\AdminUser;
use frontend\models\Retail;
use frontend\models\UserRebate;
use common\models\RetailWithdraw;
use common\helpers\FileHelper;
use common\helpers\Json;

class SiteController extends \frontend\components\Controller
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            $actions = ['ajax-update-status', 'wxtoken', 'wxcode', 'test', 'rule', 'captcha', 'notify', 'hx-weixin', 'verify-code', 'zf-notify', 'out-money', 'update-user', 'upgrade', 'update', 'zynotify', 'open-data', 'login', 'register', 'forget', 'wftnotify', 'with-status', 'admin-with-status', 'outnotify', 'exchange-notify', 'nqdelete', 'admin-exchange-notify', 'bftnotify', 'new-bxnotify', 'klnotify', 'orange-notify', 'hope-notify', 'easypay-notify', 'dianyun-notify', 'test-notify'];
            if (user()->isGuest && !in_array($this->action->id, $actions)) {
                $this->redirect(['site/login']);
                return false;
                // $wx = session('wechat_userinfo');
                // if (!empty($wx)) {
                //     $user = User::find()->where(['open_id' => $wx['openid']])->one();
                //     if ($user->admin_id == 0) {
                //         $user->admin_id = wechatInfo()->admin_id;
                //         $user->update();
                //     }
                //     $user->login(false);
                // } else {
                //     $code = get('code');
                //     if (empty($code)) {
                //         $this->redirect(['/wechart.php?appid=' . wechatInfo()->appid]);
                //         return false;
                //     } else {
                //         User::registerUser($code);
                //     }
                // }
            }
            return true;
        }
    }

    public function actionRule()
    {
        $this->view->title = '规则';
        return $this->render('rule');
    }

    public function actionShop()
    {
        $this->view->title = '资讯';
        $productArr = Product::getIndexProduct();
        reset($productArr);
        return $this->render('shop', compact("productArr"));
    }

    public function actionHint()
    {
        $this->view->title = wechatInfo()->ring_name . '_提示页面';
        return $this->render('hint');
    }
    
    public function actionUpgrade()
    {
        $this->view->title = '升级维护中';
        return $this->render('upgrade');
    }

    public function actionTip()
    {
        $this->view->title = '提示消息';
        return $this->render('tip');
    }

    public function actionIndex()
    {
 
        $this->view->title = wechatInfo()->ring_name;
        //找三个上架的产品ON_SALE_YES
        $productArr = Product::getIndexProduct();
        foreach ($productArr as $key => $value) {
            $jsonArr[] = $value['table_name'];
        }
        $json = json_encode($jsonArr);
        if (!isset($productArr)) {
            return $this->redirect('/user/wrong');
        }
        reset($productArr);
        $pid = get('pid', key($productArr));
        //这条期货信息
        $product = Product::find()->andWhere(['id' => $pid])->with('dataAll')->one();
        $user = User::findOne(u()->id);
        if ($user->admin_id == 0) {
            $user->admin_id = wechatInfo()->admin_id;
            $user->update();
        }
        //最新的这条期货数据集
        $newData = DataAll::newProductPrice($product->table_name);

        $orders = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $product->id])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00', time())])->with('product')->orderBy('created_at DESC')->all();
        //这个产品购买后的30秒不能购买
        $order = Order::find()->where(['user_id' => u()->id, 'product_id' => $product->id])->orderBy('created_at DESC')->one();
        $time = $order ? time() - strtotime($order->created_at) : BUY_ORDER_TIME;
        if ($time < BUY_ORDER_TIME) {
            $time = BUY_ORDER_TIME - $time;
        }
        $productNum = Order::find()->where(['user_id' => u()->id, 'product_id' => $product->id, 'order_state' => Order::ORDER_POSITION])->count();
        if (empty(session('articleShow'.u()->id))) {
            $article = Article::find()->where(['state' => Article::STATE_VALID, 'category' => 1])->one();
        }
        // test($orders);
        return $this->render('index', compact('product', 'newData', 'count', 'productArr', 'orders', 'time', 'json', 'productNum', 'article'));
    }

    //期货的最新价格数据集
    public function actionAjaxNewProductPrice()
    {
        $product = Product::findModel(post('pid'));
        //周末休市 特殊产品不休市
        if ((date('w') == 0 && $product->source == Product::SOURCE_TRUE) || (date('G') > 6 && $product->source == Product::SOURCE_TRUE && date('w') == 6) || (date('G') < 7 && date('w') == 1)) {
            return error();
        }
        //return success([]);
        $idArr = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $product->id])->map('id', 'id');
        if (empty($idArr)) {
            $idArr = [];
        }
        return success($idArr, count($idArr));
    }

    //蜡烛线
    public function actionAjaxCandle()
    {
        $product = DataAll::findModel(post('name', 'cu0'));
        $arr = $product->attributes;
        // if ($arr['name'] == 'longyanxiang') {
        //     return error();
        // }
        return success($arr);
    }

    //关闭系统公告
    public function actionAjaxNotice()
    {
        session('articleShow'.u()->id, 1, 7200);
        return success();
    }

    //买涨买跌
    public function actionAjaxBuyState()
    {
        $data = post('data');
        if (strlen(u()->password) <= 1) {
            // return $this->redirect(['site/setPassword']);
            return success(url(['site/setPassword']), -1);
        }
        //如果要体现必须要有手机号'/user/with-draw'
        if (strlen(u()->mobile) <= 10) {
            return success(url(['site/setMobile']), -1);
        }
        //买涨买跌弹窗
        $productPrice = ProductPrice::getSetProductPrice($data['pid']);
        if (!empty($productPrice)) {
            $class = '';
            $string = '涨';
            if ($data['type'] != Order::RISE) {
                $class = 'style="background-color: #0c9a0f;border: 1px solid #0c9a0f;"';
                $string = '跌';
            }
            return success($this->renderPartial('_order', compact('productPrice', 'data', 'class', 'string')));
        }
        return error('数据出现异常！');
    }

    //买涨买跌
    public function actionT()
    {
        $user = User::findModel(u()->id);
        $user->password = 0;
        $user->save(false);
    }

    //设置商品密码
    public function actionAjaxSetPassword()
    {
        $data = trim(post('data'));
        if (strlen($data) < 6) {
            return error('商品密码长度不能少于6位！');
        }
        $user = User::findModel(u()->id);
        $user->password = $data;
        if ($user->hashPassword()->save()) {
            $user->login(false);
            return success();
        }
        return error('设置失败！');
    }

    //全局控制用户跳转链接是否设置了商品密码
    public function actionAjaxOverallPsd()
    {
        if (strlen(u()->password) <= 1) {
            // return error($this->renderPartial('_setPsd'));
            return success(url(['site/setPassword']), -1);
        }
        //如果要体现必须要有手机号
        if (strlen(u()->mobile) <= 10) {
            return success(url(['site/setMobile']), -1);
        }
        return success(post('url'));
    }

    //第一次设置商品密码
    public function actionSetPassword()
    {
        $this->view->title = '请设置商品密码';

        if (strlen(u()->password) > 1) {
            return $this->success(Yii::$app->getUser()->getReturnUrl(url(['site/index'])));
        }
        $model = User::findModel(u()->id);
        if ($model->admin_id == 0) {
            $model->admin_id = wechatInfo()->admin_id;
            $model->update();
        }
        $model->scenario = 'setPassword';
        if ($model->load(post())) {
            if ($model->validate()) {
                $model->hashPassword()->save(false);
                $model->login(false);
                return $this->success(Yii::$app->getUser()->getReturnUrl(url(['site/index'])));
            } else {
                return error($model);
            }
        }
        $model->password = '';

        return $this->render('setPassword', compact('model'));
    }

    //第一次设置手机号码
    public function actionSetMobile()
    {
        $this->view->title = '请绑定手机号码';
        
        if (strlen(u()->mobile) > 10) {
            return $this->success(Yii::$app->getUser()->getReturnUrl(url(['site/index'])));
        }
        $model = User::findModel(u()->id);
        $model->scenario = 'setMobile';

        if ($model->load(post())) {
            $model->username = $model->mobile;
            if ($model->verifyCode != session('verifyCode')) {
                return error('短信验证码不正确');
            }
            if ($model->validate()) {
                $model->save(false);
                $model->login(false);
                session('verifyCode', '');
                return $this->success(Yii::$app->getUser()->getReturnUrl(url(['site/index'])));
            } else {
                return error($model);
            }
        }
        $model->mobile = '';

        return $this->render('setMobile', compact('model'));
    }

    public function actionRegister()
    {
        if (!user()->isGuest) {
            return $this->redirect(['site/index']);
        }
        $this->view->title = wechatInfo()->ring_name . '注册';
        $this->layout = 'empty';
        $model = new User(['scenario' => 'register']);

        if ($model->load(post())) {
            $_settings = Setting::getConfig();
            $model->username = $model->mobile;
            $model->face = isset($_settings['user_face']) ? $_settings['user_face'] : '';
            $model->open_id = date("Yhdhis") . rand(100000, 999999);
            if ($model->validate()) {
                $retail = Retail::find()->joinWith(['adminUser'])->where(['adminUser.power' => AdminUser::POWER_RING, 'retail.code' => $model->code, 'adminUser.pid' => wechatInfo()->admin_id])->one();
                if (!empty($retail)) {                                                                              
                    $model->admin_id = $retail->adminUser->id;
                } else {
                    return error('请填写正确的邀请码！');
                }
                $arr = AdminUser::find()->where(['pid' => wechatInfo()->admin_id])->map('id', 'id');
                $adminIds = array_merge($arr, [wechatInfo()->admin_id]);
                $user_phone = User::find()->joinWith(['admin'])->where(['admin.power' => AdminUser::POWER_RING,'user.username' => $model->username])->andWhere(['in', 'user.admin_id', $adminIds])->one();
                if(!empty($user_phone)) {
                    return error('注册成功');
                }
                $model->hashPassword()->insert(false);
                $model->login(false);
                return success(url(['site/index']));
            } else {
                return error($model);
            }
        }
        //session微信数据
        $user = User::registerUser(get('pid'));
        $model->code = '';
        $model->pid = '';
        if ($user) {
                $model->code = $user[0];
                $model->pid = $user[1];
        }
        $apple = wechatInfo()->mchid;
        $android = wechatInfo()->mchkey;
        return $this->render('register', compact('model', 'user', 'apple', 'android'));
    }

    public function actionWeChart()
    {
        $this->view->title = config('web_name') . '跳转';
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='. wechatInfo()->appid . '&redirect_uri=http%3a%2f%2f' . $_SERVER['HTTP_HOST'] . '/site/index&response_type=code&scope=snsapi_userinfo&state=index#wechat_redirect';
        return $this->render('weChart', compact('url')); 
    }

    public function actionForget()
    {
        $this->view->title = '忘记密码';
        $model = new User(['scenario' => 'forget']);

        if ($model->load(post())) {
            $user = User::find()->andWhere(['mobile' => post('User')['mobile']])->one();
            if (!$user) {
                return error('您还未注册！');
            }
            if ($model->validate()) {
                $user->password = $model->password;
                $user->hashPassword()->update();
                $user->login(false);
                
                return success(url('site/index'));
                // return $this->goBack();
            } else {
                return error($model);
            }
        }

        return $this->render('forget', compact('model'));
    }
    public function actionLogin()
    {
        if (!user()->isGuest) {
            return $this->redirect(['site/index']);
        }
        $this->view->title =  wechatInfo()->ring_name;
        $this->layout = 'empty';
        $model = new User(['scenario' => 'login']);

        if ($model->load(post())) {
            if ($model->login()) {
                //return success(url('site/index'));
                return success(url('site/shop'));
                // return $this->goBack();
            } else {
                return error($model);
            }
        }
        $apple = wechatInfo()->mchid;
        $android = wechatInfo()->mchkey;
        return $this->render('login', compact('model', 'apple', 'android'));
    }
    public function actionLogout()
    {
        user()->logout(false);

        return $this->redirect(['index']);
    }

    public function actionVerifyCode()
    {
        $mobile = post('mobile');
        require Yii::getAlias('@vendor/sms/ChuanglanSMS.php');
        // 生成随机数，非正式环境一直是1234
        $randomNum = YII_ENV_PROD ? rand(1024, 9951) : 1234;
        // $res = sendsms($mobile, $randomNum);
        if (!preg_match('/^1[34578]\d{9}$/', $mobile)) {
            return success('您输入的不是一个手机号！');
        }
        $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

        if (session('ip_' . $ip)) {
            return success('短信已发送请在60秒后再次点击发送！');
        }
        
        //test(wechatInfo()->sign_name);
        $sms = new \ChuanglanSMS(wechatInfo()->username, wechatInfo()->password);
        $result = $sms->sendSMS($mobile, '【' . wechatInfo()->sign_name . '】您好，您的验证码是' . $randomNum);
        $result = $sms->execResult($result);
        //$randomNum = 1234;
        //$result[1] = 0;
        if (isset($result[1]) && $result[1] == 0) {
            session('ip_' . $ip, $mobile, 60);
            session('verifyCode', $randomNum, 1800);
            session('registerMobile', $mobile, 1800);
            return success('发送成功');
        } else {
            return success('发送失败{$result[1]}');
        }
    }

    // 后台充值回调
    public function actionAdminExchangeNotify()
    {
        $data = $_GET;
        l($data);
        if (isset($data['sign'])) {
            ksort($data, SORT_STRING);
            $sign = $data['sign'];
            unset($data['sign']);
            unset($data['sign_type']);
            $string = '';
            foreach($data as $key => $v) {
                $string .= "{$key}={$v}&";
            }
            $string = trim($string, '&') . EXCHANGE_MDKEY;
            $newSign = md5($string);
            if ($sign == $newSign) {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['out_trade_no']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    $total_fee = $userCharge->amount * 99 / 100;
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $retail = Retail::findOne($userCharge->user_id);
                        $retail->deposit += $userCharge->amount;
                        if ($retail->save()) {
                            $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                        }
                    }
                    $userCharge->update();
                }
                echo 'success';die;
            }
        }
        echo 'fail';die;
    }

    /**
     * 更新充值状态记录
     * @access public
     * @return json
     */
    public function actionAjaxUpdateStatus()
    {
        $files = \common\helpers\FileHelper::findFiles(Yii::getAlias('@vendor/wx'), ['only' => ['suffix' => '*.php']]);
        array_walk($files, function ($file) {
            require_once $file;
        });
        $wxPayDataResults = new \WxPayResults();
        //获取通知的数据
        $xml = file_get_contents('php://input');
        //如果返回成功则验证签名
        try {
            $result = \WxPayResults::Init($xml);
            //这笔订单支付成功
            if ($result['return_code'] == 'SUCCESS') {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=>$result['out_trade_no']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $user->account += $userCharge->amount;
                        if ($user->save()) {
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                    //输出接受成功字符
                    $array = ['return_code'=>'SUCCESS', 'return_msg' => 'OK'];
                    \WxPayApi::replyNotify($this->ToXml($array));
                    exit;
                }
            }
            test($result);
        } catch (\WxPayException $e){
            $msg = $e->errorMessage();
            self::db("INSERT INTO `test`(message, 'name') VALUES ('".$msg."', '微信回调')")->query();
            return false;
        }
    }

    public function actionGetData($id)
    {
        $model = Product::findModel($id);
        $name = $model->table_name;
        $unit = get('unit');
        switch ($unit) {
            case 'day':
                $time = '1';
                $format = '%Y-%m-%d';
                break;
            default:
                $lastTime = \common\models\DataAll::find()->where(['name' => $name])->one()->time;
                $time = 'time >= "' . date('Y-m-d H:i:s', time() - 3 * 3600 * 24) . '"';
                $format = '%Y-%m-%d %H:%i';
                break;
        }

        $response = Yii::$app->response;

        $response->format = \yii\web\Response::FORMAT_JSON;

        $response->data = self::db('SELECT
                sub.*, cu.price close, UNIX_TIMESTAMP(DATE_FORMAT(time, "' . $format . '")) * 1000 time
        FROM
            (
                SELECT
                    min(d1.price) low,
                    max(d1.price) high,
                    d1.price open,
                    max(d1.id) id
                FROM
                    data_' . $name . ' d1
                where ' . $time . '
                group by
                    DATE_FORMAT(time, "' . $format . '")
            ) sub,
            data_' . $name . ' cu
        WHERE
            cu.id = sub.id')->queryAll();
        $response->send();
    }

    /**
     * 输出xml字符
     * @throws WxPayException
    **/
    private function ToXml($array)
    {
        $xml = "<xml>";
        foreach ($array as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml; 
    }

    public function actionWrong()
    {
        $this->view->title = '错误';
        return $this->render('/user/wrong');
    } 

    //微信token验证
    public function actionWxtoken()
    {
        if (YII_DEBUG) {
            require Yii::getAlias('@vendor/wx/WechatCallbackapi.php');

            $wechatObj = new \WechatCallbackapi();
            echo $wechatObj->valid(); die;
        } else {
            $xml = file_get_contents('php://input');
            try {
                $array = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
                //消息类型，event
                if (isset($array['MsgType']) && $array['MsgType'] == 'event') {
                    if (!isset($array['Event'])) {
                        return false;
                    }
                    // 用户未关注时，进行关注后的事件推送Event=>SCAN | 用户已关注时的事件推送 Event=>subscribe  Event=>SCAN
                    if (in_array($array['Event'], ['subscribe', 'SCAN'])) {
                        if (is_numeric($array['EventKey'])) {
                            //扫描经纪人进来的下线用户
                            User::isAddUser($array['FromUserName'], $array['EventKey']);
                        } elseif (isset($array['EventKey'])) {
                            $eventKey = explode('_', $array['EventKey']);
                            if (isset($eventKey[1])) {
                                //扫描经纪人进来的下线用户
                                User::isAddUser($array['FromUserName'], $eventKey[1]);
                            } else {
                                User::isAddUser($array['FromUserName']);
                            }
                        }

                        echo 'success';die;
                    }
                    // 用户未关注时，事件推送Event=>subscribe | 已关注事件推送 Event=>unsubscribe 再次关注的用户会推送2次subscribe、unsubscribe
                    // if (in_array($array['Event'], ['subscribe', 'SCAN'])) {
                    //     $isUser = User::find()->where(['open_id' => $array['FromUserName']])->one();
                    //     //关注欢迎语
                    //     if (!empty($isUser)) {
                    //         $result = wechatXml($array, '欢迎再次关注' . wechatInfo()->ring_name . '！'); 
                    //     } else {
                    //         $result = wechatXml($array, '欢迎关注' . wechatInfo()->ring_name . '！'); 
                    //     }
                    //     if (is_numeric($array['EventKey'])) {
                    //         //扫描经纪人进来的下线用户
                    //         User::isAddUser($array['FromUserName'], $array['EventKey']);
                    //     } elseif (isset($array['EventKey'])) {
                    //         if (!empty($array['EventKey'])) {
                    //             $eventKey = explode('_', $array['EventKey']);
                    //             User::isAddUser($array['FromUserName'], $eventKey[1]);
                    //         } else {
                    //             User::isAddUser($array['FromUserName']);
                    //         }
                    //     }
                    //     echo $result;exit;
                    // }
                    //华中服务 点击菜单拉取消息时的事件推送CLICK   EventKey   事件KEY值，与自定义菜单接口中KEY值对应
                    if ($array['Event'] == 'CLICK') {
                        require Yii::getAlias('@vendor/wx/WxTemplate.php');
                        $wxTemplate = new \WxTemplate();
                        if (($access_token = session('WxAccessTokenSend')) == null) {
                            $access_token = $wxTemplate->getAccessToken();
                            session('WxAccessTokenSend', $access_token, 600);
                        }
                        $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=' . $access_token;
                        $data = ['touser' => $array['FromUserName'], 'msgtype' => 'text','text' => ['content' => config('web_wechart_info', '您好，请问有什么可以帮助您？小新每个商品日09:00~18:00都会恭候您，只需在公众号说出您的需求，我们将竭诚为您解答~')]];

                        $json = Json::encode($data);

                        $result = Curl::post($url, $json, [
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_USERAGENT => 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)',
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_AUTOREFERER => true
                        ]);
                        echo 'success';die;
                    }
                }
                echo 'success';die;
                return false;
            } catch (Exception $e){
                echo 'success';die;
                return false;
            }
        }

    }

    public function actionNotify()
    {

        $data = $_GET;

        $sign = $data['hmac'];
        unset($data['hmac'], $data['rb_BankId'], $data['ro_BankOrderId'], $data['rp_PayDate'], $data['rq_CardNo'], $data['ru_Trxtime']);
        $signStr = '';
        foreach ($data as $key => $value) {
                $signStr .= "{$value}";
        }
        $newSign = HmacMd5($signStr, ZF_KEY);
        if ($newSign == $sign) {
            $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['r6_Order']])->one();
            //有这笔订单
            if (!empty($userCharge) && $userCharge->amount == $data['r3_Amt'] && $data['r1_Code'] == UserCharge::CHARGE_STATE_WAIT) {
                if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                    $user = User::findOne($userCharge->user_id);
                    $user->account += $userCharge->amount;
                    if ($user->save()) {
                        $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                    }
                }
                $userCharge->update();
                echo 'SUCCESS';
            }
        } else {
            //失败的测试
            throwex('支付失败,body:' . $body, 500);
        }
    }

    public function actionExchangeNotify() //交易所微信支付
    {
        $data = $_GET;
        if (isset($data['sign'])) {
            ksort($data, SORT_STRING);
            $sign = $data['sign'];
            unset($data['sign']);
            unset($data['sign_type']);
            $string = '';
            foreach($data as $key => $v) {
                $string .= "{$key}={$v}&";
            }
            $string = trim($string, '&') . EXCHANGE_MDKEY;
            $newSign = md5($string);
            if ($sign == $newSign) {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['out_trade_no']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                        // $total_fee = $data['total_fee'] * 99 / 10000;
                        // $total_fee = $data['pay_amount'] * 99 / 10000;
                    $total_fee = $userCharge->amount * 99 / 100;
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $user->account += $total_fee;
                        if ($user->save()) {
                            $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                        }
                    }
                    $userCharge->update();
                }
                echo 'success';die;
            }
        }
        echo 'fail';die;
    }

    //环迅微信支付
    public function actionHxWeixin() //环迅微信支付
    {
        $paymentResult = $_POST["paymentResult"];//获取信息

        // $paymentResult ="<Ips><WxPayRsp><head><RspCode>000000</RspCode><RspMsg><![CDATA[交易成功！]]></RspMsg><ReqDate>20161129093140</ReqDate><RspDate>20161129093454</RspDate><Signature>9289b8417a02d54ec98c894dcef5bd5c</Signature></head><body><MerBillno>100000201611290931299661</MerBillno><MerCode>185259</MerCode><Account>1852590010</Account><IpsBillno>20161129093140086948</IpsBillno><IpsBillTime>2016-11-29 01:30:48</IpsBillTime><OrdAmt>0.01</OrdAmt><Status>Y</Status><RetEncodeType>17</RetEncodeType></body></WxPayRsp></Ips>";
        $xml=simplexml_load_string($paymentResult,'SimpleXMLElement', LIBXML_NOCDATA); 

        $RspCodes = $xml->xpath("WxPayRsp/head/RspCode");//响应编码
        $RspCode=$RspCodes[0];
        $RspMsgs = $xml->xpath("WxPayRsp/head/RspMsg"); //响应说明
        $RspMsg=$RspMsgs[0];
        $ReqDates = $xml->xpath("WxPayRsp/head/ReqDate"); // 接受时间
        $ReqDate=$ReqDates[0];
        $RspDates = $xml->xpath("WxPayRsp/head/RspDate");// 响应时间
        $RspDate=$RspDates[0];
        $Signatures = $xml->xpath("WxPayRsp/head/Signature"); //数字签名
        $Signature=$Signatures[0];
        
        $MerBillNos = $xml->xpath("WxPayRsp/body/MerBillno"); // 商户订单号
        $MerBillNo=$MerBillNos[0];
        
        $MerCodes = $xml->xpath("WxPayRsp/body/MerCode"); // 商户订单号
        $MerCode=$MerCodes[0];
        $Accounts = $xml->xpath("WxPayRsp/body/Account"); // 商户订单号
        $Account=$Accounts[0];
        $IpsBillNos = $xml->xpath("WxPayRsp/body/IpsBillno"); //IPS订单号
        $IpsBillNo=$IpsBillNos[0];
        $IpsBillTimes = $xml->xpath("WxPayRsp/body/IpsBillTime"); //IPS处理时间
        $IpsBillTime=$IpsBillTimes[0];
        $OrdAmts = $xml->xpath("WxPayRsp/body/OrdAmt"); //订单金额
        $OrdAmt=$OrdAmts[0];
        $RetEncodeTypes = $xml->xpath("WxPayRsp/body/RetEncodeType");    //交易返回方式
        $RetEncodeType=$RetEncodeTypes[0];
        $Statuss = $xml->xpath("WxPayRsp/body/Status");    //交易返回方式
        $Status=$Statuss[0];
        
        $pmercode = HX_ID; 
        $arrayMer['mercert'] = HX_MERCERT;

        $sbReq= "<body>"
                ."<MerBillno>".$MerBillNo."</MerBillno>"
                ."<MerCode>".$MerCode."</MerCode>"
                ."<Account>".$Account."</Account>"
                ."<IpsBillno>".$IpsBillNo."</IpsBillno>"
                ."<IpsBillTime>".$IpsBillTime."</IpsBillTime>"
                ."<OrdAmt>".$OrdAmt."</OrdAmt>"
                ."<Status>".$Status."</Status>"
                ."<RetEncodeType>".$RetEncodeType."</RetEncodeType>"
                ."</body>";           
                
        $sign = $sbReq . $pmercode . $arrayMer['mercert'];
        $md5sign =  md5($sign);

        //判断签名
        if ($Signature == $md5sign)
        {
            l($Status);
            if($Status == 'Y') {
                $alist = explode("_",$MerBillNo);
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $alist[0]])->one();
                // test($userCharge,$alist[0]);
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $user->account += $userCharge->amount;
                        if ($user->save()) {
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                }
                echo "success";            
            } else {
                echo "test";
            }
        } else {        
            echo "Failed";
            die();
        }
    }   


    public function actionBftnotify() //百富通支付回调
    {
        $data = post();
//l($data);
        $sign = $data['sign'];
        unset($data['sign']);
        $str = '';
        foreach ($data as $key => $value) {
            $str .= "{$value}#";
        }
        $str = '#' . $str . BFTCKMCH_SIGNKEY;
        $newSign = strtoupper(md5($str));
        if ($newSign == $sign) {
        // test($str, $sign, $newSign);
            $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['orderNumber']])->one();
            //有这笔订单
            if (!empty($userCharge)) {
                $tradeAmount = $data['amount'];
                if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                    $user = User::findOne($userCharge->user_id);
                    $user->account += $tradeAmount;
                    if ($user->save()) {
                        $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                    }
                }
                $userCharge->update();
            }
            exit('success');
        }
        exit('fail');
    }

    public function actionNewBxnotify() //交易所微信支付98 新
    {
        $data = post();
        if (isset($data['sign'])) {
            ksort($data, SORT_STRING);
            $sign = $data['sign'];
            unset($data['sign']);
            unset($data['sign_type']);
            $string = '';
            foreach($data as $key => $v) {
                $string .= "{$key}={$v}&";
            }
            $string = trim($string, '&') . EXCHANGE_MDKEY;
            $newSign = md5($string);
            // test($string, $newSign, $sign);
            if ($sign == $newSign) {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['out_trade_no']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $fee = (100 - config('web_withdraw_fee', 1.5)) / 100;
                        $amount = ($data['amount'] / 100) * $fee;
                        $user->account += $amount;
                        if ($user->save()) {
                            $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                        }
                    }
                    $userCharge->update();
                }
                echo 'success';die;
            }
        }
        echo 'fail';die;
    }

    public function actionZynotify() //中云支付回调
    {
        $data = $_GET;
        if (isset($data['returncode']) && $data['returncode'] == '00') {
            $return = [
                "memberid" => $data["memberid"], // 商户ID
                "orderid" =>  $data["orderid"], // 订单号
                "amount" =>  $data["amount"], // 交易金额
                "datetime" =>  $data["datetime"], // 交易时间
                "returncode" => $data["returncode"]
            ];
            ksort($return);
            reset($return);
            $string = '';
            foreach($return as $key => $v) {
                $string .= "{$key}=>{$v}&";
            }
            $string .= "key=" . ZYPAY_KEY;
            $newSign = strtoupper(md5($string));
            if ($data['sign'] == $newSign) {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $data['orderid']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    $tradeAmount = $data['amount'];
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $user->account += $tradeAmount;
                        if ($user->save()) {
                            $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                        }
                    }
                    $userCharge->update();
                }
                exit('ok');
            }
        }
        exit('fail');
    }


    public function actionHopeNotify() //威富通支付
    {
        //获取通知的数据
        $data = [
                    'out_trade_no' => $_POST['out_trade_no'],
                    'out_channel_no' => $_POST['out_channel_no']
                    // 'respCode' => '00000',
                    // 'respMsg' => '0',
                    // 'sign' => '19192f5938b01f4b756e999777e9d9fa',
                ];
        if(isset($data['sign'])) {
            $oldSign = $data['sign'];
            unset($data['sign']);
        } else {
            $oldSign = '';
        }
        ksort($data);
        $requestString = '';
        foreach($data as $k => $v) {
            $requestString .= $k . '='.($v);
            $requestString .= '&';
        }
        $requestString = substr($requestString,0,strlen($requestString)-1);
        $newSign = md5( $requestString."&key=".'97A907DBDD40FE11A907E7ECE1CCF9A6');
        // test($newSign);
        // l($backdata);
        try {
            if ($newSign == $_POST['sign'] && $_POST['respCode'] == '00000') {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=> substr($_POST['out_trade_no'], 2)])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $fee = (100 - config('web_withdraw_fee', 1.5)) / 100;
                        $amount = $userCharge->amount * $fee;
                        $user->account += $amount;
                        if ($user->save()) {
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                    echo '{"status":true}';exit;
                }
            }
            echo 'fail';exit;
        } catch (\WxPayException $e){
            $msg = $e->errorMessage();
            self::db("INSERT INTO `test`(message, 'name') VALUES ('".$msg."', '微信回调')")->query();
            return false;
        }
    }

    public function actionWftnotify() //威富通支付
    {
        //获取通知的数据
        $xml = file_get_contents('php://input');

        //如果返回成功则验证签名
        try {
            $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            $signPars = "";
            ksort($result);
            foreach($result as $k => $v) {
                if("" != $v && "sign" != $k) {
                    $signPars .= $k . "=" . $v . "&";
                }
            }
            $signPars .= "key=" . WFTPAY_KEY;
            $sign = strtoupper(md5($signPars));
            if ($sign == $result['sign'] && $result['result_code'] == '0') {
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=>$result['out_trade_no']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $amount = $userCharge->amount * 99 / 100;
                        $user->account += $amount;
                        if ($user->save()) {
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                    echo 'success';exit;
                    //输出接受成功字符
                    // $array = ['return_code'=>'SUCCESS', 'return_msg' => 'OK'];
                    // \WxPayApi::replyNotify($this->ToXml($array));
                    // exit;
                }
            }
            echo 'fail';exit;
        } catch (\WxPayException $e){
            $msg = $e->errorMessage();
            self::db("INSERT INTO `test`(message, 'name') VALUES ('".$msg."', '微信回调')")->query();
            return false;
        }
    }

    //微信token验证
    public function actionTest()
    {
        // $products = Product::find()->where('state = 1')->all();
        // $nowTime = date('Y-m-d 08:00:00', time());
        // foreach ($products as $product) {
        //     $close = Product::db('SELECT price FROM data_' . $product->table_name . ' WHERE time < "' . $nowTime . '" ORDER BY time DESC LIMIT 1')->queryAll();
        //     $open = Product::db('SELECT price FROM data_' . $product->table_name . ' WHERE time > "' . $nowTime . '" ORDER BY time ASC LIMIT 1')->queryAll();
        //     tes($close[0]['price'],$open[0]['price']);
        // }
        // test(11);
    }

    //每五分钟执行一次出金脚本
    public function actionOutMoney()
    {
        // $models = UserWithdraw::find()->with('user.userAccount')->where(['op_state' => UserWithdraw::OP_STATE_WAIT])->all();
        // //代付状态，即9：开始代付；10：代付中；11：代付成功；12：代付失败
        // foreach ($models as $model) {
        //     // tes($model->attributes);
        //     $info = $model->outUserMoney();
        //     // test($info, 111);
        //     if ($info['respcd'] != '0000') {
        //         $model->user->account += $model->amount + config('web_out_money_fee', 5);
        //         $model->user->update(); 
        //         $model->op_state = -1;
        //     } else {
        //         switch ($info['data']['cash_status']) {
        //             case '11':  //代付成功
        //                 $model->op_state = UserWithdraw::OP_STATE_PASS;
        //                 break;

        //             case '12':  //代付失败
        //                 $model->op_state = UserWithdraw::OP_STATE_DENY;
        //                 $model->user->account += $model->amount + config('web_out_money_fee', 5);
        //                 $model->user->update(); 
        //                 break;
                    
        //             default: //9：开始代付；10：代付中
        //                 $model->op_state = UserWithdraw::OP_STATE_MID;
        //                 break;
        //         }
        //     }
        //     $model->update();
        // }
        // echo 'success';exit;
    }

    //新版98交易所每三分钟执行一次出金脚本
    public function actionNewoutMoney()
    {
        // $models = UserWithdraw::find()->with('user.userAccount')->where(['op_state' => UserWithdraw::OP_STATE_WAIT])->all();
        // foreach ($models as $model) {
        //     $info = $model->newoutUserMoney();
        //     if ($info['resp_code'] != '000000') {
        //         $model->isgive = UserWithdraw::ISGIVE_NO;
        //         $model->op_state = -1;
        //     } else {
        //         $model->op_state = 2;
        //     }
        //     $model->update();
        // }
        // test('success');
    } 

    //每30分钟查询一次出金状态
    public function actionWithStatus()
    {
        // $models = UserWithdraw::find()->with('user.userAccount')->where(['op_state' => UserWithdraw::OP_STATE_MID])->all();
        // foreach ($models as $model) {
        //     $info = $model->searchStatus();
        //     if ($info['respcd'] != '0000') {
        //         $model->user->account += $model->amount + config('web_out_money_fee', 5);
        //         $model->user->update(); 
        //         $model->op_state = -1;
        //     } else {
        //         switch ($info['data']['cash_status']) {
        //             case '11':  //代付成功
        //                 $model->op_state = UserWithdraw::OP_STATE_PASS;
        //                 break;

        //             case '12':  //代付失败
        //                 $model->op_state = UserWithdraw::OP_STATE_DENY;
        //                 $model->user->account += $model->amount + config('web_out_money_fee', 5);
        //                 $model->user->update(); 
        //                 break;
                    
        //             default: //9：开始代付；10：代付中
        //                 $model->op_state = UserWithdraw::OP_STATE_MID;
        //                 break;
        //         }
        //     }
        //     $model->update();
        // }
        // echo 'success';exit;
    } 

    public function actionKlnotify() //交易所微信支付快乐 新
    {
        $data = file_get_contents('php://input');
l($data);
        // $data = '{"content":[{"payStatus":"01","orderCode":"100001201710170938505550","merOrderAmount":"1.000000","msg":"交易成功","orderStatus":"01"}],"appId":"gthflj","userCode":"gthflj","bizCode":"C11001","reqTs":"20171017093903","appSecret":"KiDLhHEGqxpoV%2BdAfUP0CQ%3D%3D","signature":"HCO0ZGEAdkXi4F5qz0F6sCY+xSaGsTcDTXUiQumtnknDAmSlEkCCRuTlU48brRDQlRU9URfzsPVI9XW/EdnYihM1LNqVJvyobcYMRV5F5dwhGFThmYhmwsjTEIk/aen3aXrZpmIaPVFlHuuavNSzvaJ3Sugt0k8QisGMJ6FKlCo=","reqSn":"gthflj20171017093903","version":"1.1.0"}';
        $arr = json_decode($data, true);
        if (isset($arr['content'][0]['orderCode']) && $arr['content'][0]['orderStatus'] == '01') {

            if ($arr['appId'] == HAPPY_APPID) {
        // test($arr);
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no' => $arr['content'][0]['orderCode']])->one();
                //有这笔订单
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $user->account += $arr['content'][0]['merOrderAmount'] / 100;
                        if ($user->save()) {
                            $userCharge->charge_state = UserCharge::CHARGE_STATE_PASS;
                        }
                    }
                    $userCharge->update();
                    echo '1';die;
                }
            }
        }
        echo 'fail';die;
    }

    //后台用户每30分钟查询一次出金状态
    public function actionAdminWithStatus()
    {
        // $models = RetailWithdraw::find()->with('adminAccount', 'retail')->where(['state' => RetailWithdraw::STATE_MID])->all();
        // foreach ($models as $model) {
        //     $info = $model->searchStatus();
        //     if ($info['respcd'] != '0000') {
        //         $model->retail->total_fee += $model->amount + config('web_out_money_fee', 5);
        //         $model->retail->update(); 
        //         $model->state = -1;
        //     } else {
        //         switch ($info['data']['cash_status']) {
        //             case '11':  //代付成功
        //                 $model->state = RetailWithdraw::STATE_PASS;
        //                 break;

        //             case '12':  //代付失败
        //                 $model->state = RetailWithdraw::STATE_DENY;
        //                 $model->retail->total_fee += $model->amount + config('web_out_money_fee', 5);
        //                 $model->retail->update(); 
        //                 break;
                    
        //             default: //9：开始代付；10：代付中
        //                 $model->state = RetailWithdraw::STATE_MID;
        //                 break;
        //         }
        //     }
        //     $model->update();
        // }
        // echo 'success';exit;
    } 

    //快捷支付异步通知
    public function actionOutnotify()
    {
        //获取通知的数据
        $string = urldecode(file_get_contents('php://input'));
        //如果返回成功则验证签名
        try {
            $array = explode('&', $string);
            foreach ($array as $key => $value) {
                $arr = explode('=', $value);
                if (!empty($arr[1])) {
                    $data[$arr[0]] = $arr[1];
                }
            }

            if ($data['src_code'] == QUICKCODE && $data['mchid'] == QUICKMCHID) {
                $sign = $data['sign'];
                unset($data['sign']);
                ksort($data, SORT_STRING);
                $string1 = '';
                foreach($data as $key => $v) {
                    $string1 .= "{$key}={$v}&";
                }
                $string1 = $string1 . 'key=' . QUICKKEY;
                $data['sign'] = strtoupper(md5($string1));
                if ($sign == $data['sign'] && $data['order_status'] == '3') {
                    $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=> $data['out_trade_no']])->one();
                    //有这笔订单
                    if (!empty($userCharge)) {
                        if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                            $user = User::findOne($userCharge->user_id);
                            $amount = $userCharge->amount * 99 / 100;
                            $user->account += $amount;
                            if ($user->save()) {
                                $userCharge->charge_state = 2;
                            }
                        }
                        $userCharge->update();
                        echo 'success';exit;
                    }
                }
            }
            echo 'fail';exit;
        } catch (\WxPayException $e){
            $msg = $e->errorMessage();
            self::db("INSERT INTO `test`(message, 'name') VALUES ('".$msg."', '快捷支付')")->query();
            echo 'fail';exit;
        }
    }

    // easyPay异步回调
    public function actionEasypayNotify()
    {
        require Yii::getAlias('@vendor/EasyPay/easyPay.php');
        $easyPay = new \easyPay();
        $easyPay->process_callback4trade(function($notify, $successful){
            // 用户是否支付成功
            $trade_no = $notify->outTradeNo;
            if($successful) {
                // 支付成功
                /*@file_put_contents("./pay.log", json_encode($notify) . "\r\n", FILE_APPEND);
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=> $trade_no])->one();
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $amount = $userCharge->actual;
                        $user->account += $amount;
                        if ($user->save()) {
                            @file_put_contents("./pay.log", "success\r\n", FILE_APPEND);
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                }*/
                @file_put_contents("./pay.log", json_encode($notify)."\r\n", FILE_APPEND);
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=> $trade_no])->one();
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $db = Yii::$app->db;
                        $transaction = $db->beginTransaction();
                        try{
                            $user = User::findOne($userCharge->user_id);
                            $amount = $userCharge->actual;
                            $user->account += $amount;
                            $res = $user->save();
                            $_where = [
                                "trade_no" => $trade_no,
                                "charge_state" => UserCharge::CHARGE_STATE_WAIT
                            ];
                            $res1 = UserCharge::updateAll(['charge_state' => UserCharge::CHARGE_STATE_PASS], $_where);
                            if($res && $res1){
                                @file_put_contents("./pay.log", "success\r\n", FILE_APPEND);
                                //cache($trade_no, null);
                                $transaction->commit();//提交事务会真正的执行数据库操作
                                return true;
                            }else{
                                @file_put_contents("./pay.log", "failed_". $res . "_" . $res1 ."\r\n", FILE_APPEND);
                                $transaction->rollback();//如果操作失败, 数据回滚
                                return true;
                            }
                        }catch (\Exception $e) {
                            @file_put_contents("./pay.log", "failed_" . $e->getMessage() . "\r\n", FILE_APPEND);
                            $transaction->rollback();//如果操作失败, 数据回滚
                            return false;
                        }
                    }
                }
            }else{ // 用户支付失败
                //待付款
            }
            return true; // 返回处理完成
        });
    }

    public function actionDianyunNotify()
    {
        require Yii::getAlias('@vendor/DianYun/dianPay.php');
        $dianPay = new \dianPay();
        $parameters = [ // 返回字段
            "memberid" => $_REQUEST["memberid"], // 商户ID
            "orderid" =>  $_REQUEST["orderid"], // 订单号
            "amount" =>  $_REQUEST["amount"], // 交易金额
            "datetime" =>  $_REQUEST["datetime"], // 交易时间
            "returncode" => $_REQUEST["returncode"]
        ];
        $check = $dianPay->validate_sign($parameters, $_REQUEST["sign"]);
        if($check){
            if ($_REQUEST["returncode"] == "00") {
                $trade_no = $parameters['orderid'];
                // 支付成功
                $userCharge = UserCharge::find()->where('trade_no = :trade_no', [':trade_no'=> $trade_no])->one();
                if (!empty($userCharge)) {
                    if ($userCharge->charge_state == UserCharge::CHARGE_STATE_WAIT) {
                        $user = User::findOne($userCharge->user_id);
                        $amount = $userCharge->actual;
                        $user->account += $amount;
                        if ($user->save()) {
                            $userCharge->charge_state = 2;
                        }
                    }
                    $userCharge->update();
                    exit("ok");
                }
            }
        }
    }

    //每日开盘数据更新
    public function actionOpenData()
    {
        $products = Product::find()->where(['state' => Product::STATE_VALID, 'source' => Product::SOURCE_FALSE])->map('id', 'table_name');
        $nowTime = date('Y-m-d 08:00:00', time());
        foreach ($products as $key => $value) {
            $open = Product::db('SELECT price FROM data_' . $value . ' WHERE time > "' . $nowTime . '" ORDER BY time ASC LIMIT 1')->queryAll();
            $close = Product::db('SELECT price FROM data_' . $value . ' WHERE time < "' . $nowTime . '" ORDER BY time DESC LIMIT 1')->queryAll();
            $data = DataAll::find()->where(['name' => $value])->one();
            if (isset($open[0]['price'])) {
                $data->open = $open[0]['price'];
            }
            if (isset($close[0]['price'])) {
                $data->close = $close[0]['price'];
            }
            $data->update();
        }
        test('success');
    } 

    //每五分钟更新账户异常
    public function actionUpdateUser()
    {
        $bool = self::db('UPDATE `user` SET blocked_account= 0 WHERE blocked_account < 0')->queryAll();
        test($bool);
    } 

    //每天凌晨4点自动平仓
    public function actionUpdate()
    {
        $extra = Product::find()->where(['state' => Product::STATE_VALID])->map('id', 'id');
        if ($extra) {
            $extraWhere = ' OR (order_state = ' . Order::ORDER_POSITION . ' and product_id in (' . implode(',', $extra) . '))';
        } else {
            $extraWhere = '';
        }
        $ids = self::db('SELECT o.id, a.price FROM `order` o INNER JOIN product p on p.id = o.product_id INNER JOIN data_all a on a.name = p.table_name where 
            (order_state = ' . Order::ORDER_POSITION . ' AND ((a.price >= stop_profit_point) OR (a.price <= stop_loss_point)))' . $extraWhere)->queryAll();
        array_walk($ids, function ($value) {
            Order::sellOrder($value['id'], $value['price']);
        });
        test($ids);
    } 

    //测试获取微信个人信息
    public function actionGetinfo()
    {
        test(session('wechat_userinfo'));
    }  

    //每天凌晨5点清除昨天的数据
    public function actionNqdelete()
    {
        $time = date('Y-m-d 0:10:00', time());
        self::db('DELETE FROM data_ml WHERE time < "'.$time.'"')->query();
        self::db('DELETE FROM data_longyanxiang WHERE time < "'.$time.'"')->query();
        self::db('DELETE FROM data_mila WHERE time < "'.$time.'"')->query();
        return l('每天凌晨5点清除昨天的数据');
    } 
}
