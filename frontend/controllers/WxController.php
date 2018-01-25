<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use frontend\models\Product;
use frontend\models\Order;
use frontend\models\ProductPrice;
use frontend\models\Coupon;
use frontend\models\UserCoupon;
use frontend\models\UserMedal;
use frontend\models\DataAll;

class WxController extends \frontend\components\Controller
{

    public function actionIndex()
    {
        $url = $_SERVER['HTTP_HOST'];
        $menu = '
              {
                "button": [
                    {
                        "type": "view",
                        "name": "立即体验",
                        "url": "http://' . $url . '/site/shop"
                    },
                    {
                        "type": "view",
                        "name": "经纪人",
                        "url": "http://' . $url . '/manager/register"
                    },
                    {
                        "type":"click",
                        "name":"在线客服",
                        "key":"XX_Nanqe_001" 
                    }
                ]
            }
                ';
        if(get('add') == 'lj') {
            require Yii::getAlias('@vendor/wx/WxTemplate.php');
            $wxTemplate = new \WxTemplate();
            $access_token = $wxTemplate->getAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $access_token;
            $result = httpRequest($url, $menu);
            test($result);
        }
    }

    /**
     * @authname 微信菜单删除
     */
    public function actionDelete()
    {
        if(get('delete') == 'lj') {
            $menu = '';
            require Yii::getAlias('@vendor/wx/WxTemplate.php');
            $wxTemplate = new \WxTemplate();
            $access_token = $wxTemplate->getAccessToken();
            $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $access_token;
            $result = httpRequest($url, $menu);
            test($result);
        }
    }
}
