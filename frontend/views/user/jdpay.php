<style type="text/css">
    .main{
        background: #fff;
        width:100vw;
        height:100vh;
    }
    .top-tip{
        font-size:36px;
        color:#faad0e;
        height:100px;
        line-height: 100px;
        text-align: center;
        padding-left: 7px;
        background-size: 40px 40px;
    }
    .welcome{
        width:250px;
        margin:0 auto;
        height:34px;
        line-height: 33px;
        text-align: center;
        font-size:20px;
        color:#faad0e;
        background: #fff;
        margin-top:-24px;
        border-radius:18px;
        border:1px solid #F8BF47;
    }
    .img-content{
        width:225px;
        margin:0 auto;
        height:225px;
        margin-top:25px;
        border:1px solid #F8BF47;
    }
    .img-content img{
        width:100%;
    }
    .notice{
        text-align: center;
        margin-top:50px;
    }
    .notice>span{
        border-bottom:1px dashed #FAAE14;
        color:#201e23;
        font-size:24px;
        padding-bottom:10px;
    }
    .back-btn{
        display: block;
        width: 150px;
        height: 40px;
        border-radius:20px;
        margin:0 auto;
        background: #FAAD0E;
        text-align: center;
        line-height: 40px;
        font-size:22px;
        color:#fff;
        margin-top:20px;
    }
</style>
 <div class="main">
     <div class="top-tip">
        <img style="width:40px;" src="/images/QQ.png">
         JD扫码支付
     </div>
    <div class="main-content">
        <h1 class="welcome">欢迎使用JD支付</h1>
        <div class="img-content">
            <img src="<?= $src ?>">
        </div>
        <div class="notice">
            <span>JD扫码，<span><?= $amount ?></span>元 </span>
        </div>
    </div>
 </div>
