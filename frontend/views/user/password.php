<?php common\components\View::regCss('yanzheng.css') ?>
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
    修改密码
</div>

<div class="forget-box">
    <div class="title">修改商品密码</div>
    <div class="content-wrap">
    <?php $form = self::beginForm(['showLabel' => false]) ?>
        <?= $form->field($model, 'oldPassword')->passwordInput(['placeholder' => '请输入原密码', 'class' => 'textvalue'])?>
        <?= $form->field($model, 'newPassword')->passwordInput(['placeholder' => '请输入6-18位字母或数字', 'class' => 'textvalue']) ?>
        <?= $form->field($model, 'cfmPassword')->passwordInput(['placeholder' => '请再次输入密码', 'class' => 'textvalue']) ?>
        <a class="btn-sure" id="submitBtn">确定</a>
    <?php self::endForm() ?>
    </div>
</div>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    $.alert(msg.info);
                } else {
                    $.alert(msg.info);
                    window.location.href = '<?= url(['user/index']) ?>'
                }
            }
        }));
        return false;
    });
});
</script>