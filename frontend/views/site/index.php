<?php use common\helpers\Html; ?>
<?php common\assets\HighStockAsset::register($this) ?>
<?php $this->regJs('candle') ?>

<?php $this->regCss('jiaoyi') ?>
<?php $this->regCss('trade') ?>
<?php $this->regCss('geren') ?>
<?php $this->regCss('common.css') ?>
 <style type="text/css">
 body{
    /*position: fixed;*/
    left: 0;

 }
  @keyframes sponde{
    0%{width:6px;height:6px;opacity:1;}
    100%{width:30px;height:30px;opacity:0;}
  }
  @-webkit-keyframes sponde{
    0%{width:6px;height:6px;opacity:1;}
    100%{width:30px;height:30px;opacity:0;}
  }
  @-moz-keyframes sponde{
    0%{width:6px;height:6px;opacity:1;}
    100%{width:30px;height:30px;opacity:0;}
  }
  @-ms-keyframes sponde{
    0%{width:6px;height:6px;opacity:1;}
    100%{width:30px;height:30px;opacity:0;}
  }
  @-o-keyframes sponde{
    0%{width:6px;height:6px;opacity:1;}
    100%{width:30px;height:30px;opacity:0;}
  }
  .aniContainer{
    position:absolute;
    width:50px;
    height:50px;
    display:flex;
    display:-webkit-box;/* android 2.1-3.0, ios 3.2-4.3 */
    display:-webkit-flex;/* Chrome 21+ */
    align-items:center;
    justify-content:center;
    -webkit-box-pack: center;/* android 2.1-3.0, ios 3.2-4.3 */
    -webkit-box-align: center;/* android 2.1-3.0, ios 3.2-4.3 */
    /*bottom: 94px;*/


    justify-content: center;
    -webkit-box-pack:center;
    -webkit-justify-content: center;
    -ms-flex-pack:center;
    justify-content:center;


    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-align-items: center;
    -ms-align-items: center;
    -o-align-items: center;
    align-items: center;
    right: -14px;
    visibility:hidden;
    transition: all 0.4s;
    -webkit-transition: all 0.4s;
    /*opacity:0;*/
  }
  .aniContainer .core{
    position:absolute;
    width:3px;
    height:3px;
    border-radius:50%;
    background:#2158ED;
    top:24px;
    left:24px;
  }
  .aniContainer .aniBorder{
      border-radius:50%;
     /* border:6px solid #2158ED;*/
     background:#2158ED;
      -webkit-animation:sponde 1s infinite;
     -moz-animation:sponde 1s infinite;
     -ms-animation:sponde 1s infinite;
     -o-animation:sponde 1s infinite;
     animation:sponde 1s infinite;
  }
/*弹出框 - start*/
#myModal{
    position: fixed;
    width: 100vw;
    height: 100vh;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background:rgba(0,0,0,.3);
}
.modal-content{
    width:calc(100% - 80px);
    margin:0 auto;
    background:#fff;
    border-radius:6px;
    margin-top:50%;
    padding:15px;
}
.title{
    position: relative;
    text-align: center;
    font-size:16px;
    margin-bottom:10px;
}
.icon-close{
    position: absolute;
    right:10px;
    top:0;
    width:20px;
    height:20px;
    line-height: 20px;
    text-align: center;
    border-radius:50%;
    border:1px solid #ccc;
    color:#ccc;
}
.notice-content{
    font-size:12px;
    color:#777777;
    line-height:20px;
    margin:0 10px;
}
 #createorderbox {
     bottom: 49px;
 }
/*弹出框 - end*/
</style>
    <div>
        <div class="transaction indexContent">
            <div class="tra_ad" style="display: none;">
                <a href=""><img src=""></a>
            </div>
            <!--账户资产-->
            <div class="boxflex assets-wrap">
                <div class="userinfo-wrap">
                    <a href="<?= url(['user/index']) ?>"><img src="<?= u()->face ?>"></a>
                    <p style="font-size:12px;">个人中心</p>
                </div>
                <div class="cash-asset box_flex_1">
                    <div class="asset">
                        资产<span id="userProfit" style="color:#eb7d12;"><?= sprintf('%.2f', u()->account - u()->blocked_account) ?></span>元
                    </div>
                    <div style="margin-top:6px;" class="btn-withdraw-wrap">
                        <div class="recharge overallPsd" data-url="<?= url(['user/recharge']) ?>"><span>充值</span></div>
                        <div class="withdraw overallPsd" data-url="<?= url(['user/withDraw']) ?>"><span>提现</span></div>
                    </div>
                </div>
                <!--账户资产-金币-->
                <div class="coin-asset orderCountDown" <?php if ($time >= BUY_ORDER_TIME) {$style = 'hidden';$time=BUY_ORDER_TIME;$disClass='';} else {$style='visible';$disClass='disabled';} ?> style="visibility: <?= $style ?>;">
                    <label>下单倒计时</label>
                    <div>
                        <i class="icon-clock"></i>
                        <span class="redsymbol countDown">00:<?= $time?></span>
                    </div>
                </div>
            </div>
            <!-- 商品列表 -->
            <div class="goodslist-wrap">
                <ul class="boxflex selectProcut">
                    <?php foreach ($productArr as $key => $value): ?>
                    <li class="box_flex_1 <?php if ($key == $product->id){ echo 'active';} ?>" data-pid="<?= $key ?>" data-name="<?= $value['table_name'] ?>" data-close="<?= $value['close'] ?>" style="width: 33%; text-align: left">
                    <?php $class='down';if ($value['price'] > $value['close']){ $class = 'up';} $price=$value['price'];
                    if ((date('w') == 6 && $value['source'] == 1 && date('G') > 5) || (date('w') == 0 && $value['source'] == 1)) {$price='休市';} ?>
                        <div class="gooddetail"><?= $value['name'] ?><br><span class="price isTrade<?= $key ?> price-<?= $class ?>"><span><?= $price ?></span>
                        <?php if ($price != '休市'): ?>
                            <i class="arrow arrow-<?= $class ?>"></i>
                        <?php endif ?></span></div>
                    </li>
                    <?php endforeach ?>
                </ul>
            </div>
            <!-- 商品详情以及走势图K线 -->
            <div class="goodinfo-wrap">
                <div class="goodinfo boxflex">
                    <span class="key">昨收:</span>
                    <span class="value closeprice"><?= floatval($newData['close']) ?></span>
                    <span class="box_flex_1"></span>
                    <span class="key">今开:</span>
                    <span class="value openprice"><?= floatval($newData['open']) ?></span>
                    <span class="box_flex_1"></span>
                    <span class="key">最高:</span>
                    <span class="value maxprice"><?= floatval($newData['high']) ?></span>
                    <span class="box_flex_1"></span>
                    <span class="key">最低:</span>
                    <span class="value minprice"><?= floatval($newData['low']) ?></span>
                </div>
                <div style="position:relative">
                    <div id="areaContainer" style=" min-width: 310px; width:100%;margin-top:-40px"></div>
                    <div id="kContainer" style=" min-width: 310px; width:100%; display: none;margin-top:-40px"></div>
                    <div class="aniContainer">
                       <span class="core"></span>
                       <span class="aniBorder"></span>
                    </div>
                </div> 

                <div class="graph-kind">
                    <ul id="feature-tab" class="boxflex" style="width: 100%; transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); transition-duration: 500ms; transform: translate(0px, 0px) translateZ(0px);">
                        <li class="box_flex_1 active"><a data-value="" data-unit="-1">今日走势</a></li>
                        <!-- <li class="box_flex_1"><a data-value="1" data-unit="0">1M</a></li> -->
                        <li class="box_flex_1"><a data-value="2" data-unit="1">5分钟k线</a></li>
                        <li class="box_flex_1"><a data-value="5" data-unit="2">10分钟k线</a></li>
                        <li class="box_flex_1"><a data-value="6" data-unit="3">30分钟k线</a></li>
                        <li class="box_flex_1"><a data-value="3" data-unit="4">60分钟k线</a></li>
                        <!-- <li class="box_flex_1"><a data-value="10" data-unit="5">日K线</a></li> -->
                    </ul>
                </div>
            </div>
            <!-- 弹框 -start-->
            <?php if (!empty($article)): ?>
            <div id="myModal">
                <div class="modal-content">
                    <p class="title">
                        <span><?= $article->title ?></span>
                        <span class="icon-close">&times;</span>
                    </p>

                    <p class="notice-content"><?= $article->content ?></p>
                </div>
            </div>
            <?php endif ?>
            <!-- 弹框 -end -->

            <div class="deal-btn-wrap <?= $disClass ?>">
                <div class="table">
                    <div class="table-cell btnrise-wrap buyProduct" data-type="1">
                        <!-- <label><span><img src="/images/icon-up.png" alt="" style="margin-top: -8px;"></span></label> -->
                        <label><span>买涨</span></label>
                    </div>
                    <div class="table-cell btndown-wrap buyProduct" data-type="2">
                        <!-- <label><span><img src="/images/icon-down.png" alt="" style="margin-top: -8px;"></span></label> -->
                        <label><span>买跌</span></label>
                    </div>
                </div>
                <p><?= config('web_trade_time', '商品时间：周一~周五早上6:00~凌晨4:00 周末休市') ?></p>
            </div>
            <div class="holdlist-wrap"><ul></ul></div>
        </div>
<!--         <div class="footer_cont table">
            <i class="table-cell arrow arrow-tip"></i>
            <span class="table-cell">
                <marquee direction="left" scrollamount="3" class="active"><?= config('web_scroll', '利用大数据行情，抓住大波动收益机会！&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：投资有风险，入市需谨慎！&nbsp;&nbsp;&nbsp;&nbsp;提现时间为每周一至周五上午9：30-10：30    下午：2：30-5：30') ?></marquee>

            </span>
        </div> -->
        <div class="myContent">

        </div>
        <div class="myButtom" style="margin-bottom:28px;">
            <div class="holdlist-wrap">
                <ul>
                <?= $this->render('_orderList', compact('orders')) ?>
                </ul>
            </div>
        </div>
    </div>

   <!-- <div class="myButtom">
        <div class="holdlist-wrap">

        </div>
    </div>-->
<ul class="clear-fl footer-nav">
    <li><a href="/site/shop">
            资讯
        </a></li>
    <li class="active"><a href="/site/index">
            交易
        </a></li>
    <li class=""><a href="/user/index">
            我的
        </a></li>
</ul>
<input type="hidden" id="productId" value="<?= $product->id ?>">
<input type="hidden" id="productNum" value="<?= $productNum ?>">
<div id="jsonData" style="display: none;"><?= $json ?></div>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script type="text/javascript">
//屏蔽所有右上角功能
function onBridgeReady(){
    WeixinJSBridge.call('hideOptionMenu');
}

if (typeof WeixinJSBridge == "undefined"){
    if( document.addEventListener ){
        document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
    }else if (document.attachEvent){
        document.attachEvent('WeixinJSBridgeReady', onBridgeReady); 
        document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
    }
} else {
    onBridgeReady();
}

$(function() {
    //倒计时
    var wait = <?= $time ?>;
    function time(obj) {
        if (wait == -1) {
            $('.orderCountDown').css('visibility', 'hidden');
            $('.deal-btn-wrap').removeClass('disabled');
            wait = <?= BUY_ORDER_TIME ?>;
            return false;
        }
        if (wait < 10) {
            obj.html('00:0' + wait);
        } else {
            obj.html('00:' + wait);
        }
        wait--;
        setTimeout(function() {
            time(obj);
        },
        1000);
    }
    //订单是否在倒计时之内
    <?php if ($time < BUY_ORDER_TIME): ?>
        setTimeout(function() {
            time($('.countDown'));
        },
        1000);
    <?php endif ?>

    $('.buys a').click(function() {
        $(this).addClass("selects").siblings().removeClass("selects");
    })
    //三个产品的切换
    $('.selectProcut>li').click(function() {
        $('.selectProcut>li').removeClass("active");
        $(this).addClass("active");
        window.location.href = "/site/index?pid="+$(this).data('pid');
    })

    //买涨买跌
    $('.buyProduct').click(function() {
        if ($('.deal-btn-wrap').hasClass('disabled')) {
            return $.alert('下单后<?= BUY_ORDER_TIME ?>秒之内不能再次下单！');
        }
        var data = {};
        data.pid = <?= $product->id ?>;
        data.type = $(this).data('type');
        $.post("<?= url('site/ajaxBuyState')?>", {data: data}, function(msg) {
            if (msg.state) {
                if (msg.data == -1) {
                    window.location.href = msg.info;
                } else {
                    $('.myContent').append(msg.info);
                    $('body').find('.footer_cont').remove();    
                }
            } else {
                $.alert(msg.info);
            }
        }, 'json');
    })

    //弹窗关闭按钮
    $("#myModal .icon-close").click(function(){
        $.post("<?= url(['site/ajaxNotice'])?>", function(msg) {
            $("#myModal").css("display","none");
        }, 'json');
    });

    //下单
    $(".myContent").on("click", '.payOrder', function() {
        var data = {};
        data.hand = parseInt($('.myContent').find('.hand').val());
        data.deposit = $('.myContent .deposit').find('.active').html();
        //止盈止损点数
        // data.stop_profit_point = $('.myContent .stop_profit_point').val();
        // data.stop_loss_point = $('.myContent .stop_loss_point').val();
        data.point = $('.myContent .point').find('.active').html();
        
        //产品id
        data.product_id = <?= $product->id ?>;
        data.rise_fall = $(this).data('type');
        $.post("<?= url('order/ajaxSaveOrder')?>", {data: data}, function(msg) {
            if (msg.state) {
                // window.location.href = '<?= url(['order/position']) ?>';
                var money = parseFloat($('#userProfit').html());
                var userProfit = parseFloat(money - msg.data[0]);
                $('#userProfit').html((userProfit).toFixed(2));
                $('#productNum').val(msg.data[1]);
                $('.orderCountDown').css('visibility', 'visible');
                $('.deal-btn-wrap').addClass('disabled');
                $.alert('购买成功！');
                $('.myContent').html('');
                //var $dom = $('.myButtom .holdlist-wrap>ul').html() + msg.info;
                $('.myButtom .holdlist-wrap>ul').html(msg.info);

                //改变k线图高度  重新渲染；
                resetKline();
                time($('.countDown'));
                $('.myContent').before('<div class="footer_cont table"><i class="table-cell arrow arrow-tip"></i><span class="table-cell"><marquee direction="left" scrollamount="3" class="active">利用大数据行情，抓住大波动收益机会！&nbsp;&nbsp;&nbsp;&nbsp;温馨提示：投资有风险，入市需谨慎！&nbsp;&nbsp;&nbsp;&nbsp;提现时间为每周一至周五上午9：30-10：30    下午：2：30-5：30</marquee> </span></div><div>');
            } else {
                $('.right .deposit_price').attr('price', data.price_rate * data.hand);
                $.alert(msg.info);
                // if (msg.info == '您的余额已不够支付，请充值！') {
                //    window.location.href = '<?= HX_PAY_DOMAIN . url(['user/pay']) ?>'; 
                // }
            }
        }, 'json');
    });
});
</script>

    
