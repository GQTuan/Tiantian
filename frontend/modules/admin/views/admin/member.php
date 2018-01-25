<?= $html ?>

<script>
$(function () {   
    $(".list-container").on('click', '.editBtn', function () {
        var $this = $(this);
        $.prompt('请输入修改的返点', function (value) {
            $.post($this.attr('href'), {point: value}, function (msg) {
                if (msg.state) {
                    location.replace(location.href);
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });   
    $(".list-container").on('click', '.feeWithdraw', function () {
        var $this = $(this);
        $.prompt('请输入手续费金额', function (value) {
            $.post($this.attr('href'), {fee: value}, function (msg) {
                if (msg.state) {
                    location.replace(location.href);
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });   
    $(".list-container").on('click', '.depositWithdraw', function () {
        var $this = $(this);
        $.prompt('请输入保证金数目(负数扣除)', function (value) {
            $.post($this.attr('href'), {deposit: value}, function (msg) {
                if (msg.state) {
                    location.replace(location.href);
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });
    $(".list-container").on('click', '.editBtnPassword', function () {
        var $this = $(this);
        $.prompt('请输入修改的密码', function (value) {
            $.post($this.attr('href'), {password: value}, function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '修改成功');
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });
});
</script>
