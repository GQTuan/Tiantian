<?php use common\helpers\Html; ?>

<?= $html ?>

<p class="cl pd-5 mt-20">
    <span>赠送总金额：<?= Html::redSpan($amount, ['class' => 'countAmount']) ?></span>
</p>