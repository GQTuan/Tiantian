
<?php common\components\View::regCss('registered.css') ?>
<?php common\components\View::regCss('mobile.css') ?>

<?php $form = self::beginForm(['showLabel' => false]) ?>
<style>
    body{
        background:#fff;
    }
    a.in-aput-xz {
      border:1px solid #e4393c;
    }
    .tijiao{
      background: #A80000;
      border: 0;
      width: 80%;
    }
    .back-login{
      text-align: right;
      margin-right: 10%;
      margin-top: 10px;
    }

  .form-listbox label {
    /*text-align: right;*/
  }
</style>

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
    .load_app {
        padding: 0 10%;
        margin-top: 20px;
    }
    .load_app a {
        border: 2px solid #F15A5C;
        border-radius: 8px;
        padding: 8px 16px;
        font-size: 14px;
        color: #F15A5C;
    }
    .inp-font {
      width: 100%;
    }
    .lf {
        float: left;
    }
    .rt {
        float: right;
    }
</style>


<div style="background:transparent" class="header">
    <a href="javascript:history.go(-1)"> <span class="back_arrow"></span> </a>
    注册
</div>

<?php $form = self::beginForm(['showLabel' => false]) ?>
   <div class="wrap-content">
        <!-- <div class="form-listbox clearfix">
          <label class="no-star fl">交易平台<i id="jypt" class="wenh icon icon-question Question">?</i>
          </label>
        <div class="input-box">                                    
            <a href="javascript:;" id="platform_2"   class="in-aput in-aput-5  in-aput-xz">云交易</a>
            <input name="personalInformation.platform" id="platform" type="hidden" value="MT4">
        </div>
        </div>

        <div class="form-listbox clearfix">
          <label class="no-star fl">账户类型<i id="y_kf_common" class="wenh icon icon-question Question">?</i></label>
          <div class="input-box">
                                                     
                 <a href="javascript:;" id="account_type_2"  num="2" v="STD" class="in-aput-8 fl in-aput-xz" style="margin:0 0 0 3.8%;;">标准账户</a>
                 <input name="personalInformation.accountType" id="accountType" type="hidden" value="STD">
                <input id="tiaoType" name="tiaoType" type="hidden" value="STD,MIN">
          </div>
        </div> -->
               
         <div class="form-listbox clearfix">  
          <label class="fl">昵　　称</label>
          <div class="input-box">
          <?= $form->field($model, 'nickname')->textInput(['placeholder' => '为保障阁下的资金安全,请填写真实姓名', 'class' => 'inp-font fl', 'style' => 'font-size:12px'])  ?>
          </div>
        </div>
        <!-- <div class="form-listbox clearfix">
          <label class="no-star fl">称　　谓</label>
          <div class="input-box">
          <a  id="title_type_1" class="in-aput in-aput-4  fl sex" href="javascript:">先生</a>
          <a v="Ms" num="2" id="title_type_2" class="in-aput in-aput-5  fl in-aput-cl in-aput-xz sex" href="javascript:;">女士</a>
          <input type="hidden" value="Ms" id="title" name="personalInformation.title">
          </div>
        </div> -->

<!--         <div class="form-listbox tan-zin7 clearfix">
          <label class="fl">电子邮箱</label>
          <div class="input-box">
          <ul style="position: absolute; margin-top: 36px; min-width: 419px; visibility: hidden; z-index: 999;" class="autocomplete-container"></ul>
          <ul class="autocomplete-container" style="position: absolute; margin-top: 36px; min-width: 239px; visibility: hidden; z-index: 999;"></ul><input type="text" class="inp-font email-autocomplete" value="" size="70" id="email" name="personalInformation.email" placeholder="请输入您的邮箱" style="font-size: 12px">
          <p style="display: none;" id="texttipsEmail" class="number-tips lans">必需填写有效的电邮地址，<b>密码及重要信息</b>会通过电邮发出。</p>
          <div id="emailerror" style="height:0px;"></div>
          </div>
        </div>   -->
        <div class="form-listbox tan-zin8 clearfix">
          <label class="fl">邀请码</label>
          <div class="input-box">
            <div class="tel-po inp-font-tel-1">
               <span class=""><?= $form->field($model, 'code')->textInput(['placeholder' => '代理商邀请码', 'class' => 'inp-font fl'])  ?></span>
            </div>
          </div>
        </div>

        <div class="form-listbox tan-zin8 clearfix">
          <label class="fl">手机号码</label>
          <div class="input-box">
            <div class="tel-po inp-font-tel-1">
              <input type="hidden" id="mobilePhone_areaCodes" value="" name="personalInformation.mobilePhone_areaCodes">
               <span class=""><?= $form->field($model, 'mobile')->textInput(['placeholder' => '手机号', 'class' => 'inp-font fl'])  ?></span>
            </div>
          </div>
        </div>
       <?= $form->field($model, 'pid')->textInput(['hidden' => 'hidden'])  ?>
        <div class="form-listbox tan-zin8 clearfix">
          <label class="fl">密码</label>
          <div class="input-box">
            <div class="tel-po inp-font-tel-1">
              <input type="hidden" id="mobilePhone_areaCodes" value="" name="personalInformation.mobilePhone_areaCodes">
               <span class=""><?= $form->field($model, 'password')->passwordInput(['placeholder' => '请输入您的密码', 'class' => 'inp-font fl'])  ?></span>
            </div>
          </div>
        </div>
        

        <div class="form-listbox tan-zin8 clearfix">
          <label class="fl">再次输入密码</label>
          <div class="input-box">
            <div class="tel-po inp-font-tel-1">
              <input type="hidden" id="mobilePhone_areaCodes" value="" name="personalInformation.mobilePhone_areaCodes">
               <span class=""><?= $form->field($model, 'cfmPassword')->passwordInput(['placeholder' => '请再次输入您的密码', 'class' => 'inp-font fl'])  ?></span>
            </div>
          </div>
        </div>

        <div style="display:none;" id="cookiecaptcha" class="form-listbox tan-zin10 clearfix">
          <label class="fl">图片验证</label>
          <div class="input-box">
          <input type="text" size="35" maxlength="4" id="captcha" name="captcha" class="inp-font vcen m-pad2 fl">
          <span class="yzm-img"><img width="100%" height="34px"  id="p_captcha_img" ></span></div>
          <div id="captcha_msg"></div>
        </div>
        <!--  验证方式  设定为：mobile(手机)-->
        <input type="hidden" id="vcodeType" value="mobile" name="personalInformation.vcodeType">
        
        <div class="form-listbox tan-zin9 clearfix">
            <label class="fl">验 证 码</label>
            <div class="input-box">
              <?= $form->field($model, 'verifyCode')->textInput(['placeholder' => '请输入手机验证码', 'class' => 'vcen in-aput-4  fl'])  ?>
              <input type="button" class="code fr" value="获取手机验证码"  id="verifyCodeBtn" data-action="<?= url(['site/verifyCode']) ?>">
              <!--   <div class="yzmts lans">验证码已发送到你的手机号码, 请查收</div> -->
          <div id="checkCodeerror" class="yzmts lans"></div>
          <div id="checkCodeErrer2" class="yzmts lans"></div>
            </div>
        </div>         
        <div class="button clearfix">
         
          <a href="javascript:;" class="tijiao" id="submitBtn">注册</a>
          <a class="back-login" href="<?= url('site/login') ?>">返回登录</a>
        </div>          
      </div>
     <?php self::endForm() ?>
  
<!-- 遮罩层开始 -->
<div class="transmask" style="display: none">
    <div class="infotips">你的信息已提交,正在审核<br/>请耐心等待审核</div>
</div>



<div class="clear_fl load_app">
    <a href="https://fir.im/<?= $apple; ?>" class="lf">苹果app下载</a>
    <a href="https://fir.im/<?= $android; ?>" class="rt">安卓app下载</a>
</div>
<!-- 遮罩层结束 -->

<?php self::endForm() ?>

<script>
$(function () {
    $("#submitBtn").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    $.alert(msg.info);
                } else {
                    window.location.href = msg.info;
                }
            }
        }));
        return false;
    });
    // 验证码
    $("#verifyCodeBtn").click(function () {
        var mobile = $('#user-mobile').val();
        var url = $(this).data('action');
        if (mobile.length != 11) {
            $.alert('您输入的不是一个手机号！');
            return false;
        }
        $.post(url, {mobile: mobile}, function(msg) {
              $.alert(msg.info);
        }, 'json');
    });

    $('.sex').click(function(){
        $(this).addClass('in-aput-xz').siblings('.sex').removeClass('in-aput-xz');
    })
});
</script>