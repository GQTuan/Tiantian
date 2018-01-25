<?= $html ?>

<script>
$(function () {   
    $(".list-container").on('click', '.updateState', function () {
        var $this = $(this);
        $.post($this.attr('href'), {point: value}, function (msg) {
            if (msg.state) {
                location.replace(location.href);
            } else {
                $.alert(msg.info);
            }
        }, 'json');
        return false;
    });   
});
</script>