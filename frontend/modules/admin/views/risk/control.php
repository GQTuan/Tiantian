<?php use common\helpers\Hui; ?>
<?php use common\helpers\Html; ?>

<?php $form = self::beginForm() ?>
<div id="riskArea">
    <table class="table table-border table-hover">
        <tr>
            <th>产品</th>
            <th>涨跌人数比</th>
            <th>涨跌金额比</th>
            <th>设定价格</th>
            <th>设定秒数</th>
            <th>倒计时</th>
        </tr>
        <?php foreach ($products as $product): ?>
        <tr>
            <th>
                <h5><?= $product['name'] ?>（当前价格：<?= $product['dataAll']['price'] ?>）</h5>
            </th>
            <td id="tdNum<?= $product['table_name'] ?>"><?= Html::redSpan($orderInfo[$product['table_name']]['riseNum']) ?>：<?= Html::greenSpan($orderInfo[$product['table_name']]['downNum']) ?></td>
            <td id="tdAmount<?= $product['table_name'] ?>"><?= Html::redSpan($orderInfo[$product['table_name']]['riseAmount']) ?>：<?= Html::greenSpan($orderInfo[$product['table_name']]['downAmount']) ?></td>
            <td>
                <input type="text" name="product[<?= $product['table_name'] ?>][]" class="input-text" placeholder="目标价格">
            </td>
            <td class="text-r">
                <input type="text" name="product[<?= $product['table_name'] ?>][]" class="input-text restTime" placeholder="指定秒数">
                <input type="hidden" name="product[<?= $product['table_name'] ?>][]" value="<?= $product['dataAll']['price'] ?>">
            </td>
            <td>
                
            </td>
        </tr>
        <?php endforeach ?>
        <tr>
            <td colspan="6" class="text-c"><?= Hui::secondaryBtn('保存', null, ['id' => 'submitBtn', 'class' => 'size-L']) ?></td>
        </tr>
    </table>
</div>
<?php self::endForm() ?>

<script>
$(function () {
    ;!function () {
        var ajaxInfo = function () {
            $.get('<?= url(['orderInfo']) ?>', function (msg) {
                var data = msg.info;
                for (var table in data) {
                    $("#tdAmount" + table).html('<span style="color:#E31;">' + data[table]['riseAmount'] + '</span>：<span style="color:#5C5;">' + data[table]['downAmount']  + '</span>');
                    $("#tdNum" + table).html('<span style="color:#E31;">' + data[table]['riseNum'] + '</span>：<span style="color:#5C5;">' + data[table]['downNum'] + '</span>');
                }
                setTimeout(ajaxInfo, 1000);
            }, 'json');
        };
        setTimeout(ajaxInfo, 1000);
    }();
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '操作成功', function () {
                        $(".restTime").each(function () {
                            var sec = parseInt($(this).val());
                            if (sec > 0) {
                                var count = function ($td, sec) {
                                    if (sec > 0) {
                                        $td.html(--sec);
                                        setTimeout(function () {
                                            count($td, sec);
                                        }, 1000);
                                    }
                                };
                                count($(this).parent().next(), sec);
                            }
                        });
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