<?php use common\helpers\Html; ?>

<?= $html ?>

<p class="cl pd-5 mt-20">
    <span>当前总共充值了<span class="count" style="color:#E31;"><?= $count ?></span>元</span>
</p>
<?php if (u()->power >= 9999): ?>
<a class="userExcel btn btn-success radius r">导出充值记录</a>
<?php endif ?>
<script type="text/javascript">
    $(".userExcel").on('click', function () {
        var str = '';
        $('.search-form ul>li').each(function(){
            var $this = $(this).find('.input-text');
            if ($this.attr('name') != undefined) {
                var value = $this.val();
                if (value.length > 0) {
                    // var arr = $this.attr('name').split('['),               
                    //     arr = arr[1].split(']');               
                    // str += arr[0] + '=' + value + '&';             
                    str += $this.attr('name') + '=' + value + '&';
                }
            }
        });
        var url = "<?= url(['user/userChargeExcel']) . '?admin.id=&' ?>" + str;
        window.location.href = url;
    });
</script>