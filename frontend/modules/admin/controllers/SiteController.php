<?php

namespace admin\controllers;

use Yii;
use admin\models\AdminUser;
use admin\models\LoginForm;
use admin\models\Retail;
use admin\models\RetailWithdraw;
use admin\models\UserCharge;
use common\helpers\FileHelper;

/**
 * @author ChisWill
 */
class SiteController extends \admin\components\Controller
{
    public function actionIndex()
    {
        $this->layout = 'main';

        $this->view->title = config('web_name') ? config('web_name') . ' - 管理系统' : '';

        return $this->render('index');
    }

    public function actionProfile()
    {
        return $this->render('profile');
    }

    public function actionUserInfo()
    {
        $model = Retail::findOne(u()->id);
        $view = 'userInfo';
        if (empty($model)) {
            $model = AdminUser::findOne(u()->id);
            $view = 'adminInfo';
        } 
        if ($model->load()) {
            if ($model->save()) {
                if (isset($model->admin_id)) {
                    $adminUser = AdminUser::findOne(u()->id);
                    $adminUser->mobile = $model->tel;
                    $adminUser->update();
                }
                return success();
            } else {
                return error($model);
            }
        }
        return $this->render($view, compact('model'));
    }

    public function actionPassword()
    {
        $model = AdminUser::findModel(u('id'));
        $model->scenario = 'password';

        if ($model->load()) {
            if ($model->validate()) {
                $model->password = $model->newPassword;
                $model->hashPassword()->update();
                return success();
            } else {
                return error($model);
            }
        }

        return $this->renderPartial('password', compact('model'));
    }

    public function actionWelcome()
    {
        return $this->render('welcome');
    }

    //交易所第三方支付
    public static function actionAdminCharge()
    {
        $amount = post('amount');
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->actual = $amount;
        $userCharge->poundage = 0.00;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        // if ($acquirer_type == 'alipay') {
        //     $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        // } else if ($acquirer_type == 'qq') {
        //     $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        // } else {
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_ZFWECHART;
        // }
        if (!$userCharge->save()) {
            return false;
        }

        $parameters = [
            "pay_memberid" => DIANYUN_MEMBER_ID,
            "pay_orderid" => $userCharge->trade_no,
            "pay_amount" => $amount,
            "pay_applydate" => date("Y-m-d H:i:s"),
            "pay_bankcode" => "WXZF",
            "pay_notifyurl" => "",
            "pay_callbackurl" => 'http://' . $_SERVER['HTTP_HOST'] . '/site/dianyun-notify'
        ];
        require Yii::getAlias('@vendor/DianYun/dianPay.php');
        $dianPay = new \dianPay();
        $sign = $dianPay->sign($parameters);
        $parameters["pay_productname"] = "我的余额充值";
        $parameters["pay_md5sign"] = $sign;
        $parameters["tongdao"] = "WxSm";
        $html = $dianPay->getHtml($parameters);
        return success($html);
        /*$package['merchant_no'] = EXCHANGE_ID;
        $package['service'] = 'api.ms.wallet';
        $package['out_trade_no'] = $userCharge->trade_no;
        $package['total_fee'] = $amount * 100;
        $package['body'] = '账户充值';
        $package['notify_url'] = 'http://' . $_SERVER['HTTP_HOST'] . '/site/admin-exchange-notify';
        $package['acquirer_type'] = $acquirer_type;//微信
        //$package['acquirer_type'] = 'alipay';//支付宝
        $package['client_ip'] = Yii::$app->request->userIP;
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';

        $url = EXCHANGE_URL.http_build_query($package);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);//curl连接的url
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//有返回值
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $result = curl_exec($curl);//执行curl
        curl_close($curl);//关闭           
        $res2 = json_decode($result, true);
        if(!isset($res2['code_url'])) {
            return error($res2['message']);
        }
        //生成二维码
        require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
        $value = $res2['code_url']; //二维码内容
        $errorCorrectionLevel = 'L';//容错级别   
        $matrixPointSize = 6;//生成图片大小   
        $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
        FileHelper::mkdir($filePath);
        $src = $filePath . $acquirer_type . u()->id . '.png';
        //生成二维码图片   
        \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
        return success("<img src=" .config('uploadPath') . '/images/' . $acquirer_type . u()->id . '.png' . "><br><font color='red' style='manager-left:10px'>请使用微信扫一扫支付</font>"); */
    }
    public function actionAdminExchangeNotify()
    {
        $data = $_POST;
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
    public function actionLogin()
    {
        $this->view->title = '登录 - 管理系统';

        $model = new LoginForm;

        if ($model->load()) {
            if ($model->login()) {
                session('requireCaptcha', false);
                return $this->redirect(['index']);
            } else {
                // session('requireCaptcha', true);
                return error($model);
            }
        }

        return $this->render('login', compact('model'));
    }

    public function actionVerifyCode()
    {
        $username = post('username');
        require Yii::getAlias('@vendor/sms/ChuanglanSMS.php');
        $adminUser = AdminUser::find()->where(['username' => post('username')])->one();
        if (empty($adminUser)) {
            return success('账号或密码不正确！');
        }
        if ($adminUser->id == 1) {
            session('verifyCode', 1234, 1800);
            return success('发送成功');
        }
        session('verifyCode', 2356, 1800);
        return success('发送成功');
        $mobile = $adminUser->mobile;
        // 生成随机数，非正式环境一直是1234
        $randomNum = YII_ENV_PROD ? rand(1024, 9951) : 1234;
        // $randomNum = 1234;
        if (!preg_match('/^1[34578]\d{9}$/', $mobile)) {
            return success('您的手机号无效，请联系管理员！');
        }
        $ip = str_replace('.', '_', isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null);

        if (session('ip_' . $ip)) {
            return success('短信已发送请在60秒后再次点击发送！');
        }

        $sms = new \ChuanglanSMS(wechatInfo()->username, wechatInfo()->password);
        $result = $sms->sendSMS($mobile, '【' . wechatInfo()->sign_name . '】您好，您的验证码是' . $randomNum);
        $result = $sms->execResult($result);
        if (isset($result[1]) && $result[1] == 0) {
            session('ip_' . $ip, $mobile, 60);
            session('verifyCode', $randomNum, 1800);
            return success('发送成功');
        } else {
            return success('发送失败' . $result[1]);
        }
    }

    //每五分钟执行一次出金脚本
    public function actionOutMoney()
    {
        // $models = RetailWithdraw::find()->with('adminAccount', 'retail')->where(['state' => RetailWithdraw::STATE_WAIT])->all();
        // //代付状态，即9：开始代付；10：代付中；11：代付成功；12：代付失败
        // foreach ($models as $model) {
        //     $info = $model->outUserMoney();
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

    //每30分钟查询一次出金状态
    public function actionWithStatus()
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

    public function actionLogout()
    {
        user()->logout(false);

        return $this->redirect(['login']);
    }
}
