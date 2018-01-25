<?php $this->regCss('manager.css') ?>
<style type="text/css">
    .header {
        padding: 0px 5px;
        text-align: center;
        position: relative;
        height: 45px;
        line-height: 45px;
        color: #fff;
        font-size: 18px;
        color: #666;
        border-bottom: 1px solid #ddd;
        background: #fff;
    }
    .header a {
        display: inline-block;
        position: absolute;
        top: 1px;
        left: 5px;
        color: #666;
    }
    .back_arrow{
        position: relative;
    }
    .back_arrow:after,.back_arrow:before{
        content: "";
        position: absolute;
        width: 12px;
        border-top: 2px solid #666;
        transform-origin: left;
        -webkit-transform-origin: left;
        top: 20px;
        left: 10px;
    }
    .back_arrow:after{
        transform:rotate(45deg);
        -webkit-transform:rotate(45deg);
    }
    .back_arrow:before{
        transform:rotate(-45deg);
        -webkit-transform:rotate(-45deg);
    }
</style>


<div class="header">
    <a href="javascript:history.go(-1)"> <span class="back_arrow"></span> </a>
    经纪人
</div>



<div id="main">
    <div class="index-box">
        <div class="info clearfix">
            <div class="wxImg">
                <img src="<?= u()->face ?>">
            </div>
            <p class="realName nameAndTel"><?= $extend->realname ?></p>
            <p class="tel nameAndTel"><?= $extend->mobile ?></p>
            <a href="<?= url(['manager/income']) ?>" style="display: block;">
            	<p class="myIncome nameAndTel">
	            	<span class="myIncome">我的收入</span>
	            </p>
	            <p class="nameAndTel myIncome-num-wrap boxflex">
		            <span class="myIncome-num box_flex_1"><?= $extend->rebate_account ?></span>
		            <i class="earrow earrow-right"></i>
	            </p>
            </a>
            
        </div>
        <div class="meun-box menuBox">
            <div class="menu-wrap">
        		<a href="<?= url(['manager/customer']) ?>" style="display: block;">
	                <div class="menu-item customer boxflex" data-index="0">
	                    <i class="icon icon-customer"></i>
	                    <span class="menu-itemTitle">直属客户</span>
	                    <span class="box_flex_1 menu-itemNum"><?= $userNum ?>人</span>
	                    <i class="earrow earrow-right"></i>
	                </div>
        		</a>
        		<a href="<?= url(['manager/cover']) ?>" style="display: block;">
	                <div class="menu-item coverings boxflex" data-index="2">
	                    <i class="icon icon-coverings"></i>
	                    <span class="menu-itemTitle menu-cover">客户平仓</span>
	                    <span class="box_flex_1 menu-itemNum"><?= $orderNum ?>笔</span>
	                    <i class="earrow earrow-right"></i>
	                </div>
        		</a>
        		<a href="<?= url(['manager/myCode']) ?>" style="display: block;">
	                <div class="menu-item boxflex" data-index="3">
	                    <i class="icon icon-mycard"></i>
	                    <span class="menu-itemTitle menu-id">我的名片</span>
	                    <span class="box_flex_1 menu-itemNum"></span>
	                    <i class="icon icon-ewm"></i>
	                    <i class="earrow earrow-right"></i>
	                </div>
        		</a>
            </div>
        </div>
    </div>
</div>
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

</script>