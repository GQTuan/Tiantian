<?php $form = self::beginForm() ?>
<?= $user->title('用户') ?>
<?= $form->field($user, 'username') ?>
<?= $form->field($user, 'realname') ?>
<?= $form->field($user, 'mobile') ?>
<?= $form->field($user, 'password')->textInput(['placeholder' => $user->isNewRecord ? '' : '不填表示不修改']) ?>
<?= $form->field($user, 'power')->dropDownlist()->label('用户类型') ?>
<div class="row cl myPid">
    
</div>
<?= $form->submit($user) ?>
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
    //用户类型的切换
    $('#adminuser-power').on('change', function() {
        var power = $(this).val();
        $.post("<?= url(['admin/ajaxSubUser']) ?>", {power: power}, function(msg) {
            if (msg.state) {
                $('.myPid').html(msg.info);
            } else {
                $.alert(msg.info);
            }
        }, 'json');
    });
});
</script>