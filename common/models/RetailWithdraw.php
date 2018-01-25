<?php

namespace common\models;

use Yii;

/**
 * 这是表 `retail_withdraw` 的模型
 */
class RetailWithdraw extends \common\components\ARModel
{
    const TYPE_FEE = 1;
    const TYPE_DEPOSIT = 2;
    // const STATE_STA = 2; // 
    const STATE_WAIT = 1; // 
    const STATE_PASS = 2; // 
    const STATE_DENY = -1; // 
    const STATE_MID = 3; // 审核中

    const AISGIVE_YES = 1;
    const AISGIVE_NO = 2;

    public function rules()
    {
        return [
            [['admin_id', 'amount'], 'required'],
            [['admin_id', 'type', 'isgive', 'state', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe'],
            [['out_sn'], 'string', 'max' => 25]
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => '用户ID',
            'amount' => '金额',
            'type' => '类型：1手续费体现，2保证金充值',
            'out_sn' => '订单号',
            'isgive' => '是否返还',
            'state' => '操作状态',
            'created_at' => '创建时间',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /****************************** 以下为设置关联模型的方法 ******************************/

    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'admin_id']);
    }

    public function getUpdatedBy()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'updated_by']);
    }

    public function getAdminAccount()
    {
        return $this->hasOne(AdminAccount::className(), ['admin_id' => 'admin_id']);
    }

    public function getRetail()
    {
        return $this->hasOne(Retail::className(), ['admin_id' => 'admin_id']);
    }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'retailWithdraw.id' => $this->id,
                'retailWithdraw.admin_id' => $this->admin_id,
                'retailWithdraw.amount' => $this->amount,
                'retailWithdraw.type' => $this->type,
                'retailWithdraw.isgive' => $this->isgive,
                'retailWithdraw.state' => $this->state,
                'retailWithdraw.created_by' => $this->created_by,
                'retailWithdraw.updated_by' => $this->updated_by,
            ])
            ->andFilterWhere(['like', 'retailWithdraw.out_sn', $this->out_sn])
            ->andFilterWhere(['like', 'retailWithdraw.created_at', $this->created_at])
            ->andFilterWhere(['like', 'retailWithdraw.updated_at', $this->updated_at])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    
    public function outUserMoney()
    {
        $data['platcode'] = OUTCODE;
        $data['out_sn'] = $this->out_sn;
        $data['account_name'] = $this->adminAccount->bank_user; //银行卡或存在上的所有人姓名
        $data['account_type'] = '对私'; //账号类型
        $data['card_type'] = '储蓄卡'; //银行卡或存折号码
        $data['account_no'] = $this->adminAccount->bank_card; //银行卡或存折号码
        $data['amt'] = $this->amount * 100;
        $data['head_bank_name'] = $this->adminAccount->bank_code;        

        ksort($data, SORT_STRING);
        $string1 = '';
        foreach($data as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = $string1 . 'key=' . OUTKEY;
        $data['sign'] = strtoupper(md5($string1));
        $url =  OUTURL . '/settlement';
        
        $result = httpRequest($url, $data);
        return json_decode($result, true);               
    } 

    public function newoutAdminMoney()
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
        $package['account_no'] = $this->adminAccount->bank_card; //银行卡或存折号码
        $package['account_name'] = $this->adminAccount->bank_user; //银行卡或存在上的所有人姓名
        $package['account_type'] = '00'; //账号类型
        $package['client_ip'] = Yii::$app->request->userIP;//订单备注
        $package['id_type'] = '0';
        $package['id'] = $this->adminAccount->id_card;
        $package['bank_code'] = $this->adminAccount->bank_name; //银行编码
        ksort($package, SORT_STRING);
        $string1 = '';
        foreach($package as $key => $v) {
            $string1 .= "{$key}={$v}&";
        }
        $string1 = trim($string1, '&') . EXCHANGE_MDKEY;
        $package['sign'] = md5($string1);
        $package['sign_type'] = 'MD5';
        $url = 'http://scpay.shopping98.com/v1/gateway.shtml';
        $request = httpRequest($url, $package);
        // test($request);
        return json_decode($request, true); 

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



    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
    // Map method of field `is_manager`
    public static function getTypeMap($prepend = false)
    {
        $maps = [
            self::TYPE_FEE => '手续费',
            self::TYPE_DEPOSIT => '保证金',
        ];

        return self::resetMap($maps, $prepend);
    }

    // Format method of field `is_manager`
    public function getPoTypelue($value = null)
    {
        return $this->resetValue($value);
    }

    // Map method of field `op_state`
    public static function getStateMap($prepend = false)
    {
        $map = [
            self::STATE_WAIT => '待审核',
            self::STATE_PASS => '已通过',
            self::STATE_MID => '审核中',
            self::STATE_DENY => '不通过',
        ];

        return self::resetMap($map, $prepend);
    }

    // Format method of field `op_state`
    public function getStateValue($value = null)
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
