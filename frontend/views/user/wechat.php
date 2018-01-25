<style type="text/css">
    .pay-title{
        font-size:41px;
        color:#81b826;
        text-align: center;
        margin-bottom:10px;
        margin-top:40px;
    }
    .pay-title img{
        width:55px;
        vertical-align: middle;
        margin-right:8px;
        position: relative;
        top:-3px;
    }
    .welcome{
        width:240px;
        margin:0 auto;
        border:1px solid #81B826;
        border-radius:15px;
        height:32px;
        line-height:32px;
        text-align: center;
        color:#81B827;
        font-size:20px;
    }
    .img-content{
        display: block;
        background:url(/images/ewm.png) no-repeat center center;
        background-size:163px;
        width:160px;
        height:160px;
        padding:15px;
        margin:0 auto;
        margin-top:35px;
    }
    .img-content img{
        width:100%;
        height:100%;
    }
    .notice{
        font-size:23px;
        color:#565656;
        width:270px;
        margin:0 auto;
        border-bottom:1px dashed #434343;
        margin-top:10px;
        padding-bottom:10px;
    }
    .bold{
        font-size:30px;
    }
    .acount{
        display: inline-block;
        width:60px;
        color:#F54A4A;
        text-align: center;
    }
    .back-btn{
        display: block;
        width:150px;
        height: 30px;
        border-radius:15px;
        background: #F64A4A;
        line-height: 30px;
        text-align: center;
        color:#fff;
        font-size:20px;
        text-decoration: none;
        margin:0 auto;
        margin-top:20px;
    }
    .notice{
        font-size:12px;
    }
</style>



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

<div class="order">
    <h1 class="pay-title"><img src="/images/ic.png">微信支付</h1>
    <p class="welcome">欢迎使用微信支付</p>
    <div  class="img-content">
        <img src="<?= $src ?>">
    </div>
    <div class="notice" >
    请按如下步骤操作：<br>
    <p>方法一：请保存本支付二维码，然后用另一部手机打开微信扫一扫，完成支付；</p>
    <p>方法二：将本支付二维码发送至电脑端或者其他的微信、QQ 等，然后用本机微信扫一扫，完成支付</p>
   <!-- <p>3.点击右上角功能键选择从相册选取本次充值支付二维码</p>
    <p>4.完成支付</p> -->
    </div>
</div>
