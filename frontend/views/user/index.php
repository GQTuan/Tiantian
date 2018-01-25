<?php $this->regCss('geren.css') ?>
<?php $this->regCss('jiaoyi.css') ?>
<?php use frontend\models\User; ?>
        <!--个人中心-->
        <div class="personal">
            <div class="per_top">
                <div class="boxflex">
                    <div class="img-wrap"><img class="userimage" src="<?= u()->face ?>"></div>
                    <div class="box_flex_1">
                        <div class="p_zichan">资产：<span id="total-asset"><?= sprintf('%.2f', $user->account) ?></span>元</div>
                    </div>
                    <div class="btncenter-withdraw-wrap">
                        <div class="recharge"><a class="overallPsd" data-url="<?= url(['user/recharge']) ?>">充值</a></div>
                        <div class="withdraw"><a class="overallPsd" data-url="<?= url(['user/withDraw']) ?>">提现</a></div>
                    </div>
                </div>
                <div class="boxflex cash-wrap">
                    <div class="box_flex_1">
                        <div class="p_zijin">
                            <div class="key" id="able-cash"><?= $user->account - $user->blocked_account ?></div>
                            <div class="value">可用资金</div>
                        </div>
                    </div>
                    <div class="box_flex_1">
                        <div class="p_zijin">
                            <div class="key" id="used-cash"><?= $user->blocked_account ?></div>
                            <div class="value">占用合约定金</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="center-list-wrap">
                <ul>
                    <li class="table" data-index="0">
                        <a href="<?= url(['user/transDetail']) ?>">
                        <i class="icon icon-operrec table-cell"></i>
                        <span class="table-cell title-text">我的商品轨迹</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>
<!--                     <li class="table" data-index="0">
                        <a href="<?= url(['order/position']) ?>">
                        <i class="icon icon-position table-cell"></i>
                        <span class="table-cell title-text">我的持仓单</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li> -->
                    <li class="table" data-index="1">
                        <a href="<?= url(['user/insideMoney']) ?>">
                        <i class="icon icon-expenditure table-cell"></i>
                        <span class="table-cell title-text">出金记录</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>
                    <li class="table" data-index="1">
                        <a href="<?= url(['user/outMoney']) ?>">
                        <i class="icon icon-income table-cell"></i>
                        <span class="table-cell title-text">入金记录</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>
                    <!--<li class="table" data-index="1">
                        <a href="<?/*= url(['user/bankCard']) */?>">
                        <i class="icon icon-income table-cell"></i>
                        <span class="table-cell title-text">绑定银行卡</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>-->
                    <li class="table" data-index="3">
                        <a href="<?= url(['user/setting']) ?>">
                            <i class="icon icon-setting table-cell"></i>
                            <span class="table-cell title-text">个人设置</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>                        
                    </li>
                    <li class="table" data-index="1">
                        <a href="<?= url(['manager/index']) ?>">
                        <i class="icon icon-customer table-cell"></i>
                        <span class="table-cell title-text"><?= $manager ?></span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>
                    <li class="table" data-index="1">
                        <a href="<?= url(['site/logout']) ?>">
                        <i class="icon icon-out table-cell"></i>
                        <span class="table-cell title-text">退出登录</span>
                        </a>
                        <span class="earrow earrow-right table-cell"></span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="myContent">

        </div>


<div class="myButtom">
        <div class="holdlist-wrap">
            <ul class="clear-fl footer-nav">
                <li><a href="/site/shop">
                    资讯
                </a></li>
                <li class=""><a href="/site/index">
                    交易
                </a></li>
                <li class="active"><a href="/user/index">
                    我的
                </a></li>
            </ul>
        </div>
    </div>