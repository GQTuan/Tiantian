<style type="text/css">
    .main{
        background: #00AAEF;
        width:100vw;
        height:100vh;
        overflow: hidden;
        padding: 15px;
        position: fixed;
        top: 0;
        left: 0;
        box-sizing:border-box;
    }
    .top-tip{
        font-size:36px;
        color:#00aaef;
        height:100px;
        line-height: 100px;
        text-align: center;
        padding-left: 0px;
        background:#fff url()no-repeat 85px center;
        background-size: 40px 40px;
    }
    .welcome{
        width:250px;
        margin:0 auto;
        height:34px;
        line-height: 34px;
        text-align: center;
        font-size:20px;
        color:#00aaef;
        background: #fff;
        margin-top:30px;
        border-radius:18px;
    }
    .img-content{
        width:225px;
        margin:0 auto;
        height:225px;
        margin-top:25px;
    }
    .img-content img{
        width:100%;
    }
    .notice{
        text-align: center;
        margin-top:50px;
    }
    .notice>span{
        border-bottom:1px dashed #FDFEFF;
        color:#fff;
        font-size:20px;
        padding-bottom:0px;
    }
    .back-btn{
        display: block;
        width: 150px;
        height: 40px;
        border-radius:20px;
        margin:0 auto;
        background: #fff;
        text-align: center;
        line-height: 40px;
        font-size:22px;
        color:#00aaef;
        margin-top:20px;
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
        color: #fff;
        border-bottom: 1px solid #ddd;
        background: #fff;
    }
    .header a {
        display: inline-block;
        position: absolute;
        top: 1px;
        left: 5px;
        color: #fff;
    }
    .back_arrow{
        position: relative;
    }
    .back_arrow:after,.back_arrow:before{
        content: "";
        position: absolute;
        width: 12px;
        border-top: 2px solid #fff;
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




 <div class="main">
    <div style="background:transparent;border:none;" class="header">
        <a href="javascript:history.go(-1)"> <span class="back_arrow"></span> </a>
        支付
    </div>

    <div class="main-content" style="margin-top: -30px;">
        <h1 class="welcome">欢迎使用支付宝支付</h1>
        <div class="img-content">
            <img src="<?= $src ?>">
        </div>
        <div class="notice">
            <span>先保存二维码，再用支付宝扫一扫--选择相册--点击保存好的二维码支付<span style="color:red"><?= $amount?></span>元，请不要长按二维码直接支付 </span>
        </div>
    </div>
 </div>
