<?= $html ?>

<script>
$(function () {
    $(".list-container").on('click', '.giveBtn', function () {
        var $this = $(this);
        $.prompt('请输入经纪人ID', function (value) {
            $.post($this.attr('href'), {pid: value}, function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '更改成功', function () {
                        location.reload();
                    });
                } else {
                    $.alert(msg.info);
                }
            }, 'json');
        });
        return false;
    });

    $(".list-container").on('click', '.ringBtn', function () {
        var $this = $(this);
        $.prompt('请输入微圈的ID', function (value) {
            $.post($this.attr('href'), {admin_id: value}, function (msg) {
                if (msg.state) {
                    $.alert(msg.info || '更改成功', function () {
                        location.reload();
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