<?php

namespace admin\models;

use Yii;

class AdminAccount extends \common\models\AdminAccount
{
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['realname', 'id_card', 'bank_name', 'bank_card', 'bank_user', 'bank_mobile', 'bank_address'], 'required', 'on' => 'with'],
        ]);
    }

    public function scenarios()
    {
        return array_merge(parent::scenarios(), [
            'with' => ['id_card', 'bank_name', 'bank_card', 'bank_user', 'bank_mobile', 'bank_address'],
        ]);
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            // 'field1' => 'description1',
            // 'field2' => 'description2',
        ]);
    }
}
