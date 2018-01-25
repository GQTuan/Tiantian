<?php use common\helpers\Hui; ?>
<?php $form = self::beginForm() ?>
<?= $form->field($adminAccount, 'id_card')->textInput() ?>
<?= $form->field($adminAccount, 'bank_name')->dropDownlist() ?>
<?= $form->field($adminAccount, 'bank_card')->textInput() ?>
<?php if(!empty($adminAccount->bank_user)): ?>
<?= $form->field($adminAccount, 'bank_user')->textInput(['readonly' => 'readonly']) ?>
<?php else: ?>
<?= $form->field($adminAccount, 'bank_user')->textInput() ?>   
<?php endif; ?>
<?= $form->field($adminAccount, 'bank_mobile')->textInput() ?>
<?= $form->field($adminAccount, 'bank_address')->textInput() ?>
<?= $form->field($retailWithdraw, 'amount')->textInput() ?>
<div class="row cl" style="margin: 0 auto;padding-left: 40%">
<?= Hui::successBtn('确认', null, ['id' => 'submitBtn', 'class' => 'size-L']) ?>
</div>
<?php self::endForm() ?>
<script>
$(function () {
    $("#submitBtn").click(function () {
            $("form").ajaxSubmit($.config('ajaxSubmit', {
                success: function (msg) {
                    if (msg.state) {
                        $.alert(msg.info || '操作成功', function () {
                            parent.location.reload();
                        })
                    } else {
                        $.alert(msg.info);
                    }
                }
            }));
        return false;
    });
    });
</script>