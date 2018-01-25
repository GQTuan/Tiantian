<?php

namespace common\models;

use Yii;

/**
 * 这是表 `bank` 的模型
 */
class Bank extends \common\components\ARModel
{
    public function rules()
    {
        return [
            [['number'], 'required'],
            [['number'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    public function attributeLabels()
    {
        return [
            'number' => 'Number',
            'name' => '名称',
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
                'bank.number' => $this->number,
            ])
            ->andFilterWhere(['like', 'bank.name', $this->name])
            ->andTableSearch()
        ;
    }

    /****************************** 以下为公共操作的方法 ******************************/

    

    /****************************** 以下为字段的映射方法和格式化方法 ******************************/
}
