<?php

namespace common\models;

use Yii;

/**
 * 这是表 `user_withdraw` 的模型
 */
class UserWithdraw extends \common\components\ARModel
{
    const OP_STATE_WAIT = 1;
    const OP_STATE_PASS = 2;
    const OP_STATE_MID = 3;
    const OP_STATE_DENY = -1;

    const ISGIVE_YES = 1;
    const ISGIVE_NO = 2;
    public function rules()
    {
        return [
            [['out_sn', 'user_id', 'amount', 'account_id'], 'required'],
            [['user_id', 'account_id', 'op_state', 'isgive'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['out_sn'], 'string', 'max' => 25]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'out_sn' => '代付订单号',
            'user_id' => '用户ID',
            'amount' => '出金金额',
            'account_id' => '出金账号ID',
            'type' => '类型',
            'op_state' => '操作状态：1待审核，2已操作，-1不通过',
            //'isgive' => '是否返还',
            'created_at' => '申请时间',
            'updated_at' => '审核时间',
        ];
    }

    /****************************** 以下为设置关联模型的方法 ******************************/

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'userWithdraw.id' => $this->id,
                'userWithdraw.user_id' => $this->user_id,
                'userWithdraw.amount' => $this->amount,
                'userWithdraw.account_id' => $this->account_id,
                'userWithdraw.type' => $this->type,
                'userWithdraw.op_state' => $this->op_state,
                //'userWithdraw.isgive' => $this->isgive,
            ])
            ->andFilterWhere(['like', 'userWithdraw.out_sn', $this->out_sn])
            ->andFilterWhere(['like', 'userWithdraw.created_at', $this->created_at])
            ->andFilterWhere(['like', 'userWithdraw.updated_at', $this->updated_at])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    public function easyPayOutUserMoney()
    {
        $parameters = [
            "merchantNo" => EASYPAY_MERCHANT_NO,
            "outPayNo" => $this->out_sn,
            "payPassword" => EASYPAY_PAY_PASSWORD,
            "amount" => $this->amount * 100,
            "receiverBankName" => $this->user->userAccount->bank_code,
            "receiverBranchBankName" => $this->user->userAccount->bank_address,
            "receiverCardNo" => $this->user->userAccount->bank_card,
            "receiverAccountName" => $this->user->userAccount->realname,
            "callbackURL" => 'http://' . $_SERVER['HTTP_HOST'] . '/site/easypay-withdraw-notify'
        ];
        try{
            require Yii::getAlias('@vendor/EasyPay/easyPay.php');
            $easyPay = new \easyPay();
            $response = $easyPay->request(EASYPAY_PAY_API_NAME, EASYPAY_API_VERSION, $parameters);
            if($response['errorCode'] == 'SUCCEED'){
                $resp = json_decode($response['data'], true);
            }else{
                return false;
            }
        }catch (\Exception $e){
            return false;
        }
    }

    public function outUserMoney()
    {
        $data['platcode'] = OUTCODE;
        $data['out_sn'] = $this->out_sn;
        $data['account_name'] = $this->user->userAccount->realname; //银行卡或存在上的所有人姓名
        $data['account_type'] = '对私'; //账号类型
        $data['card_type'] = '储蓄卡'; //银行卡或存折号码
        $data['account_no'] = $this->user->userAccount->bank_card; //银行卡或存折号码
        $data['amt'] = $this->amount * 100;
        $data['head_bank_name'] = $this->user->userAccount->bank_code;        

        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . OUTKEY;
        $data['sign'] = strtoupper(md5($string1));
        $url = OUTURL . '/settlement';

        $result = httpRequest($url, $data);
        return json_decode($result, true);               
    }  

    public function getMoneyState($out_sn)
    {
        $package = [];
        $reqTime = date('YmdHis');
        $package['service'] = 'v2_liquidation_query';
        $package['version'] = '2.0';
        $package['charset'] = 'UTF-8';
        $package['req_time'] = $reqTime;
        if (empty($out_sn)) {
            return []; 
        }
        $package['nonce_str'] = $out_sn;
        $package['merchant_no'] = EXCHANGE_ID;
        $package['out_trade_no'] = $package['nonce_str'];
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';
        $url = 'http://liquidation.shopping98.com/v2/liquidation/gateway.shtml?'.http_build_query($package);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);//curl连接的url
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//有返回值
        curl_setopt($curl, CURLOPT_HEADER, 0);
        $result = curl_exec($curl);//执行curl
        curl_close($curl);//关闭
        return json_decode($result, true);                 
    }

    public function searchStatus()
    {
        $data['platcode'] = OUTCODE;
        $data['out_sn'] = $this->out_sn;
        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . OUTKEY;
        $data['sign'] = strtoupper(md5($string1));

        $url = OUTURL . '/settlement/query';
        $result = httpRequest($url, $data);
        // test($data, $result);
        return json_decode($result, true);        
    } 

    public function newoutUserMoney()
    {
        $package = [];
        $reqTime = date('YmdHis');
        $package['service'] = 'v1_liquidation_pay';
        $package['version'] = '1.0';
        $package['merchant_no'] = EXCHANGE_ID;
        $package['charset'] = 'UTF-8';
        $package['req_time'] = $reqTime;
        $package['nonce_str'] = rand(10000, 99999);
        if (empty($this->out_sn)) {
            return []; 
        }
        $package['out_trade_no'] = $this->out_sn;
        $package['amount'] = $this->amount * 100;
        $package['account_no'] = $this->user->userAccount->bank_card; //银行卡或存折号码
        $package['account_name'] = $this->user->userAccount->realname; //银行卡或存在上的所有人姓名
        $package['account_type'] = '00'; //账号类型
        $package['client_ip'] = Yii::$app->request->userIP;;//订单备注
        $package['id_type'] = '0';
        $package['id'] = $this->user->userAccount->id_card;
        $package['bank_code'] = $this->user->userAccount->bank_name; //银行编码
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';
        $url = 'http://scpay.shopping98.com/v1/gateway.shtml';
        // test($package);
        $request = httpRequest($url, $package);
        return json_decode($request, true);

    }   

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/

    // Map method of field `op_state`
    public static function getOpStateMap($prepend = false)
    {
        $map = [
            self::OP_STATE_WAIT => '待审核',
            self::OP_STATE_PASS => '已通过',
            self::OP_STATE_MID => '审核中',
            self::OP_STATE_DENY => '不通过',
        ];

        return self::resetMap($map, $prepend);
    }

    // Format method of field `op_state`
    public function getOpStateValue($value = null)
    {
        return $this->resetValue($value);
    }

    // Map method of field `isgive`
    public static function getIsgiveMap($prepend = false)
    {
        $map = [
            self::ISGIVE_YES => '已回款',
            self::OP_STATE_PASS => '未回款',
        ];

        return self::resetMap($map, $prepend);
    }

    // Format method of field `isgive`
    public function getIsgiveValue($value = null)
    {
        return $this->resetValue($value);
    }
}
