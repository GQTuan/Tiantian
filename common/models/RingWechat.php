<?php

namespace common\models;

use Yii;

/**
 * 这是表 `ring_wechat` 的模型
 */
class RingWechat extends \common\components\ARModel
{
    public function rules()
    {
        return [
            [['admin_id', 'ring_name', 'url', 'appid', 'appsecret'], 'required'],
            [['admin_id', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['ring_name', 'url', 'appid', 'appsecret', 'mchid', 'mchkey', 'token', 'sign_name'], 'string', 'max' => 100],
            [['username', 'password'], 'string', 'max' => 50]
        ];
    }

    public function attributeLabels()
    {
        return [
            'admin_id' => 'Admin ID',
            'ring_name' => '微圈名称',
            'url' => '域名',
            'appid' => 'Appid',
            'appsecret' => '公众号秘钥',
            'mchid' => '商户号',
            'mchkey' => '商户key值',
            'token' => 'token值',
            'sign_name' => '签名',
            'username' => '短信用户名',
            'password' => '短信密码',
            'created_at' => 'Created At',
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
    //找到经纪人
    public function getManagerUser()
    {
        return $this->hasOne(User::className(), ['member_id' => 'admin_id']);
    }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'ringWechat.admin_id' => $this->admin_id,
                'ringWechat.created_by' => $this->created_by,
                'ringWechat.updated_by' => $this->updated_by,
            ])
            ->andFilterWhere(['like', 'ringWechat.ring_name', $this->ring_name])
            ->andFilterWhere(['like', 'ringWechat.url', $this->url])
            ->andFilterWhere(['like', 'ringWechat.appid', $this->appid])
            ->andFilterWhere(['like', 'ringWechat.appsecret', $this->appsecret])
            ->andFilterWhere(['like', 'ringWechat.mchid', $this->mchid])
            ->andFilterWhere(['like', 'ringWechat.mchkey', $this->mchkey])
            ->andFilterWhere(['like', 'ringWechat.token', $this->token])
            ->andFilterWhere(['like', 'ringWechat.sign_name', $this->sign_name])
            ->andFilterWhere(['like', 'ringWechat.username', $this->username])
            ->andFilterWhere(['like', 'ringWechat.password', $this->password])
            ->andFilterWhere(['like', 'ringWechat.created_at', $this->created_at])
            ->andFilterWhere(['like', 'ringWechat.updated_at', $this->updated_at])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
}
