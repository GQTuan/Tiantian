<?php use common\helpers\Hui; ?>

<?php $form = self::beginForm() ?>
<?= $form->field($model, 'username')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'realname')->textInput(['disabled' => 'disabled']) ?>
<?= $form->field($model, 'mobile')?>
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
});
</script>