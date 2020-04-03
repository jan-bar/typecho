<?php
/**
 * Typecho版 多功能验证码
 * 
 * @package VariousCaptcha
 * @author  你好啊007
 * @version 0.0.1
 * @update: 2016.12.20
 * @link https://www.zj007.pub/
 */
class VariousCaptcha_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate() {
      if (!function_exists('gd_info')) {
        throw new Typecho_Plugin_Exception(_t('对不起, 您的主机不支持 gd 扩展, 无法正常使用此功能'));
      }
      // 下面是图形验证码定义
      Typecho_Plugin::factory('Widget_Feedback')->comment = array('VariousCaptcha_Plugin', 'filter');
      Typecho_Plugin::factory('Widget_Feedback')->trackback = array('VariousCaptcha_Plugin', 'filter');
      Typecho_Plugin::factory('Widget_XmlRpc')->pingback = array('VariousCaptcha_Plugin', 'filter');
      Helper::addAction('captcha', 'VariousCaptcha_Action');
      
      return _t('插件已激活，现在可以对插件进行设置！');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate() {
      return _t('插件已禁用！');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form){
      $style = new Typecho_Widget_Helper_Form_Element_Select('style', array('0' => '算术验证码',
          '1' => '数字验证码',
          '2' => '数字+字母验证码',
          '3' => '中文验证码',
          '4' => '仿google验证码'), '0', _t('验证码样式:'), _t('选择一个你喜欢的验证码。'));
      $form->addInput($style);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 评论过滤器
     * 
     * @access public
     * @param array $comment 评论结构
     * @param Typecho_Widget $post 被评论的文章
     * @param array $result 返回的结果上下文
     * @param string $api api地址
     * @return void
     */
    public static function filter($comment, $post, $result)
    {
      $captchaCode  = Typecho_Request::getInstance()->captcha;

      require_once 'VariousCaptcha/SaveCatcha/SaveCatcha.php';
      $img = new SaveCatcha();
      if ( !$img->check($captchaCode) ) {
        echo "<script>alert('验证码错误, 请重新输入!');window.location.href=document.referrer;</script>";
      }else{
        return $comment;
      }
    }
    /**
     * 输出图形验证码
     * 语法: VariousCaptcha_Plugin::captcha();
     *
     * @access public
     * @return string
     */
    public static function captcha()
    {
      require_once 'VariousCaptcha/SaveCatcha/SaveCatcha.php';// 将验证码样式保存到session
      new SaveCatcha(Helper::options()->plugin('VariousCaptcha')->style);
      
      /* 下面是关于图片和输入框的样式 */
      echo <<<EOT
<style type="text/css">
  .captcha_pic{float:left;border-radius:10px;cursor:pointer;}
  .captcha_input{margin-left:10px;float:left;height:20px;width:100px;}
</style>
EOT;
      echo '<img class="captcha_pic" src="'. Typecho_Common::url('/action/captcha', Helper::options()->index) .'" alt="captcha" onclick="this.src = this.src + \'?\' + Math.random()" title="点击图片刷新验证码" />'
      . '<input class="captcha_input" id="captcha" class="captcha" name="captcha" placeholder="验证码" /><p>';
    }
}
