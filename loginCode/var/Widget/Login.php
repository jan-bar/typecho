<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * 登录动作
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 * @version $Id$
 */

/**
 * 登录组件
 *
 * @category typecho
 * @package Widget
 * @copyright Copyright (c) 2008 Typecho team (http://www.typecho.org)
 * @license GNU General Public License 2.0
 */
class Widget_Login extends Widget_Abstract_Users implements Widget_Interface_Do
{
    /**
     * 初始化函数
     *
     * @access public
     * @return void
     */
    public function action()
    {
        // protect
        $this->security->protect();

        /** 如果已经登录 */
        if ($this->user->hasLogin()) {
            /** 直接返回 */
            $this->response->redirect($this->options->index);
        }

        session_start();
        $now_time = time();
        if (!empty($this->request->varName) && !empty($this->request->varPass)) {
          if (!$this->user->login($this->request->varName,$this->request->varPass,true,5)) {
            echo '{"code":-2,"desc":"错误你猜"}';
            return; /*验证用户名密码失败*/
          }
          if (empty($_SESSION['endTime']) || ($sub_time = $_SESSION['endTime'] - $now_time) < 0) {
            $_SESSION['endTime'] = $now_time + 60; /*当前时间加N秒的时间*/
          } else {
            echo '{"code":'. $sub_time .'}';
            return; /*验证码处于N以内则返回剩余秒数*/
          }
          $uniqid = substr(md5(uniqid(microtime(true),true)), 0, 6);
          $_SESSION[$this->request->varName . 'code'] = $uniqid;
          $pid = pcntl_fork();/*通过子进程发送通知*/
          if ($pid == -1) {
            echo '{"code":-2,"desc":"服务器内部错误"}';
          } else if ($pid > 0) {
            echo '{"code":-1}';
          } else {
            $postdata = http_build_query(array(
              'text' => '[' .$this->request->varName .']获取验证码',
              'desp' => '['.$uniqid.']')
            );
            $opts = array('http' => array(
              'method'  => 'POST',
              'header'  => 'Content-type: application/x-www-form-urlencoded',
              'content' => $postdata)
            );
            $sckey  = 'your key'; /*我的key(http://sc.ftqq.com/?c=code)*/
            $result = file_get_contents('http://sc.ftqq.com/'.$sckey.'.send', false, stream_context_create($opts));
          }
          return;
        }

        /** 初始化验证类 */
        $validator = new Typecho_Validate();
        $validator->addRule('name', 'required', _t('请输入用户名'));
        $validator->addRule('code', 'required', _t('请输入验证码'));
        $validator->addRule('password', 'required', _t('请输入密码'));

        /** 截获验证异常 */
        if ($error = $validator->run($this->request->from('name', 'code', 'password'))) {
            Typecho_Cookie::set('__typecho_remember_name', $this->request->name);

            /** 设置提示信息 */
            $this->widget('Widget_Notice')->set($error);
            $this->response->goBack();
        }

        $valid = false;
        if (!empty($_SESSION['endTime']) && ($now_time - $_SESSION['endTime']) < 120 &&
            !empty($_SESSION[$this->request->name . 'code']) && 
            $this->request->code == $_SESSION[$this->request->name . 'code']) {
          $valid = true; /*N秒以内且填写正确,删除session中验证码*/
          unset($_SESSION['verCode']);
        }

        if ($valid) { /** 开始验证用户 **/
          $valid = $this->user->login($this->request->name, $this->request->password,
          false, 1 == $this->request->remember ? $this->options->time + $this->options->timezone + 30*24*3600 : 0);
        }

        /** 比对密码 */
        if (!$valid) {
            /** 防止穷举,休眠3秒 */
            sleep(3);

            $this->pluginHandle()->loginFail($this->user, $this->request->name,
            $this->request->password, 1 == $this->request->remember);

            Typecho_Cookie::set('__typecho_remember_name', $this->request->name);
            $this->widget('Widget_Notice')->set(_t('用户名或密码或验证码无效'), 'error');
            $this->response->goBack('?referer=' . urlencode($this->request->referer));
        }

        $this->pluginHandle()->loginSucceed($this->user, $this->request->name,
        $this->request->password, 1 == $this->request->remember);

        /** 跳转验证后地址 */
        if (NULL != $this->request->referer) {
            $this->response->redirect($this->request->referer);
        } else if (!$this->user->pass('contributor', true)) {
            /** 不允许普通用户直接跳转后台 */
            $this->response->redirect($this->options->profileUrl);
        } else {
            $this->response->redirect($this->options->adminUrl);
        }
    }
}
