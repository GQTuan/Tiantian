<?php use common\helpers\Html;
      use frontend\models\Product; ?>
<?php $this->regCss('iconfont/iconfont.css') ?>
<?php $this->regCss('list.css') ?>
<?php $this->regCss('manager.css') ?>
<script src="http://res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<div id="main">
    <div class="twoDCode-box">
        <div class="codeImg">
            <img src="<?= $src ?>" id="codeImg" />
        </div>
        <div class="codeTxtDiv">
            <p class="codeTxt" id="codeImgTxt">扫扫二维码，马上体验微盘赚钱</p>
        </div>
    </div>
</div>
