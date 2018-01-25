<?php common\components\View::regCss('registered.css') ?>
<?php common\components\View::regCss('mobile.css') ?>
<?php common\components\View::regCss('login.css') ?>
<style>
    body{
        background:#fff;
    }
    .load_app {
        padding: 0 10px;
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
    #loginForm{
      padding: 0 30px;
    }
    .wrap-content {
      padding: 4% 0;
      background: #fff;
    }
    .form-listbox {
      padding: 3px 0;
  }
  #user-username,#user-password {
    background: url(/images/user-icon.png) no-repeat 16px center;
    padding-left: 54px;
    background-size: 20px 20px;
    color: #a0a0a0;
    font-size: 14px;
    height: 46px;
    line-height: 46px;
    border-radius: 23px;
    box-sizing: border-box;
    border:1px solid #ccc;
  }
  #user-password {
    background: url(/images/pwd-icon.png) no-repeat 16px center;
    background-size: 20px 20px;
  }
  .button {
    border-top: none; 
  }
  .forget-btn{
    float: right;
    color: #f04447;
  }
  .tijiao{
    height: 46px;
    line-height: 46px;
    background: url(/images/login-btn.png) no-repeat center center!important;
    background-size: 100% 100%!important;
    border: 0!important;
    outline: 0!important;
    display: block;
    width: 100%;
    margin-top: 18px;
  }
  .register_link{
    height: 46px;
    line-height: 46px;
    outline: 0!important;
    display: block;
    width: 100%;
    text-align: center;

    color: #F15A5C;
    background: #F3F4F6;
    border: 1px solid #F15A5C;
    border-radius: 23px;
    text-decoration: none;
    margin-top: 14px
  }
  .logo_img {
      width: 100px;
      height: 100px;
      margin: 30px auto;
      margin-bottom: 10px;
  }
  .logo_img img{
      width: 100%;
      height: 100%;
      border-radius:50%;
  }
</style>


<div class="logo_img">
  <img src="/images/205135642884.jpg">
</div>


<?php $form = self::beginForm(['showLabel' => false]) ?>
 <!--   <div class="container">
        <div class="row" style="margin-bottom:25px;">
             <div class="col-xs-12">
                <div class="logo_img">
                    <img src="<?= config('web_logo') ?>" alt="<?= config('web_name') ?>">
                </div>
            </div> 

        </div>
    </div> -->
     <div class="wrap-content">
        
        <div class="form-listbox tan-zin8 clearfix">
          <div class="input-box1">
            <div class="tel-po inp-font-tel-1">
              <input type="hidden" id="mobilePhone_areaCodes" value="" name="personalInformation.mobilePhone_areaCodes">
               <span class=""><?= $form->field($model, 'username')->textInput(['placeholder' => '请输入您的手机号','class' => 'inp-font fl']) ?></span>
            </div>
          </div>
        </div>
        <div class="form-listbox tan-zin8 clearfix">
          <div class="input-box1">
            <div class="tel-po inp-font-tel-1">
              <input type="hidden" id="mobilePhone_areaCodes" value="" name="personalInformation.mobilePhone_areaCodes">
               <span class=""><?= $form->field($model, 'password')->passwordInput(['placeholder' => '请输入您的密码','class' => 'inp-font fl']) ?></span>
            </div>
          </div>
        </div>
        <div style="text-align: center" class="form-listbox tan-zin8 clearfix">
          <span ><a  class="forget-btn" href="<?= url('site/forget') ?>">忘记密码？</a></span>
        </div>

        <!-- <div class="button clearfix">
          <div class="pull-right">
            
          </div>
           
        </div>  --> 
        <a href="javascript:;" class="tijiao" ></a>
         <div style="text-align: center" class="form-listbox tan-zin8 clearfix">
          <span ><a class="register_link" href="<?= url(['site/register']) ?>">注册新用户</a></span> 
          <!-- <span ><a href="<?= url(['site/register']) ?>">注册新用户</a></span>  -->
        </div>
     </div>
    <div class="clear_fl load_app">
        <a href="https://fir.im/<?= $apple; ?>" class="lf">苹果app下载</a>
        <a href="https://fir.im/<?= $android; ?>" class="rt">安卓app下载</a>
    </div>
        <?php self::endForm() ?>
<script>
$(function () {
    $(".tijiao").click(function () {
        $("form").ajaxSubmit($.config('ajaxSubmit', {
            success: function (msg) {
                if (!msg.state) {
                    return $.alert(msg.info);
                } else {
                    window.location.href = msg.info;
                }
            }
        }));
        return false;
    });
});
</script>
