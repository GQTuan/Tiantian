<?php

namespace admin\controllers;

use Yii;
use admin\models\Article;
use common\helpers\Hui;
use common\helpers\Html;

class ArticleController extends \admin\components\Controller
{
    /**
     * @authname 资讯列表
     */
    public function actionList()
    {
        $query = Article::find()->orderBy('id DESC');

        $html = $query->getTable([
            'id',
            'title',
            'content',
            'publish_time',
            ['type' => ['edit' => 'saveArticle', 'delete' => 'deleteArticle']]
        ], [
            'addBtn' => ['saveArticle' => '添加新闻']
        ]);

        return $this->render('list', compact('html'));
    }

    /**
     * @authname 系统公告
     */
    public function actionNotice()
    {
        $query = Article::find()->where('admin_id = 0')->orderBy('id DESC');

        $html = $query->getTable([
            'id',
            'title',
            'content',
            'state' => ['type' => 'select', 'header' => '是否展示'],
            // 'state' => ['header' => '是否展示'],
            // ['header' => '是否展示', 'width' => '80px', 'value' => function ($row) {
            //     $text = $row->state == Article::STATE_VALID?'隐藏':'展示';
            //     return Hui::primaryBtn($text, ['updateState', 'id' => $row->id], ['class' => 'updateState']);
            // }],
            ['type' => ['edit' => 'saveArticle']],
        ], [
            'addBtn' => ['saveArticle' => '添加系统公告']
        ]);

        return $this->render('notice', compact('html'));
    }

    /**
     * @authname 添加/编辑资讯
     */
    public function actionSaveArticle($id = 0)
    {
        $model = Article::findModel($id);

        if ($model->load(post())) {
            $model->publish_time = self::$time;
            // $model->state = Article::STATE_INVALID;
            if ($model->save()) {
                return success();
            } else {
                return error($model);
            }
        }

        return $this->render('saveArticle', compact('model'));
    }

    /**
     * @authname 删除资讯
     */
    public function actionDeleteArticle()
    {
        return parent::actionDelete();
    }
}
