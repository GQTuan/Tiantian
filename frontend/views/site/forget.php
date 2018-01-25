<?php $this->regCss('yanzheng.css') ?>

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


<div style="background:transparent" class="header">
    <a href="javascript:history.go(-1)"> <span class="back_arrow"></span> </a>
    忘记密码
</div>



<div class="forget-box">
    <?php $form = self::beginForm(['showLabel' => false]) ?>
    <!-- <div class="title">忘记密码</div> -->
    <div class="content-wrap">
        <?= $form->field($model, 'mobile')->textInput(['placeholder' => '请输入您的手机号码', 'class' => 'textvalue regTel'])  ?>
        <div class="textcode" id="verifyCodeBtn" data-action="<?= url(['site/verifyCode']) ?>"  class="textvalue yzbtn">获取验证码</div>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => '请输入6~12位密码', 'class' => 'textvalue']) ?>
        <?= $form->field($model, 'cfmPassword')->passwordInput(['placeholder' => '确认密码', 'class' => 'textvalue']) ?>
        <?= $form->field($model, 'verifyCode')->textInput(['placeholder' => '请输入手机验证码', 'class' => 'textvalue regCode'])  ?>
        <p id="errorMsg"></p>
        <a class="btn-sure disabled" id="submitBtn">确定</a>
    </div>
    <?php self::endForm() ?>
</div>

<script>
$(function () {
    var $inputs = $('.regTel');
    $inputs.keyup(function() {
        if ($inputs.val().length >= 11) {
            $('#submitBtn').removeClass('disabled');
        } else {
            $('#submitBtn').addClass('disabled');
        }
    });
    //倒计时
    var wait = 60;
    function time(obj) {
        if (wait == 0) {
            obj.removeClass('disabled');           
            obj.html('重新获取验证码');
            wait = 60;
        } else {
            obj.addClass('disabled');
            obj.html('重新发送(' + wait + ')');
            wait--;
            setTimeout(function() {
                time(obj);
            },
            1000)
        }
    }
    //提交
    $("#submitBtn").click(function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    $.alert(msg.info);
                } else {
                    window.location.href = msg.info;
                }
            }
        }));
        return false;
    });
    // 验证码
    $("#verifyCodeBtn").click(function () {
        if ($(this).hasClass('disabled')) {
            return false;
        }
        var mobile = $('.regTel').val();
        var url = $(this).data('action');
        if (mobile.length != 11) {
            $.alert('您输入的不是一个手机号！');
            return false;
        }
        $.post(url, {mobile: mobile}, function(msg) {
                if (msg.state) {
                    time($('#verifyCodeBtn'));
                } else {
                    $.alert(msg.info);
                }
        }, 'json');
    });
});
</script>      