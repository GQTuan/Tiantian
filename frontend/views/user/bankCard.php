<?php $this->regCss('main') ?>
<style type="text/css">
    html{
        height: 100vh;
        background: #F4F4F4;
    }
    .form-group{
        position: relative;
        height: 50px;
        line-height: 50px;
        background: #fff;
    }
    .form-group+.form-group{
        margin-top: 12px;
    }
    .form-group:before{
        content:"开户银行";
        font-size:14px;
        color: #828284;
        display: inline-block;
        width: 90px;
        margin-left: 10px;
        margin-right: 10px;
        text-align: right;
    }
    .form-group:nth-child(3):before{
        content:"身份证号";
    }
    .form-group:nth-child(4):before{
        content:"银行卡号";
    }
    .form-group:nth-child(5):before{
        content:"持卡人姓名";
    }
    .form-group:nth-child(6):before{
        content:"预留手机号";
    }
    .form-group:nth-child(7):before{
        content:"短信验证码";
    }
    .form-group input{
        font-size: 14px;
        color: #474747;
        padding-left: 10px;
    }
    .code.fr{
        position: absolute;
        height: 30px;
        line-height: 30px;
        right: 12px;
        top: 321px;
        color: #828284;
        background: transparent;
    }
    #submitBtn{
        color: #fff;
        font-size: 15px;
        background: #00E5B8;
        width: 90%;
        height: 44px;
        line-height: 44px;
        border-radius:6px;
        margin-left: 5%;
        margin-top: 30px;
    }
    #submitBtns{
        color: #fff;
        font-size: 15px;
        background: #e63234;
        width: 90%;
        height: 44px;
        line-height: 44px;
        border-radius:6px;
        margin-left: 5%;
        margin-top: 30px;
    }
</style>
<?php $form = self::beginForm(['showLabel' => false]) ?>
    <?= $form->field($bankCard, 'bank_name')->dropDownlist() ?>
    <?= $form->field($bankCard, 'id_card')->textInput(['placeholder' => '请输入身份证号']) ?>
    <?= $form->field($bankCard, 'bank_card')->textInput(['placeholder' => '请输入银行卡号（借记卡）']) ?>
    <?= $form->field($bankCard, 'bank_user')->textInput(['placeholder' => '请输入持卡人姓名']) ?>
    <?= $form->field($bankCard, 'bank_mobile')->textInput(['placeholder' => '请输入银行卡预留手机号']) ?>
    <button type="submit" id="submitBtn" class=" col-xs-12 navbar-fixed-bottom text-center footer_bg font_16">提交绑定</button>
<?php self::endForm() ?>
<button type="submit" id="submitBtns" class=" col-xs-12 navbar-fixed-bottom text-center footer_bg font_16">解除绑定</button>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    $.alert(msg.info);
                } else {
                    $.alert(msg.info, function(){
                    window.location.href = '<?= url('user/index') ?>'
                    });
                }
            }
        }));
        return false;
    });
    $("#submitBtns").click(function () {
        $.post("<?= url(['user/deleteBank']) ?>",function(msg){
            if(msg.state) {
                $.alert(msg.info, function(){
                    window.location.href = '<?= url('user/index') ?>'
                });
            }else {
                $.alert(msg.info);
            }
        })
    });    
});
</script>