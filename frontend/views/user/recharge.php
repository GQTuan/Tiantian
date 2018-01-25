<?php $this->regCss('iconfont/iconfont.css') ?>
<?php $this->regCss('mine.css') ?>
<?php $this->regCss('common.css') ?>
<style type="text/css">
    body{background:#fff!important;}
    .header_con{
        height: 50px;
        line-height: 50px;
        background: #F54B4B;
        position: relative;
        text-align: center;
        margin-bottom: 5px;
    }
    .header_con p{
        font-size: 16px;
        color: #fff;
    }
    .header_con a{
        width: 25px;
        height: 20px;
        position: absolute;
        left: 20px;
        top: 15px;
        z-index: 1000;
    }
    .header_con a img{
        width: 100%;
        height: 100%;
        position: absolute;
        left: 0;
        top: 0;
    }
    .selecthe {
        color: rgb(51, 51, 51);
        font-size: 12px;
        line-height: 28px;
        border-top: none;
        padding: 5px 15px;
        margin: 0;
    }
    .boxflex1.paystyle {
        color: #333;
        border-top: none;
        border-bottom: none;
    }
    .btn_re:nth-child(6){
        margin-left: 0;
    }
    .group_btn .btn_re a{
        /*background: rgb(22, 22, 22)!important;*/
        /*color: #D6AD30!important;*/
        border:none;
        font-size: 14px;
        border:1px solid #999;
        box-sizing: border-box;
    }
    .group_btn .btn_re .btn_money.on{
        background: #f54b4b !important;
        color: white !important;
        border:1px solid #f54b4b;
    }
    .boxflex1.paystyle.checkImg2 {
        border-top:none!important;
    }
    .payType .boxflex1.paystyle {
        height: 44px;
        line-height: 44px;
        border-bottom: 1px solid #e0e0e0;
        padding: 0 30px;
    }
    .boxflex1.paystyle.checkImg2{
        border-top: 1px solid #e0e0e0!important;
    }
    .moneyhead {
        font-size: 12px;
        height: 50px;
        line-height: 50px;
    }
    .boxflex1.paystyle span+img{
        width: 22px;
        height: 22px;
        position: relative;
        top: 14px;
    }
    .boxflex1.paystyle img:nth-child(1){
        width: 26px!important;
        height: 26px;
        border-radius: 50%;
        margin-right: 26px;
    }
    .boxflex1.paystyle span{
        font-size: 14px;
    }
    .recharge-btn{
        color: #fff;
        background: #f54b4b;
        margin: 28px;
        text-align: center;
        font-size: 15px;
        border-radius: 5px;
        padding: 7px 0;
    }
    .field-chargeAmount #chargeAmount {
        /*background: rgb(22, 22, 22) !important;
        border-color: rgb(76, 66, 35) !important;*/
        /*color: white;*/
    }
    .form-control {
        display: block;
        width: 100%;
        height: 34px;
        padding: 6px 12px;
        font-size: 14px;
        line-height: 1.42857143;
        color: #555;
        background-color: #fff;
        background-image: none;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
        -webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
        -o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        box-sizing: border-box;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .boxflex1 {
         background: transparent; 
    }
    .no_border{
        border-bottom: 0!important;
    }
    .real_count_con{
        color: #e4393c;
        font-size: 12px;
        padding: 0 15px;
        margin-top: 12px;
    }
    .recharge-run{
        color: red;
        font-size: 11px;
        text-align: center;
    }
</style>

<header class="header_con">
    <a href="javascript:history.go(-1)" class="lf">
        <img src="/images/call_back.png">
    </a>
    <p>充值</p>
</header>
<div class="container " style="padding:0;">
    <p class="selecthe">选择充值面额（元）</p>
    <div class="col-xs-12">
        <div style="padding: 0 10px;" class="form-group field-chargeAmount required">
            <input type="text" id="chargeAmount" class="form-control" placeholder="可输入10-10000的整数金额（元）">
            <div class="help-block"></div>
        </div>            
    </div>
    <?php $form = self::beginForm(['showLabel' => false, 'action' => url(['user/pay']), 'id' => 'payform']) ?>
    <div class="boxflex1 paystyle" style="padding: 10px 15px 0;">
        <div class="group_btn clearfloat">
            <div class="btn_re">
                <a class="btn_money on">68</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">160</a>
            </div>
            <div class="btn_re btn_center">
                <a class="btn_money">340</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">860</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">1500</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">2300</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">3200</a>
            </div>
            <div class="btn_re">
                <a class="btn_money">4400</a>
            </div>
        </div>
        <input type="hidden" id="amount" name="amount" value="68">
        <input type="hidden" id="type" name="type" value="1">
    </div>
    <p class="real_count_con">实际到账：<span class="real_count"></span></p>
    <div class="boxflex1">
        <div class="moneyhead">充值方式</div>
    </div>
    <div class="payType">
        <div class="boxflex1 paystyle checkImg2" style="border-top:0;"  data-type="1">
            <img src="/images/icon-chat.png" style="width: 20px;">
            <span>微信扫码</span>
            <img src="/images/seleted.png" alt="" style="float:right;" class="check-paytwo checkPay" >
        </div>

       <div class="boxflex1 paystyle checkImg1"  data-type="2">
            <img src="/images/alipay.png" style="width: 20px;">
            <span>支付宝扫码</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-payone checkPay" >
        </div>
       <div class="boxflex1 paystyle checkImg1"  data-type="3">
            <img src="/images/qqpay.png" style="width: 20px;">
            <span>QQ扫码</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-payone checkPay" >
        </div>
       <!--<div class="boxflex1 paystyle checkImg2" style="border-top:0;"  data-type="4">
            <img src="/images/jd.png" style="width: 20px;">
            <span>京东扫码</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-paytwo checkPay" >
        </div>-->
       <div class="boxflex1 paystyle checkImg1"  data-type="5">
            <img src="/images/pay.png" style="width: 20px;">
            <span>银联扫码</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-payone checkPay" >
        </div>
       <!--<div class="boxflex1 paystyle checkImg1"  data-type="6">
            <img src="/images/pay.png" style="width: 20px;">
            <span>H5网银支付</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-payone checkPay" >
        </div>
        <div class="boxflex1 paystyle checkImg1"  data-type="7">
            <img src="/images/pay.png" style="width: 20px;">
            <span>手机银联快捷</span>
            <img src="/images/notseleted.png" alt="" style="float:right;" class="check-payone checkPay" >
        </div>-->
    </div>
    <div class="recharge-btn" id="payBtn">立即充值</div>


    <p class="recharge-run">充值规则：每笔收取2%手续费</p>

    <?php self::endForm() ?>
    <div class="row">
        <!-- <div class="col-xs-12 text-center font_14 remain">跳转至微信安全支付网页，微信转账说明</div> -->
<!--         <div class="col-xs-12 text-center font_12">
            <font>注1：暂时只能使用借记卡充值</font>
            <br>
            <font>注2：为了管控资金风险，单日充值限额20000元</font>
        </div> -->
    </div>
</div>
<script>
$(function() {
    var options = [68, 160, 340, 860, 1500, 2300, 3200, 4400];
    $(".btn_money").each(function(index, el) {
       var range = parseInt( 10 - Math.random() * 20 );
       var count = options[index] + range;
       $(el).html(count);
    });




    $('#type').val(1);
    $(".btn_money").click(function() {
        $(".on").removeClass("on");
        $(this).addClass("on");
        $('#amount').val($(this).html());
        $("#chargeAmount").val( $(this).html() );

        var val = $(this).html();
        var rate = <?= $rate ?>;    //后台给定
        $(".real_count").html(val - Math.ceil( val * rate ));
    });

    $(".btn_money.on").trigger("click");
    
    $("#chargeAmount").blur(function(){
        var val = $(this).val();
        var rate = <?= $rate ?>;    //后台给定

        $(".btn_money.on").removeClass("on");
        var amount = $(this).val();
        var _this = $(this);
        if( isNaN(amount) ){
            $.alert("充值金额必须为数字", function(){
                _this.val(10);
                $('#amount').val(10);
                val = 10;
                $(".real_count").html(val - Math.ceil( val * rate ));
            });
        }
        if(amount < 10){
            $.alert("充值金额不小于10元", function(){
                _this.val(10);
                $('#amount').val(10);
                val = 10;
                $(".real_count").html(val - Math.ceil( val * rate ));
            });
        }

        $('#amount').val(amount);
        $(".real_count").html(val - Math.ceil( val * rate ));
    });

    $('#payBtn').on('click', function(){
        var amount = $('#amount').val();
        if(!amount || isNaN(amount) || amount <= 0){
            alert('金额输入不合法!');
            return false;
        }
        $("#payform").submit();
    });

    $('.payType .paystyle').on('click', function(){
        var type = $(this).data('type');
        $('.payType .paystyle').each(function(){
            if (type == $(this).data('type')) {
                $(this).find('.checkPay').attr({"src":"/images/seleted.png"});
            } else {
                $(this).find('.checkPay').attr({"src":"/images/notseleted.png"});
            }
        });
        $('#type').val(type);
    });

})
</script>
