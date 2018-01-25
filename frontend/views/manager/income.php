<?php $this->regCss('manager.css') ?>
<?php common\assets\DatePickerAsset::register($this) ?>


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
    收入
</div>




    <div id="main">
        <div class="querylist-box" style="visibility: visible;"><div class="queryheader-wrap">
            <div class="queryheader">
                <i class="icon icon-income"></i>
                <span class="headervalue">返佣金额:<span class="redsymbol"><?= $extend->rebate_account ?>元</span></span>
                <span style="font-size: 10px; margin-left: 10px; color: #bbb;">注：以实际到账为准</span>
            </div>
            <?php $form = self::beginForm(['method' => 'get']) ?>
            <div class="condition">
                <div class="time boxflex">
                    <div class="key">日期:</div>
                    <div class="dateInput value box_flex_1">
                        <?= $form->field($model, 'start_date')->textInput(['type' => 'date', 'class' => 'laydate-icon', 'placeholder' => '请输入起始日期']) ?>
                    </div>
                </div>
                <div class="time boxflex">
                    <div class="key">至:
                    </div>
                    <div class="dateInput value box_flex_1">
                        <?= $form->field($model, 'end_date')->textInput(['type' => 'date', 'class' => 'laydate-icon', 'placeholder' => '请输入结束日期']) ?>
                    </div>
                </div>
            </div>
            <div class="btn-queryheader">
                <div class="btn-query"><button class="btn btn-45-24-blue" id="searchBtn" type="submit">查询</button>
                </div>
            </div>
            <?php self::endForm() ?>
        </div>
        <div class="listwrap">
            <div class="boxflex header">
                <div class="name box_flex_1">客户昵称
                </div>
                <div class="good box_flex_1">商品
                </div>
                <div class="money box_flex_1">返佣金额
                </div>
                <div class="time box_flex_1">时间
                </div>
            </div>
            <?= $this->render('_income', compact('data')) ?>

            <?= self::linkPager() ?>
            <div class="iscroll-wrap" style="height: 382px;">
                <div class="iscroll-content" style="transition-timing-function: cubic-bezier(0.1, 0.57, 0.1, 1); transition-duration: 0ms; transform: translate(0px, 0px) translateZ(0px); min-height: 383px;">
                    <ul style="min-height: 383px;">
                        <li class="data-empty">当前查询记录为<?= $count ?>条</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>