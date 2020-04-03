<?php
class VariousCaptcha_Action extends Typecho_Widget implements Widget_Interface_Do
{
  /**
   * 该方法使用typecho方法
   * 在from表单post时增加catcha这一项
   */
  public function action()
  {
    /** 防止跨站 **/
    $referer = $this->request->getReferer();
    if (empty($referer)) {
      exit;
    }
    $refererPart = parse_url($referer);
    $currentPart = parse_url(Helper::options()->siteUrl);
    if ($refererPart['host'] != $currentPart['host'] || 0 !== strpos($refererPart['path'], $currentPart['path'])) {
      exit;
    }
    /** 防止跨站 **/
    
    require_once 'VariousCaptcha/SaveCatcha/SaveCatcha.php';
    $img = new SaveCatcha();
    switch($img->get_style()){
      case '1'://纯数字
        $img->numbers(6, 100, 24);
      break;
      case '2'://数字 + 字母
        $img->number_char(6, 100, 24);
      break;
      case '3'://中文验证码
        $img->chinese(3, 150, 50);
      break;
      case '4'://仿 chrome
        $img->imitate_chrome(5, 180, 40);
      break;
      case '0': // 算数加法
      default:
        $img->arithmetic(100, 24);
      break;
    }
  }
}
