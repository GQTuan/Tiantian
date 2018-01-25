<?php foreach ($data as $order) :?>
<li>
    <div class="title">【已平仓】</div>
    <div class="boxflex">
        <div class="keyvalue boxflex box_flex_1">
            <label>商品：</label><span class="box_flex_1"><?= $order->product->name ?></span></div>
        <div class="keyvalue boxflex box_flex_1">
            <label>数量：</label><span class="box_flex_1"><?= $order->hand ?></span></div>
    </div>
    <div class="boxflex">
        <div class="keyvalue boxflex box_flex_1">
            <label>建仓价：</label><span class="box_flex_1"><?= $order->price ?></span></div>
        <div class="keyvalue boxflex box_flex_1">
            <label>建仓时间：</label><span class="box_flex_1"><?= $order->created_at ?></span></div>
    </div>
    <div class="boxflex">
        <div class="keyvalue boxflex box_flex_1">
            <label>定金：</label><span class="box_flex_1"><?= $order->deposit ?></span></div>
        <div class="keyvalue boxflex box_flex_1">
            <label>止盈止损点数：</label><span class="box_flex_1"><?= $order->stop_profit_price ?>（<?= $order->riseFallValue ?>）</span></div>
    </div>
    <div class="boxflex">
        <div class="keyvalue boxflex box_flex_1">
            <label>平仓价：</label><span class="box_flex_1"><?= $order->sell_price ?></span></div>
        <div class="keyvalue boxflex box_flex_1">
            <label>平仓时间：</label><span class="box_flex_1"><?= $order->updated_at ?></span></div>
    </div>
    <div class="boxflex">
        <div class="keyvalue boxflex box_flex_1">
            <?php $text = '止损';$str = '亏';$class='down'; $profit=$order->fee - $order->deposit; if ($order->profit > 0) {$text = '止盈';$profit = -$profit;$str = '赚';$class = 'up';} elseif ($order->profit == 0) { $text = '保本';$profit = 0;$str = '平';$class = 'up';} ?>
            <label>平仓类型：</label><span class="box_flex_1"><?= $text ?></span></div>
        <div class="keyvalue boxflex box_flex_1">
            <label>手续费：</label><span class="box_flex_1"><?= $order->fee ?></span></div>
    </div>
    <div class="earn-wrap earn-<?= $class ?>"><span class="dearn"><?= $str ?></span><span class="profit"><?= $profit ?></span></div>
</li>
<?php endforeach ?>