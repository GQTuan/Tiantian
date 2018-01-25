<?php

namespace admin\models;

use Yii;

class Order extends \common\models\Order
{
    public $is_profit;
    public $start_time;
    public $end_time;
    public $ringname;
    public $membername;
    
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
            'order_state' => '持仓状态',
            // 'field2' => 'description2',
        ]);
    }

    public function listQuery()
    {
        $query = $this->search()
            ->andFilterWhere(['>=', 'order.created_at', $this->start_time])
            ->andFilterWhere(['<=', 'order.created_at', $this->end_time])
            ->andFilterWhere(['order.product_id' => $this->product_name]);
        if ($this->is_profit) {
            $query->andFilterWhere([$this->is_profit, 'profit', 0]);
        }
        if ($this->ringname) {
            $query->andFilterWhere(['like', 'admin.username', $this->ringname]);
        }
        if ($this->membername) {
            $idArr = AdminUser::find()->where(['like', 'username', $this->membername])->map('id', 'id');
            if (empty($idArr)) {
                $query->andFilterWhere(['admin.id' => 0]);
            } else {
                $idArr2 = AdminUser::find()->where(['in', 'pid', $idArr])->map('id', 'id');
                $arr = $idArr;
                if (!empty($idArr2)) {
                    $arr = array_merge($idArr, $idArr2);
                }
                $query->andFilterWhere(['in', 'user.admin_id', $arr]);
            }
        }
        return $query;
    }

    public static function getIsProfitMap($prepend = false)
    {
        $map = [
            '>' => '盈利',
            '<' => '亏损'
        ];

        return self::resetMap($map, $prepend);
    }

    //微圈名下用户头寸
    public static function ringUserProfit($admin_id)
    {
        return  self::find()->joinWith(['user'])->where(['order_state' => Order::ORDER_THROW, 'admin_id' => $admin_id])->select('SUM(order.profit) profit')->one()->profit ?: 0;
    }

    public function getIsProfitValue($value = null)
    {
        return $this->resetValue($value);
    }
}
