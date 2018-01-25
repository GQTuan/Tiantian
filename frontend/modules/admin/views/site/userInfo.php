<?php use common\helpers\Hui; ?>

<?php $form = self::beginForm() ?>
<?= $form->field($model, 'realname')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'tel') ?>
<?= $form->field($model, 'point')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'total_fee')->textInput(['disabled' => 'disabled']) ?>
<?php if (u()->power == admin\models\AdminUser::POWER_SETTLE || u()->power == admin\models\AdminUser::POWER_MEMBER || u()->power == admin\models\AdminUser::POWER_OPERATE): ?>
<?= $form->field($model, 'deposit')->textInput(['disabled' => 'disabled']) . Hui::primaryBtn('充值保证金', ['adminCharge', 'id' => u()->id], ['class' => 'editBtn']) ?>
<?php endif ?>
<?php if (u()->power == admin\models\AdminUser::POWER_RING): ?>
<?= $form->field($model, 'code')->textInput(['disabled' => 'disabled']) ?>
<?php endif ?>
<?= $form->submit($model) ?>
<?php self::endForm() ?>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (msg.state) {
                    $.alert('操作成功', function () {
                        parent.location.reload();
                    });
                } else {
                    $.alert(msg.info);
                }
            }
        }));
        return false;
    });
        $(".editBtn").click(function (e) {
        e.preventDefault();
        var $this = $(this);
        $.prompt('请输入充值金额', function (value) {
            $.post($this.attr('href'), {amount: value}, function (msg) {
                if (msg.state) {
                    //$.alert(msg.info);
                    // location.replace(location.href);
                    layer.open({
                        type: 1,
                        title: '微信支付',
                        shadeClose: true,
                        shade: 0.8,
                        area: ['60%', '60%'],
                        content: msg.info,
                    });
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });
});
</script>