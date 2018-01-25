<?php use common\helpers\Hui; ?>
<?php admin\assets\LoginAsset::register($this) ?>
<style type="text/css">
    .form-container{
        position:relative;
        width:360px;
        height:40px;
        background: #fff;
        margin-left:163px;
    }
    #loginform-verifycode{
        position: absolute;
        left:0;
        top:0;
        left: -50px;
        width: 250px!important;
        border:0;
        outline: 0;
        height:40px;
    }
    .textcode.verifyCodeBtn{
        position: absolute;
        right:0;
        height: 40px;
        line-height: 40px;
        top: 0;
        padding-right: 20px;
        font-size: 12px;
        color: #666;
        cursor:pointer;
    }
    .textcode.verifyCodeBtn:hover{
        color:#e4393c;
        box-shadow: 0 0 94px rgba(255,0,0,.4) inset;
        text-align: center;
        padding-left: 10px;
    }
</style>
<div class="header"></div>
<div class="login-wraper">
    <div id="loginform" class="login-box">
    <?php $form = self::beginForm(['class' => ['form', 'form-horizontal']]) ?>
        <?= $form->field($model, 'username')->textInput(['placeholder' => $model->label('username'), 'class' => ['input-text', 'size-L']])->label('<i class="Hui-iconfont">&#xe60d;</i>', ['class' => ['form-label', 'col-xs-3']]) ?>
        <?= $form->field($model, 'password')->passwordInput(['placeholder' => $model->label('password'), 'class' => ['input-text', 'size-L']])->label('<i class="Hui-iconfont">&#xe60e;</i>', ['class' => ['form-label', 'col-xs-3']]) ?>
        <div class="form-container">
            <?= $form->field($model, 'verifyCode')->textInput(['placeholder' => '请输入手机验证码', 'style' => ['width' => '150px'], 'class' => ['input-text', 'size-L']])->label('')  ?>
            <div class="textcode verifyCodeBtn" data-action="<?= url(['site/verifyCode']) ?>">获取验证码</div>
        </div>
        <?= $form->submit('登 录', ['style' => ['width' => '100px'], 'class' => ['size-L', 'mt-20', 'mb-20']]) ?>
    <?php self::endForm() ?>
    </div>
</div>
<div class="footer"><?= config('web_copyright') ?></div>

<script>
$(function () {
    // 首次登陆隐藏验证码，登陆失败后才出现
    ;!function () {
        if ('<?= session('requireCaptcha') ?>') {
            $("#loginform-captcha").parents('.row').show();
        } else {
            $("#loginform-captcha").parents('.row').hide();
        }
    }();
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    $.alert(msg.info, function () {
                        $("#loginform-captcha").parents('.row').show();
                    });
                }
            }
        }));
        return false;
    });
    // 验证码
    $(".verifyCodeBtn").click(function () {
        var username = $('#loginform-username').val();
        var url = $(this).data('action');
        $.post(url, {username: username}, function(msg) {
            // if (msg.state) {
            //     time($('#verifyCodeBtn'));
            // } else {
                $.alert(msg.info);
            // }
        }, 'json');
    });
});
</script>
