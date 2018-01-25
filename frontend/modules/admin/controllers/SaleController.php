<?php

namespace admin\controllers;

use Yii;
use admin\models\User;
use admin\models\AdminUser;
use admin\models\UserExtend;
use admin\models\Retail;
use admin\models\UserRebate;
use common\helpers\Hui;
use common\helpers\Html;

class SaleController extends \admin\components\Controller
{
    /**
     * @authname 经纪人列表
     */
    public function actionManagerList()
    {
        $query = (new User)->managerListQuery()->orderBy('user.total_fee DESC');

        $html = $query->getTable([
            'id',
            'userExtend.realname' => ['search' => true, 'header' => '真实姓名'],
            'nickname' => ['search' => true],
            'mobile' => ['search' => true, 'header' => '经纪人手机号'],
            // 'mobile' => ['search' => true, 'header' => '注册手机号'],
            // 'pid' => ['header' => '推荐人', 'value' => function ($row) {
            //     return $row->getParentLink();
            // }],
            'admin.username' => ['search' => true, 'header' => '微圈'],
            'userExtend.rebate_account',
            'userExtend.point' => ['header' => '返点(%)'],
            'account',
            'created_at',
            ['header' => '操作', 'width' => '80px', 'value' => function ($row) {
                return Hui::primaryBtn('修改返点', ['editPoint', 'id' => $row->id], ['class' => 'editBtn']);
            }]
        ], [
            'searchColumns' => [
                'id',
                // 'userExtend.realname' => ['header' => '真实姓名'],
                // 'userExtend.mobile' => ['header' => '经纪人手机号'],
            ]
        ]);

        return $this->render('managerList', compact('html'));
    }

    /**
     * @authname 修改经纪人返点%
     */
    public function actionEditPoint() 
    {
        $userExtend = UserExtend::findModel(get('id'));
        $retail = Retail::find()->where(['account' => u()->username])->one();
        if (empty($retail)) {
            $retail = Retail::find()->joinWith(['adminUser'])->where(['adminUser.id' => $userExtend->coding])->one();
        }
        $userExtend->point = post('point');

        if ($userExtend->point > $retail->point || is_int($userExtend->point) || $userExtend->point < 0) {
            return error('此经纪人返点不能超过上级'.$retail->point.'%(设置返点为正整数)');
        }
        if ($userExtend->validate()) {
            $userExtend->update(false);
            return success();
        } else {
            return error($userExtend);
        }
    }

    /**
     * @authname 代理商返点统计
     */
    public function actionManagerRebateList()
    {
        $query = (new UserRebate)->managerListQuery()->orderBy('userRebate.created_at DESC');
        // test($query->sql);
        $count = $query->sum('amount') ?: 0;

        $html = $query->getTable([
            'id',
            'admin.username' => ['header' => '代理商账号'],
            'user.nickname' => ['header' => '会员昵称（手机号）', 'value' => function ($row) {
                return Html::a($row->user->nickname . "({$row->user->mobile})", ['', 'search[user.id]' => $row->user->id], ['class' => 'parentLink']);
            }],
            'amount',
            'point' => function ($row) {
                return $row->point . '%';
            },
            'created_at' => '返点时间'
        ], [
            'searchColumns' => [
                'admin.username' => ['header' => '代理商账号'],
                'user.id' => ['header' => '会员ID'],
                'user.mobile' => ['header' => '会员手机'],
                'time' => 'timeRange'
            ],
            'ajaxReturn' => [
                'count' => $count
            ]
        ]);

        return $this->render('managerRebateList', compact('html', 'count'));
    }
}
