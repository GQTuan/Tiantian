<?php

namespace admin\models;

use Yii;

class UserWithdraw extends \common\models\UserWithdraw
{
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
            'op_state' => '申请状态',
            // 'field2' => 'description2',
        ]);
    }

    public function listQuery()
    {
        $query = $this->search()
            ->manager()
            ->joinWith('user.admin')
            ->andFilterWhere(['>=', 'userWithdraw.updated_at', $this->start_time])
            ->andFilterWhere(['<=', 'userWithdraw.updated_at', $this->end_time]);
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
}
