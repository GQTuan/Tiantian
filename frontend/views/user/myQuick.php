<?php common\components\View::regJs('pay/paymentjs') ?>
<?php common\components\View::regJs('pay/jquery-1.7.2.min') ?>
<script>
    function wap_pay() {
        var responseText = $("#credential").text();
        console.log(responseText);
        paymentjs.createPayment(responseText, function(result, err) {
            console.log(result);
            console.log(err.msg);
            console.log(err.extra);
        }); 
    }
</script>
<div style="display: none" >

    <p id="credential"><?= $credential ?></p>
</div>

<script>
window.onload=function(){
    wap_pay();
};
</script>