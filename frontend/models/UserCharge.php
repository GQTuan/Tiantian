<?php

namespace frontend\models;

use common\modules\setting\models\Setting;
use Yii;
use common\helpers\FileHelper;

class UserCharge extends \common\models\UserCharge
{
    public $resHandler = null;
    public $reqHandler = null;
    public $pay = null;
    public $cfg = null;

    public function rules()
    {
        return array_merge(parent::rules(), [
            // [['field1', 'field2'], 'required', 'message' => '{attribute} is required'],
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            // 'scenario' => ['field1', 'field2'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'field1' => 'description1',
            // 'field2' => 'description2',
        ]);
    }

    //智汇宝
    public static function pay($amount, $acquirer_type = 'wxwap')
    {
        //$amount = 12;
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
        if ($acquirer_type == 'alipaywap') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        }elseif($acquirer_type == 'qqwap') {
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        }
        if (!$userCharge->save()) {
            return false;
        }
        $data['p0_Cmd'] = 'Buy';
        $data['p1_MerId'] = ZF_ID;
        $data['p2_Order'] = $userCharge->trade_no;
        $data['p3_Amt'] = $amount;
        $data['p4_Cur'] = 'CNY';
        $data['p5_Pid'] = 'zhongxing';
        $data['p6_Pcat'] = 'pays';
        $data['p7_Pdesc'] = 'zhongxing';  
        $data['p8_Url'] = url(['site/notify'], true); 
        $data['pa_MP'] = '123'; //  拓展
        if($acquirer_type == 'bank') {
            $data['pd_FrpId'] = '';
        }else {
            $data['pd_FrpId'] = $acquirer_type;
        }
        $data['pr_NeedResponse'] = '1';

        $string = '';
        foreach($data as $key => $v) {
            $string .= "{$v}";
        }
        $data['hmac'] = HmacMd5($string, ZF_KEY);
        $url = ZF_BACK_URL . http_build_query($data);
        header("Location:{$url}"); 
        die;
    }


    //希望支付
    public static function payHope($amount, $acquirer_type = 'T0')
    {
        // $amount = 1;
        // test(123);
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
        if ($acquirer_type == 'RFHT200012001') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        }elseif($acquirer_type == 'RFHT200013001') {
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        }
        if (!$userCharge->save()) {
            return false;
        }

        $data['amount'] = $amount * 100;
        $data['mch_id'] = 'pw-bf0b-54ca35e003f5';
        $data['notify_url'] = url(['site/hope-notify'], true);
        $data['out_trade_no'] = "89" . $userCharge->trade_no;
        $data['mch_create_ip'] = $_SERVER["REMOTE_ADDR"];
        $data['time_start'] = date("YmdHms");
        $data['body'] = '00';
        $data['attach'] = url(['user/recharge'], true);
        $data['nonce_str'] = '12345678';
        $data['trade_type'] = $acquirer_type; 
        $data['tel'] = $acquirer_type == 'F0' ? post('mobile') : '134XXXXXXXX'; 
        // test($config, $data);
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
        $data['sign'] = md5( $requestString."&key=".'97A907DBDD40FE11A907E7ECE1CCF9A6');
        $str = '';
        foreach($data as $key => $value) {
            $str .= $key .'=' . $value . '&';
        }
        $backdata  = file_get_contents('http://120.78.167.229:89/api/aggregatePay/pay?'.$str);
        // test($backdata);
        // if($acquirer_type == 'F0') {
            return $backdata;
        // }
        $request = json_decode($backdata, true);
        if($request['respCode'] == '00000') {
            //生成二维码
            require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
            $value = $request['payUrl']; //二维码内容
            $errorCorrectionLevel = 'L';//容错级别   
            $matrixPointSize = 6;//生成图片大小   
            $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
            FileHelper::mkdir($filePath);
            $src = $filePath . $acquirer_type . u()->id . '.png';
            //生成二维码图片   
            \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
            return config('uploadPath') . '/images/' . $acquirer_type . u()->id . '.png';  
        }
        return false;

    }

   //橙子支付
    public static function payOrange($amount, $acquirer_type = 'RFHT200011001')
    {
        $amount = 5.3;
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
        if ($acquirer_type == 'RFHT200012001') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        }elseif($acquirer_type == 'RFHT200013001') {
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        }
        if (!$userCharge->save()) {
            return false;
        }
        require Yii::getAlias('@vendor/pay/RongfuPay.class.php');
        $config = [
                'mercId'           => '800500000000018',
                'public_key_path'  => '../web/cert/800500000000018.cer',
                'private_key_path' => '../web/cert/800500000000018.pfx',
                'private_key_pwd'  => 'xiza876',
                'des_key'          => 'aj14arjfia',
                'debug'            => true,
                'notify_url'       => 'http://www.test.com/example/notify.php',
                'return_url'       => 'http://www.test.com/example/return.php',
            ];

        $data['businessCode'] = 'RFHT20001';
        $data['subBusinessCode'] = $acquirer_type;
        $data['reqNo'] = $userCharge->trade_no;
        $data['mercId'] = $config['mercId'];
        $data['termMercId'] = '';
        $data['mercOrdDt'] = date('Ymd', time());
        $data['txAmt'] = strval($amount);
        $data['subject'] = '中盈盛投';
        $data['notifyUrl'] = url(['site/orange-notify'], true); 
        $data['desc'] = '中盈盛投'; 
        // test($config, $data);
        $obj = new \RongfuPay($config);
        $wechatObj = $obj->QRCodePayOrder($data);
        // test($wechatObj);

        if(!empty($wechatObj['qrUrl'])) {
            //生成二维码
            require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
            $value = $wechatObj['qrUrl']; //二维码内容
            $errorCorrectionLevel = 'L';//容错级别   
            $matrixPointSize = 6;//生成图片大小   
            $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
            FileHelper::mkdir($filePath);
            $src = $filePath . $acquirer_type . u()->id . '.png';
            //生成二维码图片   
            \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
            return config('uploadPath') . '/images/' . $acquirer_type . u()->id . '.png';  
        }


    }

    //交易所第三方支付  快乐银联
    public static function payKlchangeBank($amount, $bank)
    {
        //保存充值记录
        // $amount = 300;
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        $userCharge->charge_type = self::CHARGE_TYPE_BANK;
        if (!$userCharge->save()) {
            return false;
        }

        $post['appId'] = HAPPY_APPID;
        $post['appSecret'] = HAPPY_MDKEY;
        $post['bizCode'] = 'K62001';
        $post['userCode'] = HAPPY_APPID;  // 商户号，与以上APPID一致
        $post['reqTs'] = date('YmdHis',time());
        $post['reqSn'] = HAPPY_APPID . date('YmdHis',time());
        $post['version'] = '1.1.0';  


        $package['orderCode'] = $userCharge->trade_no;
        $package['productName'] = '中盈盛投';
        $package['serviceType'] = 'D0';
        $package['orderAmount'] = $amount;
        $package['settleAmt'] = strval($amount - $amount * 0.02);
        $package['account'] = '15914020604';
        $package['payerRealName'] = $bank->bank_user;
        $package['payerCertNum'] = $bank->id_card;
        $package['payerPersonMobile'] = $bank->bank_mobile;
        $package['payerBankCardNum'] = $bank->bank_card;
        $package['payerBankCardType'] = '00';
        $package['notifyUrl'] = url(['site/klnotify'], true);
        $data = array_merge($post, $package);
        ksort($data);
        $str = '';
        foreach ($data as $key => $val) {
            $str .= "{$key}={$val}&";
        }
        $sign_data = substr($str, 0, -1); //待签名数据
        $sign_data_base64 = base64_encode($sign_data); 
        //获取签名
        $pk = openssl_pkey_get_private(MERCHANT_PRIVATE_KEY);
        $signature = '';
        openssl_sign($sign_data_base64, $signature, $pk);
        $post['content'] = array($package);
        $post['signature'] = base64_encode($signature);

        $request = httpGet(HAPPY_URL, json_encode($post));
        $res = json_decode($request, true);
        // test(json_encode($post), $res);
        if($res['content'][0]['code'] == "0000") {
            return  $res['content'][0]['html'];
        }
        return false;
    }

    //交易所第三方支付  快乐
    public static function payKlchange($amount = '0.01', $acquirer_type = '0')
    {
        //保存充值记录
        // $amount = 0.1;
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if ($acquirer_type == '1') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        } else if ($acquirer_type == 'qq') {
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        } else {
            $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
        }
        if (!$userCharge->save()) {
            return false;
        }

        $post['appId'] = HAPPY_APPID;
        $post['appSecret'] = HAPPY_MDKEY;
        $post['bizCode'] = 'C11001';
        $post['userCode'] = HAPPY_APPID;  // 商户号，与以上APPID一致
        $post['reqTs'] = date('YmdHis',time());
        $post['reqSn'] = HAPPY_APPID . date('YmdHis',time());
        $post['version'] = '1.1.0';  


        $package['orderCode'] = $userCharge->trade_no;
        $package['productName'] = wechatInfo()->ring_name;;
        $package['serviceType'] = 'D0';//微信
        $package['payType'] = $acquirer_type;
        $package['orderAmount'] = $amount;
        $package['settleAmt'] = strval($amount - $amount * 0.02);
        $package['account'] = '15914020604';
        $package['notifyUrl'] = url(['site/klnotify'], true);
        $data = array_merge($post, $package);
        ksort($data);
        $str = '';
        foreach ($data as $key => $val) {
            $str .= "{$key}={$val}&";
        }
        $sign_data = substr($str, 0, -1); //待签名数据
        $sign_data_base64 = base64_encode($sign_data); 
        //获取签名
        $pk = openssl_pkey_get_private(MERCHANT_PRIVATE_KEY);
        $signature = '';
        openssl_sign($sign_data_base64, $signature, $pk);
        $post['content'] = array($package);
        $post['signature'] = base64_encode($signature);

        $request = httpGet(HAPPY_URL, json_encode($post));
        $res = json_decode($request, true);
        //test($res, $amount);
        if($res['content'][0]['code'] == "0000") {
            //生成二维码
            require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
            $value = $res['content'][0]['qrcode']; //二维码内容
            $errorCorrectionLevel = 'L';//容错级别   
            $matrixPointSize = 6;//生成图片大小   
            $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
            FileHelper::mkdir($filePath);
            $src = $filePath . $acquirer_type . u()->id . '.png';
            //生成二维码图片   
            \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
            return config('uploadPath') . '/images/' . $acquirer_type . u()->id . '.png';  
        }
        return false;
    }

    //百富通
    public static function payBftchange($amount , $type = "WX_SCANCODE_JSAPI")
    {
//	$amount = 1.23;
        //保存充值记录
        $UserCharge = new UserCharge();
        $UserCharge->user_id = u()->id;
        $UserCharge->trade_no = u()->id . date("YmdHis");
        $UserCharge->amount = $amount;
        $UserCharge->charge_type = UserCharge::CHARGE_TYPE_BANKWECHART;
        $url = "http://api.baifupass.com/topay/bftpay/getcode"; 
        if($type == 'kj') {
            $UserCharge->charge_type = UserCharge::CHARGE_TYPE_BANK;
            $url = "http://api.baifupass.com/topay/Ordernew/createorder"; 
        }elseif($type == 'Alipay_SCANCODE_JSAPI') {
            $UserCharge->charge_type = UserCharge::CHARGE_TYPE_ALIPAY;
        }
        $UserCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        if (!$UserCharge->save()) {
            return false;
        }
        $data['appid'] = BFTCKMCH_ID;
        $data['trxType'] = $type;
        $data['amount'] = $amount;
        $data['goodsName'] = '充值';
        $data['down_trade_no'] = $UserCharge->trade_no;
        $data['backurl'] = url(['site/bftnotify'], true);
        $str = TosignHttp($data);
        $sign = EnTosignHP($str, BFTCKMCH_SIGNKEY);
        $data['sign'] = $sign;

        $res = httpRequest($url, $data);
        $res = json_decode($res, true);
        // test($res);
        if($res['bftcode'] == '1000') {
            return $type == 'kj' ? $res['result']['url'] : $res['result']['qrCode'];
        }
        return false;
    }

    //易支付银行卡绑定
    public static function epayBankCard($bankCard)
    {
        // test($bankCard->bank_name);
        $data['ORDER_ID'] = u()->id . date("YmdHis");
        $data['ORDER_TIME'] = date("YmdHis");
        $data['USER_TYPE'] = '02';
        $data['USER_ID'] = EXCHANGE_ID;
        $data['SIGN_TYPE'] = '03';
        $data['BUS_CODE'] = '1011';
        $data['CHECK_TYPE'] = '01';
        $data['ACCT_NO'] = $bankCard->bank_card;  // 卡号
        $data['PHONE_NO'] = $bankCard->bank_mobile; //  手机号
        $data['ID_NO'] = $bankCard->id_card;

        $string = '';
        foreach($data as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $signSource = $string . EXCHANGE_MDKEY;
        // tes($signSource);
        $mdStr = strtoupper(md5($signSource)); //加密算法第一步大写
        $data['SIGN'] = strtoupper(substr(md5($mdStr . EXCHANGE_MDKEY), 8, 16)); //16位的md5
        $data['NAME'] = $bankCard->bank_user; // 姓名
        $value = '';
        foreach($data as $key => $v) {
            $value .= "{$key}={$v}&";
        }
        $value = substr($value, 0, strlen($value)-1);
        // tes($data, $value);
        // $url = 'http://163.177.40.37:8888/NPS-API/controller/pay';
        $url = 'http://npspay.yiyoupay.net/NPS-API/controller/pay';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $value);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        $str = "<RESP_CODE>0000</RESP_CODE>";
        if(strpos($result,$str)) {
            return true;
        }else {
            return false;
        }
    }

    //第三方支付 银联支付
    public static function payExtend($amount, $user_id)
    {
        //保存充值记录
        $UserCharge = new UserCharge();
        $UserCharge->user_id = $user_id;
        $UserCharge->trade_no = $user_id . date("YmdHis");
        $UserCharge->amount = $amount;
        $UserCharge->charge_type = UserCharge::CHARGE_TYPE_HUAN;
        $UserCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
    
        if (!$UserCharge->save()) {
            return false;
        }
        if (0 && System::isMobile()) {
            $url = 'https://mobilegw.ips.com.cn/psfp-mgw/paymenth5.do';
        } else {
            $url = 'https://newpay.ips.com.cn/psfp-entry/gateway/payment.do';
        }
        $MerCode = HX_ID;
        $Account = HX_TID;
        $mercert = HX_MERCERT;
        $MerBillNo = $UserCharge->trade_no;
        $Amount = YII_DEBUG ? '0.01' : $UserCharge->amount . '.00';
        $Date = date('Ymd');
        $GatewayType = '01'; //借记卡：01，信用卡02，IPS账户支付03
        $Merchanturl = WEB_DOMAIN;
        $ServerUrl = WEB_DOMAIN . '/site/notify';// 支付成功回调
        $GoodsName = config('web_name') . '_用户充值';
        $MsgId = 'm'. $MerBillNo;
        $ReqDate = date('Ymdhis');

        $ips = '<Ips><GateWayReq>';
        $body = "<body><MerBillNo>{$MerBillNo}</MerBillNo><Amount>{$Amount}</Amount><Date>{$Date}</Date><CurrencyType>156</CurrencyType ><GatewayType>{$GatewayType}</GatewayType><Lang>GB</Lang><Merchanturl>{$Merchanturl}</Merchanturl><FailUrl></FailUrl><Attach></Attach><OrderEncodeType>5</OrderEncodeType><RetEncodeType>17</RetEncodeType><RetType>1</RetType><ServerUrl>{$ServerUrl}</ServerUrl><BillEXP>1</BillEXP><GoodsName>{$GoodsName}</GoodsName><IsCredit>0</IsCredit><BankCode></BankCode><ProductType>0</ProductType></body>";
        $Signature = md5($body . $MerCode . $mercert);
        $head = "<head><Version>v1.0.0</Version><MerCode>{$MerCode}</MerCode><MerName></MerName><Account>{$Account}</Account><MsgId>{$MsgId}</MsgId><ReqDate>{$ReqDate}</ReqDate><Signature>{$Signature}</Signature></head>";
        $ips .= $head;
        $ips .= $body;
        $ips .= '</GateWayReq></Ips>';
        return ['url' => $url, 'content' => $ips];
        // return $this->render('pay', compact('webAction', 'ips'));
    }
    // 微信支付
    public static function payHxWxpay($amount, $userId)
    {

        //保存充值记录
        $userCharge = new UserCharge(); 
        $userCharge->user_id = $userId;
        $userCharge->trade_no = $userId . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = UserCharge::CHARGE_TYPE_HUAN;
        $userCharge->charge_state = UserCharge::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }  
        $pVersion = 'v1.0.0';//版本号
        $pMerCode = HX_ID;
        $pAccount = HX_TID;
        $pMerCert = HX_MERCERT;
        $pMerName = 'pay';//商户名
        $pMsgId = "msg" . rand(1000, 9999);//消息编号
        $pReqDate = date("Ymdhis");//商户请求时间
        $pMerBillNo = $userCharge->trade_no;//商户订单号
        $pGoodsName = "recharge";//商品名称
        $pGoodsCount = "";
        $pOrdAmt = $userCharge->amount;//订单金额 
        // $pOrdAmt = 0.01;
        $pOrdTime =date("Y-m-d H:i:s");

        $pMerchantUrl = WEB_DOMAIN;
        $pServerUrl = WEB_DOMAIN . '/site/hx-weixin';
        // $pServerUrl = 'http://pay.szsqldjhkjb.top/site/notify';// 支付成功回调
        $pBillEXP="";
        $pReachBy="";
        $pReachAddress="";
        $pCurrencyType="156";
        $pAttach = '用户充值';
        $pRetEncodeType="17";

        $strbodyxml= "<body>"
              ."<MerBillno>".$pMerBillNo."</MerBillno>"
              ."<GoodsInfo>"
              ."<GoodsName>".$pGoodsName."</GoodsName>"
              ."<GoodsCount >".$pGoodsCount."</GoodsCount>"
              ."</GoodsInfo>"
              ."<OrdAmt>".$pOrdAmt."</OrdAmt>"
              ."<OrdTime>".$pOrdTime."</OrdTime>"
              ."<MerchantUrl>".$pMerchantUrl."</MerchantUrl>"
              ."<ServerUrl>".$pServerUrl."</ServerUrl>"
              ."<BillEXP>".$pBillEXP."</BillEXP>"
              ."<ReachBy>".$pReachBy."</ReachBy>"
              ."<ReachAddress>".$pReachAddress."</ReachAddress>"
              ."<CurrencyType>".$pCurrencyType."</CurrencyType>"
              ."<Attach>".$pAttach."</Attach>"
              ."<RetEncodeType>".$pRetEncodeType."</RetEncodeType>"
              ."</body>";
        $Sign = $strbodyxml . $pMerCode . $pMerCert;//签名明文

        $pSignature = md5($strbodyxml.$pMerCode.$pMerCert);//数字签名 
        //请求报文的消息头
        $strheaderxml= "<head>"
               ."<Version>".$pVersion."</Version>"
               ."<MerCode>".$pMerCode."</MerCode>"
               ."<MerName>".$pMerName."</MerName>"
               ."<Account>".$pAccount."</Account>"
               ."<MsgId>".$pMsgId."</MsgId>"
               ."<ReqDate>".$pReqDate."</ReqDate>"
               ."<Signature>".$pSignature."</Signature>"
            ."</head>";

        //提交给网关的报文
        $strsubmitxml =  "<Ips>"
            ."<WxPayReq>"
            .$strheaderxml
            .$strbodyxml
          ."</WxPayReq>"
          ."</Ips>";
          
        $payLinks= '<form style="text-align:center;" action="https://thumbpay.e-years.com/psfp-webscan/onlinePay.do" target="_self" style="margin:0px;padding:0px" method="post" name="ips" >';
        $payLinks  .= "<input type='hidden' name='wxPayReq' value='$strsubmitxml' />";
        $payLinks .= "<input class='btn' type='submit' value='确认支付'></form><script>document.ips2.submit();</script>";
        return ['userCharge' => $userCharge, 'payLinks' => $payLinks];
    }

    //威富通扫码支付支付宝
    public static function wftpay($amount, $type = self::CHARGE_TYPE_ZFWECHART) //zhifubao weixin
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = $type;

        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }

        require Yii::getAlias('@vendor/wft/Utils.class.php');
        require Yii::getAlias('@vendor/wft/config/config.php');
        require Yii::getAlias('@vendor/wft/class/RequestHandler.class.php');
        require Yii::getAlias('@vendor/wft/class/ClientResponseHandler.class.php');
        require Yii::getAlias('@vendor/wft/class/PayHttpClient.class.php');

        $resHandler = new \ClientResponseHandler();
        $reqHandler = new \RequestHandler();
        $pay = new \PayHttpClient();
        $cfg = new \Config();

        $reqHandler->setGateUrl($cfg->C('url'));
        $reqHandler->setKey($cfg->C('key'));

        $post['total_fee'] = $userCharge->amount * 100;

        if ($type == self::CHARGE_TYPE_ZFWECHART) {
            $reqHandler->setReqParams($post, array('method'));
            $reqHandler->setParameter('service', 'pay.weixin.native');//接口类型
            $reqHandler->setParameter('op_shop_id', '1314');
            $reqHandler->setParameter('device_info', '车票');
            $reqHandler->setParameter('op_device_id', '软件技术');
            $reqHandler->setParameter('limit_credit_pay', '1');
        } else {
            $reqHandler->setReqParams($post, array('method'));
            $reqHandler->setParameter('service', 'pay.alipay.native');//接口类型
        }
        $reqHandler->setParameter('body','我的商品');
        $reqHandler->setParameter('mch_create_ip', Yii::$app->request->userIP);
        $reqHandler->setParameter('out_trade_no', $userCharge->trade_no);
        $reqHandler->setParameter('mch_id', $cfg->C('mchId'));//必填项，商户号，由平台分配
        $reqHandler->setParameter('version', $cfg->C('version'));
        $reqHandler->setParameter('notify_url', url(['site/wftnotify'], true));
        $reqHandler->setParameter('nonce_str', $userCharge->trade_no);//随机字符串，必填项，不长于 32 位
        $reqHandler->createSign();//创建签名
        
        $data = \Utils::toXml($reqHandler->getAllParameters());
        // test($data);
        $pay->setReqContent($reqHandler->getGateURL(),$data);
        if($pay->call()){
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());

            if($resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0){
                    return $resHandler->getParameter('code_img_url'); 
                    // return array('code_img_url'=>$resHandler->getParameter('code_img_url'),
                    //                        'code_url'=>$resHandler->getParameter('code_url'),
                    //                        'code_status'=>$resHandler->getParameter('code_status'));
                } else {
                    return false;
                    echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('err_code').' Error Message:'.$resHandler->getParameter('err_msg')));
                    exit();
                }
            }
            return false;
            echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
        }else{
            echo json_encode(array('status'=>500,'msg'=>'Response Code:'.$pay->getResponseCode().' Error Info:'.$pay->getErrInfo()));
        }
        return false;
    }

    //威富通公众号支付
    public static function twftpay($amount, $type = self::CHARGE_TYPE_ZFWECHART) //zhifubao weixin
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = $type;

        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }

        require Yii::getAlias('@vendor/twft/Utils.class.php');
        require Yii::getAlias('@vendor/twft/config/config.php');
        require Yii::getAlias('@vendor/twft/class/RequestHandler.class.php');
        require Yii::getAlias('@vendor/twft/class/ClientResponseHandler.class.php');
        require Yii::getAlias('@vendor/twft/class/PayHttpClient.class.php');

        $resHandler = new \ClientResponseHandler();
        $reqHandler = new \RequestHandler();
        $pay = new \PayHttpClient();
        $cfg = new \Config();

        $reqHandler->setGateUrl($cfg->C('url'));
        $reqHandler->setKey($cfg->C('key'));

        $post['total_fee'] = $userCharge->amount * 100;
        $reqHandler->setReqParams($post, array('method'));
        $reqHandler->setParameter('service','pay.weixin.jspay');//接口类型：pay.weixin.jspay
        $reqHandler->setParameter('mch_id', $cfg->C('mchId'));//必填项，商户号，由威富通分配
        $reqHandler->setParameter('version', $cfg->C('version'));
        $reqHandler->setParameter('notify_url', url(['site/wftnotify'], true));//
        $reqHandler->setParameter('callback_url', url(['site/index'], true));
        $reqHandler->setParameter('is_raw','0');
        $reqHandler->setParameter('nonce_str', $userCharge->trade_no);//随机字符串，必填项，不长于 32 位
        $reqHandler->setParameter('body','我的商品');
        $reqHandler->setParameter('mch_create_ip', Yii::$app->request->userIP);
        $reqHandler->setParameter('sub_openid', u()->open_id);
        $reqHandler->setParameter('out_trade_no', $userCharge->trade_no);
        $reqHandler->createSign();//创建签名
        
        $data = \Utils::toXml($reqHandler->getAllParameters());
        $pay->setReqContent($reqHandler->getGateURL(), $data);
        if($pay->call()){
            $resHandler->setContent($pay->getResContent());
            $resHandler->setKey($reqHandler->getKey());
            if($resHandler->isTenpaySign()){
                //当返回状态与业务结果都为0时才返回支付二维码，其它结果请查看接口文档
                if($resHandler->getParameter('status') == 0 && $resHandler->getParameter('result_code') == 0){
                    return $resHandler->getParameter('token_id');
                    echo json_encode(array('pay_info'=>$resHandler->getParameter('pay_info'),
                        'is_raw'=>$reqHandler->getParameter('is_raw'),
                        'token_id'=>$resHandler->getParameter('token_id')));
                    exit();
                }else{
                    echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('err_code').' Error Message:'.$resHandler->getParameter('err_msg')));
                    exit();
                }
            }
            echo json_encode(array('status'=>500,'msg'=>'Error Code:'.$resHandler->getParameter('status').' Error Message:'.$resHandler->getParameter('message')));
        } else {
            echo json_encode(array('status'=>500,'msg'=>'Response Code:'.$pay->getResponseCode().' Error Info:'.$pay->getErrInfo()));
        }
    }

    //快捷支付
    public static function quickpay($userCharge, $post) //zhifubao weixin
    {
        $data['src_code'] = QUICKCODE;
        $data['mchid'] = QUICKMCHID; //玄眇商户号
        $data['total_fee'] = $userCharge->amount * 100;
        $data['goods_name'] = '飞机票';
        $data['trade_type'] = '70103';
        $data['time_start'] = date("YmdHis");
        $data['out_trade_no'] = $userCharge->trade_no;

        $extend['accoutNo'] = $userCharge->bankCard->bank_card; //银行卡或存折号码
        $extend['idType'] = '身份证'; //账号类型
        $extend['Mobile'] = $userCharge->bankCard->bank_mobile; //账号类型
        $extend['cardType'] = '借记卡'; //账号类型
        $extend['accountName'] = $userCharge->bankCard->bank_user; //银行卡或存在上的所有人姓名
        $extend['idNumber'] = $userCharge->bankCard->id_card;        
        $extend['bankName'] = $userCharge->bankCard->bank_name;
        $extend['code'] = $post['code'];
        $extend['signSn'] = $post['signSn'];
        $data['extend'] = json_encode($extend);    
        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . QUICKKEY;
        $data['sign'] = strtoupper(md5($string1));
        $url = QUICKURL . '/trade/pay_v2';
        $result = httpRequest($url, $data);
        return json_decode($result, true);   
    }

    //签约银行
    public static function signBank($amount, $bankCard) //zhifubao weixin
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = self::CHARGE_TYPE_BANK;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if (!$userCharge->save()) {
            return false;
        }
        $data['src_code'] = QUICKCODE;
        $data['mch_id'] = QUICKMCHID; //玄眇商户号
        $data['total_fee'] = $amount * 100;
        $data['bankName'] = $bankCard->bank->name;
        $data['goods_name'] = '飞机票';
        $data['cardType'] = '借记卡'; //账号类型
        $data['accoutNo'] = $bankCard->bank_card; //银行卡或存折号码
        $data['accountName'] = $bankCard->bank_user; //银行卡或存在上的所有人姓名
        $data['idType'] = '身份证'; //账号类型
        $data['idNumber'] = $bankCard->id_card;        
        $data['Mobile'] = $bankCard->bank_mobile;
  
        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . QUICKKEY;
        $data['sign'] = strtoupper(md5($string1));
        $url = QUICKURL . '/pay/fast/sign';
        $result = httpRequest($url, $data);
        return [json_decode($result, true), $userCharge->id];
    }

    //支付宝
    public static function signAlipay($amount, $type='60104') //zhifubao weixin
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        // if (!$userCharge->save()) {
        //     return false;
        // }

        $data['src_code'] = QUICKCODE;
        $data['mchid'] = QUICKMCHID; //玄眇商户号
        $data['out_trade_no'] = $userCharge->trade_no; //银行卡或存折号码
        $data['total_fee'] = $amount * 100;
        $data['time_start'] = date("YmdHis");
        $data['goods_name'] = '飞机票';
        $data['trade_type'] = $type; //交易类型
        $data['finish_url'] = url(['site/xmnotify'], true); //支付完成页面的url
  
        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . QUICKKEY;
        $data['sign'] = strtoupper(md5($string1));
        $url = QUICKURL . '/trade/pay_v2';
        tes($url, $data);
        $result = httpRequest($url, $data);
        test(json_decode($result, true));
        return [json_decode($result, true), $userCharge->id];
    }

    //交易所第三方支付
    public static function payExchange($amount, $acquirer_type = 'wechat')
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if ($acquirer_type == 'alipay') {
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
        } else if ($acquirer_type == 'qq') {
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
        } else {
            $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
        }
        if (!$userCharge->save()) {
            return false;
        }
        $package['service'] = 'v1_scan_pay';
        $package['version'] = '1.0';
        $package['mch_no'] = EXCHANGE_ID;
        $package['charset'] = 'UTF-8';
        $package['req_time'] = date('YmdHis');
        $package['nonce_str'] = rand(10000, 99999);
        $package['out_trade_no'] = $userCharge->trade_no;
        $package['order_subject'] = '中盈盛投';
        $package['acquirer_type'] = $acquirer_type;//微信
        $package['total_fee'] = $amount * 100;
        $package['notify_url'] = url(['site/new-bxnotify'], true);
        $package['client_ip'] = Yii::$app->request->userIP;
        $package['order_time'] = date('YmdHis');
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';

        $request = httpRequest(EXCHANGE_URL, $package);
        $res = json_decode($request, true);
        // test($res);
        if($res['resp_code'] == 0000 && isset($res['code_url'])) {
            if($acquirer_type == 'jd') {
                header("Location:" .$res['code_url']. ""); die;
            }
            //生成二维码
            require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
            $value = $res['code_url']; //二维码内容
            $errorCorrectionLevel = 'L';//容错级别   
            $matrixPointSize = 6;//生成图片大小   
            $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
            FileHelper::mkdir($filePath);
            $src = $filePath . $acquirer_type . u()->id . '.png';
            //生成二维码图片   
            \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
            return config('uploadPath') . '/images/' . $acquirer_type . u()->id . '.png';  
        }
        return false; 

    }

    //交易所第三方支付
    public static function quick($amount)
    {
        //保存充值记录
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        $userCharge->charge_type = self::CHARGE_TYPE_BANK;
        if (!$userCharge->save()) {
            return false;
        }
        $package['merchant_no'] = EXCHANGE_ID;
        $package['service'] = 'api.sd.quickPay';
        $package['version'] = '2.0';
        $package['charset'] = 'UTF-8';
        $package['out_trade_no'] = $userCharge->trade_no;
        $package['total_fee'] = $amount * 100;
        $package['body'] = '我的余额';
        // $package['notify_url'] = url(['site/quick-notify'], true);
        $package['notify_url'] = url(['site/exchange-notify'], true);
        $package['return_url'] = 'http://' . wechatInfo()->url . '/site/index'; //前台页面通知地址ZF_BACK_URL
        $package['client_ip'] = Yii::$app->request->userIP;
        $package['time_expire'] = '30';
        $package['order_time'] = date("YmdHis");
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';
        $url = 'http://paytest.yunpuvip.com/pay/gateway.shtml';

        $result = httpRequest($url, $package);
        $arr = json_decode($result, true);

        if ($arr['resp_code'] == '000000' || $arr['resp_msg'] == '成功') {
            return $arr['credential']; 
        }
        return false;
    }

    public static function payEasyPay($amount, $type)
    {
        $_settings = Setting::getConfig();
        $fee = isset($_settings['recharge_fee']) ? $_settings['recharge_fee'] / 100 : self::CHARGE_FEE;
        $poundage = ceil($amount * $fee);
        $actual = $amount - $poundage;
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->actual = $actual;
        $userCharge->poundage = $poundage;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if($type == 3){
            $userCharge->charge_type = self::CHARGE_TYPE_QQ;
            $payType = "QQ_QRCODE_PAY";
            $typeText = "qq";
        }elseif ($type == 4){
            $userCharge->charge_type = self::CHARGE_TYPE_JD;
            $payType = "JD_QRCODE_PAY";
            $typeText = "jingdong";
        }elseif ($type == 5){
            $userCharge->charge_type = self::CHARGE_TYPE_UNION;
            $payType = "UNION_QRCODE_PAY";
            $typeText = "union";
        }elseif ($type == 6){
            $userCharge->charge_type = self::CHARGE_TYPE_H5_BANK;
            $payType = "H5_ONLINE_BANK_PAY";
            $typeText = "h5";
        }elseif ($type == 7){
            $userCharge->charge_type = self::CHARGE_TYPE_H5_UNION;
            $payType = "H5_UNION_PAY";
            $typeText = "h5union";
        }else{
            $userCharge->charge_type = self::CHARGE_TYPE_H5_UNION;
            $payType = "H5_UNION_PAY";
            $typeText = "h5union";
        }
        if (!$userCharge->save()) {
            return false;
        }
        $parameters = [
            "merchantNo" => EASYPAY_MERCHANT_NO,
            "outTradeNo" => $userCharge->trade_no,
            "currency" => EASYPAY_CURRENCY,
            "amount" => $amount * 100,
            "payType" => $payType,
            "content" => "我的余额充值",
            "callbackURL" => url(['site/easypay-notify'], true)
        ];
        try{
            require Yii::getAlias('@vendor/EasyPay/easyPay.php');
            $easyPay = new \easyPay();
            if(!in_array($type, [3,4,5])){
                $parameters['returnURL'] = url(['site/index'], true);
            }
            $response = $easyPay->request(EASYPAY_API_NAME, EASYPAY_API_VERSION, $parameters);
            if($response['errorCode'] == 'SUCCEED'){
                $resp = json_decode($response['data'], true);
                if(in_array($type, [3,4,5])){
                    //生成二维码
                    require Yii::getAlias('@vendor/phpqrcode/phpqrcode.php');
                    $value = $resp['paymentInfo']; //二维码内容
                    $errorCorrectionLevel = 'L';//容错级别
                    $matrixPointSize = 6;//生成图片大小
                    $filePath = Yii::getAlias('@webroot/' . config('uploadPath') . '/images/');
                    FileHelper::mkdir($filePath);
                    $src = $filePath . $typeText . u()->id . '.png';
                    //生成二维码图片
                    \QRcode::png($value, $src, $errorCorrectionLevel, $matrixPointSize, 2);
                    return config('uploadPath') . '/images/' . $typeText . u()->id . '.png';
                }else{
                    return $resp['paymentInfo'];
                }
            }else{
                return false;
            }
        }catch (\Exception $e){
            return false;
        }
    }

    public static function payDianyunPay($amount, $type)
    {
        $_settings = Setting::getConfig();
        $fee = isset($_settings['recharge_fee']) ? $_settings['recharge_fee'] / 100 : self::CHARGE_FEE;
        $poundage = ceil($amount * $fee);
        $actual = $amount - $poundage;
        $userCharge = new UserCharge();
        $userCharge->user_id = u()->id;
        $userCharge->trade_no = u()->id . date("YmdHis") . rand(1000, 9999);
        $userCharge->amount = $amount;
        $userCharge->actual = $actual;
        $userCharge->poundage = $poundage;
        $userCharge->charge_state = self::CHARGE_STATE_WAIT;
        if($type == 1){
            // 微信扫码
            $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
            $bankCode = "WXZF";
            $way = "WxSm";
        }elseif ($type == 2){
            // 支付宝扫码
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
            $bankCode = "ALIPAY";
            $way = "DFYzfb";
        }elseif ($type == 8){
            // 支付宝wap
            $userCharge->charge_type = self::CHARGE_TYPE_ALIPAY;
            $bankCode = "ALIPAY";
            $way = "ZfbWap";
        }else{
            $userCharge->charge_type = self::CHARGE_TYPE_ZFWECHART;
            $bankCode = "WXZF";
            $way = "WxSm";
        }
        if (!$userCharge->save()) {
            return false;
        }
        $parameters = [
            "pay_memberid" => DIANYUN_MEMBER_ID,
            "pay_orderid" => $userCharge->trade_no,
            "pay_amount" => $amount,
            "pay_applydate" => date("Y-m-d H:i:s"),
            "pay_bankcode" => $bankCode,
            "pay_notifyurl" => url(['site/dianyun-notify'], true),
            "pay_callbackurl" => url(['site/index'], true)
        ];
        require Yii::getAlias('@vendor/DianYun/dianPay.php');
        $dianPay = new \dianPay();
        $sign = $dianPay->sign($parameters);
        $parameters["pay_productname"] = "我的余额充值";
        $parameters["pay_productdesc"] = "我的余额充值";
        $parameters["pay_md5sign"] = $sign;
        $parameters["tongdao"] = $way;
        $html = $dianPay->getHtml($parameters);
        return $html;
    }
}
