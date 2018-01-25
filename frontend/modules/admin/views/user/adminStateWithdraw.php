<?php use common\helpers\Hui; ?>
<?php $form = self::beginForm() ?>
<?= $form->field($model, 'state')->dropDownlist() ?>
<div class="row cl" style="margin: 0 auto;padding-left: 40%">
<?= Hui::successBtn('确认', null, ['id' => 'submitBtn', 'class' => 'size-L']) ?>
</div>
<?php self::endForm() ?>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $.confirm('确认修改？', function () {
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
        });
        return false;
    });
});
</script>