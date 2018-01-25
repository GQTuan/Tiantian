<?php

namespace admin\controllers;

use Yii;
use admin\models\Retail;
use admin\models\AdminUser;
use common\helpers\Hui;
use common\helpers\Html;
use common\helpers\StringHelper;

class RetailController extends \admin\components\Controller
{
    /**
     * @authname 代理商列表
     */
    public function actionList()
    {
        $query = (new Retail)->search();

        $html = $query->getTable([
            'id',
            'account' => ['search' => true],
            'company_name' => ['type' => 'text', 'search' => true],
            'realname' => ['type' => 'text', 'search' => true],
            'tel' => ['type' => 'text', 'search' => true],
            'qq' => ['type' => 'text', 'search' => true],
            'point' => ['type' => 'text'],
            'total_fee',
            'id_card' => function ($row) {
                if (!$row->id_card) {
                    return '';
                }
                return Hui::primaryBtn('查看', null, ['class' => 'viewFace']) .
                       Html::a('', $row->id_card, ['class' => 'img-fancybox hidden', 'title' => $row->label('id_card'), 'rel' => 'id_card' . $row->id]);
            },
            'paper' => function ($row) {
                if (!$row->paper) {
                    return '';
                }
                return Hui::primaryBtn('查看', null, ['class' => 'viewFace']) .
                       Html::a('', $row->paper, ['class' => 'img-fancybox hidden', 'title' => $row->label('paper'), 'rel' => 'paper' . $row->id]);
            },
            'paper2' => function ($row) {
                if (!$row->paper2) {
                    return '';
                }
                return Hui::primaryBtn('查看', null, ['class' => 'viewFace']) .
                       Html::a('', $row->paper2, ['class' => 'img-fancybox hidden', 'title' => $row->label('paper2'), 'rel' => 'paper2' . $row->id]);
            },
            'paper3' => function ($row) {
                if (!$row->paper3) {
                    return '';
                }
                return Hui::primaryBtn('查看', null, ['class' => 'viewFace']) .
                       Html::a('', $row->paper3, ['class' => 'img-fancybox hidden', 'title' => $row->label('paper3'), 'rel' => 'paper3' . $row->id]);
            },
            'code',
            'created_at',
        ], [
            'addBtn' => ['saveRetail' => '添加会员单位']
        ]);

        return $this->render('list', compact('html'));
    }

    /**
     * @authname 添加/编辑会员单位
     */
    public function actionSaveRetail($id = 0)
    {
        $model = Retail::findModel($id);

        if ($model->load()) {
            $model->code = StringHelper::random(6, 'n');
            if ($model->validate()) {
                if ($model->file1) {
                    $model->file1->move();
                    $model->id_card = $model->file1->filePath;
                }
                if ($model->file2) {
                    $model->file2->move();
                    $model->paper = $model->file2->filePath;
                }
                if ($model->file3) {
                    $model->file3->move();
                    $model->paper2 = $model->file3->filePath;
                }
                if ($model->file4) {
                    $model->file4->move();
                    $model->paper3 = $model->file4->filePath;
                }
                $model->save(false);
                $admin = new AdminUser;
                $admin->username = $model->account;
                $admin->password = $model->pass;
                $admin->realname = $model->realname;
                if ($admin->saveAdmin()) {
                    $auth = Yii::$app->authManager;
                    $role = $auth->getRole('代理商管理');
                    $auth->assign($role, $admin->id);
                } else {
                    $model->delete();
                    return error($admin);
                }
                return success();
            } else {
                return error($model);
            }
        }

        return $this->render('saveRetail', compact('model'));
    }
}
