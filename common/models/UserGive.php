<?php

namespace common\models;

use Yii;

/**
 * 这是表 `user_give` 的模型
 */
class UserGive extends \common\components\ARModel
{
    public function rules()
    {
        return [
            [['user_id', 'amount'], 'required'],
            [['user_id', 'created_by', 'updated_by'], 'integer'],
            [['amount'], 'number'],
            [['created_at', 'updated_at'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'amount' => '金额',
            'created_at' => '赠金时间',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /****************************** 以下为设置关联模型的方法 ******************************/

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getAdminUser()
    {
        return $this->hasOne(AdminUser::className(), ['id' => 'created_by']);
    }

    /****************************** 以下为公共显示条件的方法 ******************************/

    public function search()
    {
        $this->setSearchParams();

        return self::find()
            ->filterWhere([
                'userGive.id' => $this->id,
                'userGive.user_id' => $this->user_id,
                'userGive.amount' => $this->amount,
                'userGive.created_by' => $this->created_by,
                'userGive.updated_by' => $this->updated_by,
            ])
            ->andFilterWhere(['like', 'userGive.created_at', $this->created_at])
            ->andFilterWhere(['like', 'userGive.updated_at', $this->updated_at])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
}
