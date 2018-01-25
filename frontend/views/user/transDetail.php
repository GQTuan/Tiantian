<?php common\components\View::regCss('jilu.css') ?>
<!-- <p class="charge-header" style="border:1px solid #ccc;"> 
<a href="javascript:window.history.back()" style="float: left;">
<img src="/images/arrow-left.png" style="width:40px;"></a>
<span>我的商品轨迹</span>
</p> -->

<style type="text/css">
    .header {
        padding: 0px 5px;
        text-align: center;
        position: relative;
        height: 45px;
        line-height: 45px;
        color: #fff;
        font-size: 18px;
        color: #666;
        border-bottom: 1px solid #ddd;
        background: #fff;
    }
    .header a {
        display: inline-block;
        position: absolute;
        top: 1px;
        left: 5px;
        color: #666;
    }
    .back_arrow{
        position: relative;
    }
    .back_arrow:after,.back_arrow:before{
        content: "";
        position: absolute;
        width: 12px;
        border-top: 2px solid #666;
        transform-origin: left;
        -webkit-transform-origin: left;
        top: 20px;
        left: 10px;
    }
    .back_arrow:after{
        transform:rotate(45deg);
        -webkit-transform:rotate(45deg);
    }
    .back_arrow:before{
        transform:rotate(-45deg);
        -webkit-transform:rotate(-45deg);
    }
</style>


<div class="header">
    <a href="javascript:history.go(-1)"> <span class="back_arrow"></span> </a>
    商品明细
</div>



<div class="recording">
    <div class="rec_content" id="bean_list">
        <ul>
            <?= $this->render('_transDetail', compact('data')) ?>
        </ul>
    </div>
    <?php if ($pageCount < 2): ?>
        <div class="deta_more" id="deta_more_div">没有更多了</div>
    <?php else: ?>
        <div class="addMany" style="text-align: center;">
            <a style="" type="button" value="加载更多" id="loadMore" data-count="<?= $pageCount ?>" data-page="1">加载更多</a>
        </div>
    <?php endif ?>
</div>
<script type="text/javascript">
$(".addMany").on('click', '#loadMore', function() {
    var $this = $(this),
        page = parseInt($this.data('page')) + 1;

    $.get('', {p:page}, function(msg) {
        $("#bean_list>ul").append(msg.info);
        $this.data('page', page);
        if (page >= parseInt($this.data('count'))) {
            $('.addMany').hide();
        }
    });
});
</script>