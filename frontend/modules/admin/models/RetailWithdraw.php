<?php

namespace admin\models;

use Yii;

class RetailWithdraw extends \common\models\RetailWithdraw
{
    public $start_time;
    public $end_time;
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['amount'], 'required', 'on' => 'retailWith'],
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'retailWith' => ['amount'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'field1' => 'description1',
            // 'field2' => 'description2',
        ]);
    }
    public function listQuery()
    {
        return $this->search()
	//    ->where(['type' => RetailWithdraw::TYPE_FEE])
            ->joinWith(['adminAccount', 'adminUser', 'retail'])
            ->andFilterWhere(['>=', 'retailWithdraw.updated_at', $this->start_time])
            ->andFilterWhere(['<=', 'retailWithdraw.updated_at', $this->end_time]);
    }
}
