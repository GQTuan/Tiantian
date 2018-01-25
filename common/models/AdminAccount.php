<?php

namespace common\models;

use Yii;

/**
 * 这是表 `admin_account` 的模型
 */
class AdminAccount extends \common\components\ARModel
{
    public function rules()
    {
        return [
            [['admin_id', 'realname', 'id_card', 'bank_name', 'bank_card', 'bank_user', 'bank_mobile', 'bank_type', 'bank_areacode', 'province', 'city'], 'required'],
            [['admin_id'], 'integer'],
            [['created_at'], 'safe'],
            [['realname', 'id_card', 'bank_name', 'bank_card', 'bank_user', 'bank_type', 'bank_address', 'province', 'city'], 'string', 'max' => 100],
            [['bank_mobile'], 'string', 'max' => 11],
            [['bank_areacode', 'bank_code'], 'string', 'max' => 50],
            [['address'], 'string', 'max' => 150],
            [['admin_id'], 'unique']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'admin_id' => '用户ID',
            'realname' => '真实姓名',
            'id_card' => '身份证号',
            'bank_name' => '银行名称',
            'bank_card' => '银行卡号',
            'bank_user' => '持卡人姓名',
            'bank_mobile' => '银行预留手机号',
            'bank_type' => '联行号',
            'bank_areacode' => '地区码',
            'bank_code' => '银行编码',
            'bank_address' => '开户行地址',
            'province' => '省份',
            'city' => '城市',
            'address' => '地址',
            'created_at' => 'Created At',
        ];
    }

    /****************************** 以下为设置关联模型的方法 ******************************/

    // public function getRelation()
    // {
    //     return $this->hasOne(Class::className(), ['foreign_key' => 'primary_key']);
    // }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'adminAccount.id' => $this->id,
                'adminAccount.admin_id' => $this->admin_id,
            ])
            ->andFilterWhere(['like', 'adminAccount.realname', $this->realname])
            ->andFilterWhere(['like', 'adminAccount.id_card', $this->id_card])
            ->andFilterWhere(['like', 'adminAccount.bank_name', $this->bank_name])
            ->andFilterWhere(['like', 'adminAccount.bank_card', $this->bank_card])
            ->andFilterWhere(['like', 'adminAccount.bank_user', $this->bank_user])
            ->andFilterWhere(['like', 'adminAccount.bank_mobile', $this->bank_mobile])
            ->andFilterWhere(['like', 'adminAccount.bank_type', $this->bank_type])
            ->andFilterWhere(['like', 'adminAccount.bank_areacode', $this->bank_areacode])
            ->andFilterWhere(['like', 'adminAccount.bank_code', $this->bank_code])
            ->andFilterWhere(['like', 'adminAccount.bank_address', $this->bank_address])
            ->andFilterWhere(['like', 'adminAccount.province', $this->province])
            ->andFilterWhere(['like', 'adminAccount.city', $this->city])
            ->andFilterWhere(['like', 'adminAccount.address', $this->address])
            ->andFilterWhere(['like', 'adminAccount.created_at', $this->created_at])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
        // Map method of field `op_style`
    public static function getBankNameMap($prepend = false)
    {
        if (($map = session('accountBankName')) == null) {
            $map = Bank::find()->where(['state' => self::STATE_VALID])->map('number', 'name');
            session('accountBankName', $map, 72000);
        }
        // $map = [
        //     self::BANK_NAME_ZS => '招商银行',
        //     self::BANK_NAME_GS => '中国工商银行',
        //     self::BANK_NAME_JS => '中国建设银行',
        //     self::BANK_NAME_NY => '中国农业银行',
        //     self::BANK_NAME_MS => '中国民生银行',
        //     self::BANK_NAME_XY => '兴业银行总行',
        //     self::BANK_NAME_JT => '交通银行',
        //     self::BANK_NAME_GD => '中国光大银行',
        // ];

        return self::resetMap($map, $prepend);
    }

    // Format method of field `op_style`
    public function getBankNameValue($value = null)
    {
        return $this->resetValue($value);
    }
}
