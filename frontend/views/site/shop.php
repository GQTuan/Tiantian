<?php use common\helpers\Html; ?>
<?php common\assets\HighStockAsset::register($this) ?>

<?php $this->regCss('jiaoyi') ?>
<?php $this->regCss('trade') ?>
<?php $this->regCss('geren') ?>
<?php $this->regCss('common.css') ?>
 

<?php $this->regJs('swiper.min') ?>
<?php $this->regCss('swiper.min') ?>

<style type="text/css">
    .container {
        padding-right: 0px;
        padding-left: 0px;
        margin-right: auto;
        margin-left: auto;
    }
    .home .banner {
        overflow: hidden;
        width: 100%;
        text-align: start;
        z-index: 2;
        position: fixed;
    }
    .swiper-slide img {
        width: 100%;
    }
    .home .content {
        top: 118px;
        z-index: 3;
        position: absolute;
        width: 100%;
    }
    .carder {
        background-color: #fff;
        border-radius: 6px;
        margin: 0 12px;
        padding: 18px;
        display: flex;
        display: -moz-box;
        display: -webkit-box;
        box-shadow: 0rem 0rem 0.5rem 0rem rgba(0, 0, 0, 0.12);
    }
    .manifact {
        padding: 8px 0;
    }
    .manifact {
        margin-top: 10px;
    }
    .carder .item {
        display: block;
        box-flex: 1;
        -moz-box-flex: 1;
        -webkit-box-flex: 1;
        text-align: center;
    }
    .carder .item .value {
        margin-top: 6px;
        font-size: 14px;
        color: #354162;
        font-weight: bold;
        font-weight: 700;
    }
    .value span:first-child{
        font-size: 14px;
        color: #354162;
        font-weight: bold;
        font-weight: 700;
    }
    .title{
        font-size: 12px;
    }
    .carder .item .title2 {
        font-size: 12px;
        margin-top: 6px;
        color: #354162;
    }
    .carder .item img {
        width: 8px;
    }
    .carder .head {
        font-size: 12px;
        border-bottom: solid rgba(217,216,219, 0.5) 1px;
        height: 28px;
        line-height: 28px;
        padding: 0 20px;
        color: #6D768D;
        margin-bottom: 24px;
    }
    .quotation .divider {
        border-right: 1px #D9D8DB solid;
        display: inline-block;
        margin: 8px 0;
        padding: 13px 0;
    }
    .carder .item .value .flag {
        width: 44px;
    }
    .findbox {
        margin: 12px;
        background: #FFFFFF;
        border-radius: 6px;
        box-shadow: 0rem 0rem 0.5rem 0rem rgba(0, 0, 0, 0.12);
    }
    .findbox .categorys {
        padding: 14px 0;
        border-bottom: solid 1px #D9D8DB;
        display: flex;
    }
    .findbox .categorys .item {
        text-align: center;
        flex: 1;
        font-size: 12px;
    }
    .findbox .categorys .item .on {
        padding: 1px 4px;
        background-color: #354162;
        color: #ffffff;
        border-radius: 2px;
    }
    .yincan{
        width: calc(100% - 24px)!important;
        height: 66px!important;
        position: absolute!important;
        background-color: #FFFFFF!important;
        filter: alpha(opacity=0)!important;
        right: 12px!important;
        left: 12px!important;
        padding-top: 20px;
    }
    .yincan p{
        line-height: 24px!important;
    }
    .server_mask{
        position: fixed;
        width: 100vw;
        height: 100vh;
        z-index: 999999999999;
        left: 0;
        top: 0;
        background: rgba(0,0,0,.8);
        padding: 140px 70px 0 70px;
        box-sizing: border-box;
        display: none;
    }
    .server_mask img{
        width: 100%;
    }
</style>


<div class="container">
    <div class="header"></div>
    <div class="mainbody">
        <div id="app" class="home">
            <div class="banner">
                <img style="width:100%;" src="/images/banner2.png">

                <!-- <div class="swiper-container swiper-container-horizontal">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide swiper-slide-duplicate swiper-slide-next swiper-slide-duplicate-prev" data-swiper-slide-index="0">
                            <a href="/site/index">
                                <img src="/images/banner2.png">
                            </a>
                        </div>
                     
                        
                        <div class="swiper-slide swiper-slide-duplicate-active swiper-slide-prev swiper-slide-duplicate-next" data-swiper-slide-index="0">
                            <a href="/site/index">
                                <img src="/images/banner2.png">
                            </a>
                        </div>
                        
                           
                        <div class="swiper-slide swiper-slide-duplicate swiper-slide-active swiper-slide-duplicate-prev" data-swiper-slide-index="0">
                            <a href="/site/index">
                                <img src="/images/banner2.png">
                            </a>
                        </div>
                    </div>
                </div> -->
            </div>
            <div class="content">
                <div class="carder" style="display: block;padding: 8px 0">
                    <div class="head">
                        <span>今日涨跌板</span> 
                        <a class="into" style="float: right" href="/site/index">全部<img src="/images/into.png"></a>
                    </div>
                    <div class="quotation " style="display: flex; display: -moz-box; display: -webkit-box;">
                        <?php foreach ($productArr as $key => $value): ?>
                            <div class="item">
                                <a href="/site/index">
                                    <?php $class='down';if ($value['price'] > $value['close']){ $class = 'up';} $price=$value['price'];
                                    if ((date('w') == 6 && $value['source'] == 1 && date('G') > 5) || (date('w') == 0 && $value['source'] == 1)) {$price='休市';} ?>
                                    <div class="title"><?= $value['name'] ?></div>
                                    <div class="value">
                                        <span id="price-EURUSD"><?= $price ?></span>
                                        <span id="updown-EURUSD">
                                        <?php if ($price != '休市'): ?>
                                        <img src="/images/icon_arrow_<?= $class ?>.png">
                                        <?php endif ?>
                                    </span>
                                    </div>
                                </a>
                            </div>
                            <span class="divider"></span>
                        <?php endforeach ?>
                        <!--<div class="item">
                            <a href="/site/index">
                                <div class="title">黄金</div>
                                <div class="value">
                                    <span id="price-EURUSD">85543</span>
                                    <span id="updown-EURUSD">
                                        <img src="/images/icon_arrow_down.png">
                                    </span>
                                </div>
                            </a>
                        </div>
                        <span class="divider"></span>
                        <div class="item">
                            <a href="/site/index">
                                <div class="title">美元</div>
                                <div class="value">
                                    <span id="price-EURUSD">17296</span>
                                    <span id="updown-EURUSD">
                                        <img src="/images/icon_arrow_down.png">
                                    </span>
                                </div>
                            </a>
                        </div>
                        <span class="divider"></span>
                        <div class="item">
                            <a href="/site/index">
                                <div class="title">天然气</div>
                                <div class="value">
                                    <span id="price-EURUSD">5366</span>
                                    <span id="updown-EURUSD">
                                    <img src="/images/icon_arrow_up.png">
                                    </span>
                                </div>
                            </a>
                        </div>
                        <span class="divider"></span>-->
                    </div>
                </div>


                <div class="manifact carder">
                    <div class="item">
                        <div class="value">
                            <a href="/user/recharge">
                                <img class="flag" src="/images/home_recharge.png">
                            </a>
                        </div>
                        <div class="title2">充值</div>
                    </div>
                    <div class="item">
                        <div class="value">
                            <a href="/site/index">
                                <img class="flag" src="/images/home_trade.png">
                            </a>
                        </div>
                        <div class="title2">买卖</div>
                    </div>
                    <div class="item">
                        <div class="value">
                            <a href="/manager/register">
                                <img class="flag" src="/images/home_jinliren.png">
                            </a>
                        </div>
                        <div class="title2">经纪人</div>

                    </div>
                    <div class="item">
                        <div class="value">              
                                <a href="javascript:;" onclick="showewm();"><img class="flag" src="/images/home_kefu.png"></a>
                        </div>
                        <div class="title2">客服</div>
                    </div>
                </div>



           

                <div class="findbox" id="clj">
                    <div class="categorys">
                        <span class="item">
                            <a class="on" id="shishi" href="javascript:;" onclick="categoryClicked(1)">实时快讯</a>
                        </span>
                        
                        <span class="item">
                            <a href="javascript:;" id="rili" onclick="categoryClicked(2)">财经日历</a>
                        </span>
                    </div>

                    <div class="kuaixun" style="margin: 0rem;padding-bottom: 0;box-shadow:none;" id="kuaixun">
                        <!-- <div style="width:90%;height:7200px; position:absolute;background-image: url(/images/time.png); filter:alpha(opacity=0);top:919;left:172;"></div> -->
                        <div class="yincan" style="width:89%;height:1.1%;position:absolute;background-color:#FFFFFF;filter:alpha(opacity=0);right:2rem;left:1rem">
                            <p style="color: red;margin: 0 auto;text-align: center;line-height:2rem">关注云生活，让你的钱包涨涨涨！<br>关注微信，咨询您的专属VIP客服！</p>
                        </div>
                        <iframe height="8000px" width="90%" marginheight="50px" frameborder="0" scrolling="no" src="http://www.jin10.com/example/jin10.com.html?width=max&amp;height=8000&amp;theme=white&amp;scrolling=no">
                            
                        </iframe>
                    </div>
                    <div class="articles" id="articles-cjrl" style="display:none;">
                        <iframe id="iframe" height="3500px" width="90%" frameborder="0" scrolling="no" src="http://rili-d.jin10.com/open.php">
                            
                        </iframe>
                    </div>
                </div>
                



                <div class="copyright-flow">
                    <h3>
                        <a id="cr-wx" href="javascript:;" onclick="showewm();"><img src="/images/icon_wx.png"><span>微信公众号</span></a>

                    </h3>
                    <span class="time">Copyright 2016-2018 v.206002.com</span>
                </div>

            </div>
        </div>
    </div>

    <div id="wxewm" class="crp" onclick="showewm();" style="display:none;">
        <div class="crp-box">
            <h3>- 微信客服 -</h3>
            <div class="crp-img">    
                <script>
                var m=3; //随机显示图片的总数量
                var n=Math.floor(Math.random()*m+1)
                document.write ("<img src='/images/wei"+n+".jpg'>");
                </script><img src="/images/wei3.jpg">         
            </div>
            <p>长按二维码识别</p>
            <button type="button" onclick="showewm();">知道了</button>
        </div>
    </div>


    <div class="server_mask">
        <img  src="/images/mmexport1516429720270.jpg">
    </div>

    <script>
        //二维码显示与隐藏
        function showewm(){
            $(".server_mask").show();
        }
        $(".server_mask img").click(function(e){
            e.stopPropagation();
        })

        $(".server_mask").click(function(){
            $(this).hide();
        });
        function categoryClicked(index) {
            if (index == 1){
                $("#shishi").addClass('on');
                $("#rili").removeClass('on');
                $("#kuaixun").css("display",'block');
                $("#articles-cjrl").css("display",'none');
            }else if(index == 2){
                $("#rili").addClass('on');
                $("#shishi").removeClass('on');
                $("#articles-cjrl").css("display",'block');
                $("#kuaixun").css("display",'none');
                
            }
        }
        $(function(){
            var swiper = new Swiper('.swiper-container', {
                pagination: '.swiper-pagination',
                nextButton: '.swiper-button-next',
                prevButton: '.swiper-button-prev',
                paginationClickable: true,
                spaceBetween: 1,
                centeredSlides: true,
                autoplay: 2500,
                autoplayDisableOnInteraction: false,
                loop:true
            });
        });

        //页面定时刷新
        function myrefresh() 
        { 
            window.location.reload(); 
        } 
        // setTimeout('myrefresh()',30000); //指定1秒刷新一次    
    </script>
</div>







<div class="myButtom">
    <div class="holdlist-wrap">
        <ul class="clear-fl footer-nav">
            <li class="active"><a href="/site/shop">
                资讯
            </a></li>
            <li><a href="/site/index">
                交易
            </a></li>
            <li class=""><a href="/user/index">
                我的
            </a></li>
        </ul>
    </div>
</div>
