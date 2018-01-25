<?php

namespace frontend\controllers;

use Yii;
use frontend\models\User;
use frontend\models\Product;
use frontend\models\Order;
use frontend\models\ProductPrice;
use frontend\models\Coupon;
use frontend\models\UserCoupon;
use frontend\models\UserRebate;
use frontend\models\AdminDeposit;
use frontend\models\DataAll;

class OrderController extends \frontend\components\Controller
{
    public function beforeAction($action)
    {
        if (user()->isGuest) {
            $wx = session('wechat_userinfo');
            $this->redirect(['site/login']);
            return false;
            // if (!empty($wx)) {
            //     $user = User::find()->where(['open_id' => $wx['openid']])->one();
            //     $user->login(false);
            // } else {
            //     $code = get('code');
            //     if (empty($code)) {
            //         $this->redirect(['/wechart.php?appid=' . wechatInfo()->appid]);
            //         return false;
            //     } else {
            //         User::registerUser($code);
            //     }
            // }
        }
        if (!parent::beforeAction($action)) {
            return false;
        } else {
            return true;
        }
    }

    //下单
    public function actionIndex()
    {
        $this->view->title = '下单';
        $pid = req('pid');
        if (user()->isGuest) {
            return $this->redirect(['site/login']);
        }
        $product = Product::find()->andWhere(['id' => $pid])->with('dataAll')->one();
        $productPrice = ProductPrice::getSetProductPrice($product->id);
        if (!isset($productPrice)) {
            return $this->redirect(['site/wrong']);
        }
        //体验卷
        $couponType = UserCoupon::getNumberType($pid);
        return $this->render('index', compact('product', 'productPrice', 'couponType'));
    }

    //持仓
    public function actionPosition()
    {

        $this->view->title = '持仓';
        if (user()->isGuest) {
            return $this->redirect(['site/login']);
        }
        $orders = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id])->with('product')->orderBy('created_at DESC')->all();
        return $this->render('position', compact('orders'));
    }

    //平仓
    public function actionSellPosition($id)
    {
        $this->view->title = '平仓';
        if (user()->isGuest) {
            return $this->redirect(['site/login']);
        }
        $order = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'id' => $id])->with('product')->one();
        if (empty($order)) {
            return $this->redirect(['site/index']);
        }
        return $this->render('sellPosition', compact('order'));
    }

    /**
     * 更新所有持仓单数据ajax请求
     * @access public
     * @return json
     */
    public function actionAjaxUpdateOrder()
    {
        $orders = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id])->all();
        $data = [];
        foreach ($orders as $order) {
            $data[$order->id] = Order::userWinOrder($order);
        }
        return success($data);
    }

    /**
     * 更新一条持仓单数据ajax请求
     * @access public
     * @return json
     */
    public function actionAjaxUpdateOrderOne()
    {
        $data = Order::getUserOrderData(post('id'));
        // test($data);
        return success($data);
    }

    /**
     * 保存订单
     * @access public
     * @return json
     */
    public function actionAjaxSaveOrder()
    {
        if (user()->isGuest) {
            return $this->redirect(['site/login']);
        }
        $data = post('data');
	if($data['product_id'] == 9) {
	    return error('暂时无法下单');
	}
        //判断此期货是否在商品时间内
        if (!Product::isTradeTime($data['product_id'])) {
            return error('非买入时间，无法委托买入！');
        }
        if ($data['hand'] <= 0) {
            return error('商品手数不能小于等于0！');
        }
        $user = User::findModel(u()->id);
        if ($user->state == User::STATE_INVALID) {
            return error('您的账号已经冻结！');
        }
        $order = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $data['product_id']])->orderBy('created_at DESC')->one();
        $time = $order?time() - strtotime($order->created_at):BUY_ORDER_TIME + 1;  
        if ($time <= BUY_ORDER_TIME) {
            return error('下单后'.BUY_ORDER_TIME.'秒之内不能再次下单！');
        }
        //商品单日最高持仓金额
        $maxOrder = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $data['product_id']])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00', time())])->andWhere(['<', 'created_at', date('Y-m-d 00:00:00', strtotime('tomorrow'))])->with('product')->select('SUM(deposit) deposit')->one();
        if ($maxOrder->deposit > config('web_product_amount')) {
            return error('商品单日最高持仓总金额不能超过' . config('web_product_amount') . '！');
        }
        $product = Product::findModel($data['product_id']);
        //特殊产品周末正常
        if ((date('w') == 0 && $product->source == Product::SOURCE_TRUE) || (date('G') > 5 && date('w') == 6 && $product->source == Product::SOURCE_TRUE)) {
            return error('周末休市，无法委托买入！');
        }
        $orders = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $data['product_id']])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00', time())])->with('product')->orderBy('created_at DESC')->all();

        //订单处理
        $res = Order::saveOrder($data);
        if ($res['code'] == 1) {
            //取出订单信息
            $orders = Order::find()->where(['order_state' => Order::ORDER_POSITION, 'user_id' => u()->id, 'product_id' => $data['product_id']])->andWhere(['>', 'created_at', date('Y-m-d 00:00:00', time())])->with('product')->orderBy('created_at DESC')->all();
            return success($this->renderPartial('/site/_orderList', compact('orders')), [$data['deposit'] * $data['hand'], count($orders)]);
        } else {
            return error($res['info']);
        }
    }

    /**
     * 平仓订单ajax请求
     * @access public
     * @return json
     */
    public function actionAjaxSellOrder()
    {
        if (user()->isGuest) {
            return $this->redirect(['site/login']);
        }
        $order = Order::find()->where(['id' => post('id'), 'order_state' => Order::ORDER_POSITION, 'user_id' => u()->id])->one();
        if (empty($order)) {
            return error('此订单已被系统平仓！');
        }
        $bool = Order::sellOrder($order->id);
        if ($bool) {
            self::success('平仓成功！');
        } else {
            self::error('订单数据不存在！');
        }
    }
    
    /**
     * 平仓订单ajax请求
     * @access public
     * @return json
     */
    public function actionSo()
    {
        // $bool = Order::sellOrder(1, 3780);
        // $order = Order::findOne(1);
        // UserRebate::isUserRebate($order);
        // AdminDeposit::depositRecord($order);
        $bool = true;
        if ($bool) {
            self::success('平仓成功！');
        } else {
            self::error('订单数据不存在！');
        }
    }
}
