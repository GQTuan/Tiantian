<?php

namespace admin\models;

use Yii;
use common\helpers\Html;

class User extends \common\models\User
{
    public $out_account;
    public $ringname;
    public $membername;

    public function rules()
    {
        return array_merge(parent::rules(), [
            ['account', 'number', 'min' => '0', 'tooSmall' => '余额不足以出金！'],
            [['out_account'], 'safe'],
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
            'state' => '状态',
            // 'field2' => 'description2',
        ]);
    }

    public function getParentLink($name = 'id')
    {
        if ($this->pid) {
            return Html::a($this->parent->nickname, ['', 'search[' . $name . ']' => $this->pid], ['class' => 'parentLink']);
        } else {
            return '无';
        }
    }

    public function listQuery()
    {
        $query = $this->search()
            ->joinWith(['parent', 'admin'])
            ->andFilterWhere(['>', 'user.created_at', $this->created_at]);
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

    public function managerQuery()
    {
        return $this->search()
            ->with(['parent', 'userExtend', 'admin'])
            ->manager()
            ->andWhere(['is_manager' => User::IS_MANAGER_YES]);
    }

    public function managerListQuery()
    {
        return $this->search()
            ->joinWith(['parent', 'userExtend', 'admin'])
            ->manager()
            ->andWhere(['user.is_manager' => User::IS_MANAGER_YES]);
    }
}
