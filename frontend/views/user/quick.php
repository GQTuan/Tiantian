<style type="text/css">
    body{
        padding-top: 60px;
    }
    .my-from-group label{
        font-size: 16px;
        color: #333333;
        display: inline-block;
        width: 65px;
    }
    .my-from-group{
        margin-bottom: 30px;
        padding: 0 25px;
    }
    .my-from-group input{
        height: 40px;
        border-radius: 4px;
        border:1px solid #B6B6B6;
        width: calc(100% - 70px);
        font-size: 14px;
        color: #999999;
        padding: 0 15px;
        box-sizing:border-box;
    }
    .my-from-group .recharge-btn{
        width: 100%;
        text-align: center;
        height: 45px;
        line-height: 45px;
        border-radius: 6px;
        background: #FDA237;
        color: #fff;
        font-size: 20px;
        margin-top: 70px;
    }
    .mask{
        width: 100vw;
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        background: #fff url(../images/loading.gif) no-repeat center 30vh;
        background-size: 80px 80px;
        display: none;
    }
</style>

<div>
    <div class="my-from-group">
    <label>流水号：</label><input type="text" id="signSn" name="signSn" value="<?= $res[0]['data'] ?>" disabled="disabled">
    </div>
    <div class="my-from-group">
    <label>验证码：</label><input type="text" id="code" name="code">
    </div>
    <input type="hidden" id="orderId" name="orderId" value="<?= $res[1] ?>">
</div>
<div class="recharge-btn-cotnainer">
<div class="my-from-group">
     <div class="recharge-btn" id="payBtn">立即充值</div>
 </div>
</div>
<div class="mask"></div>

<script src="/js/jquery-1.10.1.min.js" type="text/javascript"></script>
<script>
$(function() {
    $('#payBtn').on('click', function(){
        var data = {};
        data.code = $('#code').val();
        if(!data.code || isNaN(data.code) || data.code <= 0){
            alert('短信输入不合法!');
            return false;
        }
        data.signSn = $('#signSn').val();
        data.orderId = $('#orderId').val();
        $(".mask").css("display","block");
        $.post("<?= url(['user/ajaxQuick']) ?>", {data:data}, function(msg){
            if (msg.state) {
                alert("支付成功！");
                window.location.href = '<?= url(['user/index']) ?>';
            } else {
                alert(msg.info);
                $(".mask").css("display","none");
            }
        })
    });
})

</script>