<?php
/**
 * 公共常量定义
 */
const PAGE_SIZE = 10;
const THEME_NAME = 'basic';
const SECRET_KEY = 'ChisWill';

const WEB_DOMAIN = 'http://www.77el.cn';

const WX_APPID = 'wx9ce56b97b19b26ea';
const WX_MCHID = '1337714901';
const WX_KEY = 'VKcJg2LUnnRPjmYtPX3Tfm8vqradppF9';
const WX_APPSECRET = '52f243db4ce3ec9516e720f875797a76';
const WX_TOKEN = 'jgZBoGWXMKzwixhJ';

const BUY_ORDER_TIME = 15;

const WEB_URL = 'http://www.tradexpo.cn';
//const ZF_BACK_URL = 'http://px43.ubhypz.cn';

const HX_PAY_DOMAIN = 'http://px29.sinasinasohusohu.cn';

//98交易所第三方支付 new
const EXCHANGE_ID = '678028545842921472';
const EXCHANGE_MDKEY = '5e7b13c8287d4fbcae71e8d66bc81e7c';
const EXCHANGE_URL = 'http://payment.shopping98.com/scan/pay/gateway';

// 快乐 支付   江贵权
const HAPPY_APPID = 'gthjgq'; 
const HAPPY_MDKEY = 'QbT8zh%2BcY66wtAdZpl0u%2FA%3D%3D';
const HAPPY_URL = 'http://47.92.120.17:8989/api/api/v1.0/formal';

//百富通
const BFTCKMCH_ID = '6ae2abc7';
const BFTCKMCH_SIGNKEY = '4d75c149367a675d0a59dfd5939e8992';
const BFTCKMCH_DESURL = '0c2071edc1a889f410e6e94e474280d6';

// 智汇宝
const ZF_ID = '9990257';
const ZF_KEY = 'hMogUMIuLfXV0EQKJCPkj6bVmSHXJezI';
const ZF_BACK_URL = 'http://sc.zhbpay.com/bhwlpay/node?';

// EasyPay
const EASYPAY_MERCHANT_NO = "";
const EASYPAY_CURRENCY = "CNY";
const EASYPAY_API_NAME = "com.opentech.cloud.easypay.trade.create";
const EASYPAY_API_VERSION = "0.0.1";
const EASYPAY_PAY_API_NAME = "com.opentech.cloud.easypay.balance.pay";
const EASYPAY_PAY_PASSWORD = "456789";

// DianYunPay
const DIANYUN_MEMBER_ID = "10383";
const DIANYUN_MD5_KEY = "ktern9aUSidDfEq9MRd8Fh1RVzwlZX";
const DIANYUN_POST_URL = "http://www.919km.cn/Pay_Index.html"; //http://www.adsstore.cn/Pay_Index.html

//MingweiPay
const MINGWEI_MERCHANT_NO = "8660592201800090";
const MINGWEI_MERCHANT_KEY = "QDKHJ924TFLVF3G9";
const MINGWEI_QQQR_URL = "http://39.108.235.176/trans/gateway/tran/genQrCode";
const MINGWEI_QQH5_URL = "http://39.108.235.176/trans/gateway/tran/h5Pay";

// 易付通
const YIFU_CUST_ID = "10154"; //商户号
const YIFU_CUST_KEY = "98368f0a75fa4939b2b5ba04ae1861ec"; //商户密钥
const YIFU_QQ_NATIVE = "pay.qq.native"; //QQ扫码支付通道
const YIFU_WX_NATIVE = "pay.weixin.native"; //微信扫码支付通道
const YIFU_YL_NATIVE = "pay.ylsm.native"; //银联扫码支付通道
const YIFU_NET_NATIVE = "pay.net.native"; //网银快捷支付通道
const YIFU_WX_H5 = "pay.h5.weixin"; //微信H5支付通道
const YIFU_QQ_H5 = "pay.h5.qq"; //QQ H5支付通道
const YIFU_QR_URL = "http://47.52.167.242/payinterface/smpay"; //扫码支付请求地址
const YIFU_NET_URL = "http://47.52.167.242/payinterface/netpay"; //网银快捷支付请求地址
const YIFU_H5_URL = "http://47.52.167.242/payinterface/h5/json"; //H5支付请求地址

/**
 * 路径别名定义
 */
Yii::setAlias('common', dirname(__DIR__));
Yii::setAlias('frontend', dirname(dirname(__DIR__)) . '/frontend');
Yii::setAlias('console', dirname(dirname(__DIR__)) . '/console');
Yii::setAlias('api', dirname(dirname(__DIR__)) . '/api');

require Yii::getAlias('@common/config/merchant.php');
/**
 * 引入自定义函数
 */
$files = common\helpers\FileHelper::findFiles(Yii::getAlias('@common/functions'), ['only' => ['suffix' => '*.php']]);
array_walk($files, function ($file) {
    require $file;
});
/**
 * 公共变量定义
 */
common\traits\ChisWill::$date = date('Y-m-d');
common\traits\ChisWill::$time = date('Y-m-d H:i:s');
/**
 * 绑定验证前事件，为每个使用`file`验证规则的字段自动绑定上传组件
 */
common\components\Event::on('common\components\ARModel', common\components\ARModel::EVENT_BEFORE_VALIDATE, function ($event) {
    foreach ($event->sender->rules() as $rule) {
        if ($rule[1] === 'file') {
            $fieldArr = (array) $rule[0];
            foreach ($fieldArr as $field) {
                $event->sender->setUploadedFile($field);
            }
        }
    }
});
/**
 * 日志组件的全局默认配置
 */
Yii::$container->set('yii\log\FileTarget', [
    'logVars' => [],
    'maxLogFiles' => 5,
    'maxFileSize' => 1024 * 5,
    'prefix' => ['common\models\Log', 'formatPrefix']
]);
Yii::$container->set('yii\log\DbTarget', [
    'logVars' => [],
    'prefix' => ['common\models\Log', 'formatPrefix']
]);
