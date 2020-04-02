<?php
include 'common.php';

if ($user->hasLogin()) {
    $response->redirect($options->adminUrl);
}
$rememberName = htmlspecialchars(Typecho_Cookie::get('__typecho_remember_name'));
Typecho_Cookie::delete('__typecho_remember_name');

$bodyClass = 'body-100';

include 'header.php';
?>
<div class="typecho-login-wrap">
    <div class="typecho-login">
        <h1><a href="https://www.janbar.top/" class="i-logo">Janbar</a></h1>
        <form action="<?php $options->loginAction(); ?>" method="post" name="login" role="form">
            <p>
                <label for="name" class="sr-only"><?php _e('用户名'); ?></label>
                <input type="text" id="name" name="name" value="<?php echo $rememberName; ?>" placeholder="<?php _e('用户名'); ?>" class="text-l w-100" autofocus />
            </p>
            <p>
                <label for="password" class="sr-only"><?php _e('密码'); ?></label>
                <input type="password" id="password" name="password" class="text-l w-100" placeholder="<?php _e('密码'); ?>" />
            </p>
            <p>
                <input type="text" id="code" name="code" value="" class="text-l w-50" style="float:left" autocomplete="off" placeholder="<?php _e('验证码'); ?>" />
                <button id="bCode" onclick="btnCode(this);return false;" class="btn btn-l w-50 primary" style="float:left margin-left:0;"><?php _e('获取验证码'); ?></button>
            </p>
            <p class="submit">
                <button type="submit" class="btn btn-l w-100 primary"><?php _e('登录'); ?></button>
                <input type="hidden" name="referer" value="<?php echo htmlspecialchars($request->get('referer')); ?>" />
            </p>
        </form>
        <p class="more-link">
            <a href="<?php $options->siteUrl(); ?>"><?php _e('返回首页'); ?></a>
            <?php if($options->allowRegister): ?>
            &bull;
            <a href="<?php $options->registerUrl(); ?>"><?php _e('用户注册'); ?></a>
            <?php endif; ?>
        </p>
    </div>
</div>
<script type="text/javascript">
function btnCode(obj) {
  var name = $("#name").val();
  var password = $("#password").val();
  if(name.length > 3 && password.length > 5) {
    if (cntDown.isOver()) {
      $.ajax({
        url:"<?php $options->loginAction(); ?>",
        data:{varName:name,varPass:password},
        dataType:"json",type:"post",async:false,cache:false,
        success:function(res) {
          console.log(res);
          switch(res.code) {
            case -1:
              setTime($("#bCode"));
            break;
            case -2:
              alert(res.desc);
            break;
            default:
              cntDown.set(res.code);
              setTime($("#bCode"));
            break;
          }
        },
        error: function() {alert("验证码发送失败");}
      });
    } else {
      setTime($("#bCode"));
    }
  }
}
var cntDown = {
  cnt : -1, max : 60,
  ok : function() {
    if (typeof localStorage !== 'undefined') {
      try {
        if (localStorage.getItem('countdown') >= 0) {
          return true;
        }
        localStorage.setItem('countdown', cntDown.max);
        return localStorage.getItem('countdown') == cntDown.max;
      } catch(e) {}
    }
    return false;
  }(),
  get() {
    if (this.ok) {
      this.cnt = localStorage.getItem('countdown');
      if (this.cnt === null) {
        this.cnt = this.max;
        localStorage.setItem('countdown', this.cnt);
      }
    } else if (this.cnt == -1) {
      this.cnt = this.max;
    }
    return this.cnt;
  },
  set(cnt) {
    this.cnt = cnt;
    if (this.ok)
      localStorage.setItem('countdown', this.cnt);
  },
  isOver() {return this.get() == this.max;},
  reset() {this.set(this.max);}
};
function setTime(obj) {
  var cnt = cntDown.get();
  if (cnt <= 0) {
    obj.prop('disabled', false);
    obj.text("获取验证码");
    cntDown.reset();
    return;
  }
  obj.prop('disabled', true);
  obj.text("("+cnt+"s)重新发送");
  cntDown.set(cnt-1);
  setTimeout(function(){setTime(obj)},1000);
}
</script>
<?php 
include 'common-js.php';
?>
<script>
$(document).ready(function () {
    $('#name').focus();
});
</script>
<?php
include 'footer.php';
?>
