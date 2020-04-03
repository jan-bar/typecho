<?php
/*
 * 生成验证码图片,已经检查验证码
 */
class SaveCatcha
{
  /**
   * 构造函数
   */
  function SaveCatcha($style = NULL)
	{
    if ( session_id() == '' ) {
			if (trim($this->session_name) != '') {
				session_name($this->session_name);
			}
			session_start();
		}
    $this->session_name = '';
    if($style != NULL) //验证码样式
      $_SESSION['SaveCatcha_Style'] = $style;
  }
  
  /**
   * 返回验证码类型
   */
  function get_style()
	{
    return isset($_SESSION['SaveCatcha_Code']) ? $_SESSION['SaveCatcha_Style'] : '0';
  }
  
  /**
   * 算数验证码
   * 记得传入图片宽和高
   */
  function arithmetic($w, $h)
	{
    $im = imagecreate($w, $h);
    $red = imagecolorallocate($im, 255, 0, 0);
    $white = imagecolorallocate($im, 255, 255, 255);
    $num1 = rand(1, 20);
    $num2 = rand(1, 20);
    $_SESSION['SaveCatcha_Code'] = (string)($num1 + $num2);
    $gray = imagecolorallocate($im, 209, 193, 248);
    $black = imagecolorallocate($im, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150));
    //画背景
    imagefilledrectangle($im, 0, 0, 100, 24, $black);
    //在画布上随机生成大量点，起干扰作用;
    for ($i = 0; $i < 80; $i++) {
      imagesetpixel($im, rand(0, $w), rand(0, $h), $gray);
    }
    imagestring($im, 5, 5,  4, $num1, $red);
    imagestring($im, 5, 30, 3, "+",   $red);
    imagestring($im, 5, 45, 4, $num2, $red);
    imagestring($im, 5, 70, 3, "=",   $red);
    imagestring($im, 5, 85, 2, "?",   $white);
    header("Content-type: image/png");
    imagepng($im);
    imagedestroy($im);
	}
  
  /**
   *  数字验证码
   *  传入数字长度、宽和高
   */
  function numbers($num, $w, $h) {
    $code = "";
    for ($i = 0; $i < $num; $i++) {
      $code .= rand(0, 9);
    }
    //将生成的验证码写入session，备验证页面使用
    $_SESSION["SaveCatcha_Code"] = $code;
    //创建图片，定义颜色值
    Header("Content-type: image/PNG");
    $im = imagecreate($w, $h);
    $black = imagecolorallocate($im, 0, 0, 0);
    $gray = imagecolorallocate($im, 200, 200, 200);
    $bgcolor = imagecolorallocate($im, 255, 255, 255);

    imagefill($im, 0, 0, $gray);
    //画边框
    imagerectangle($im, 0, 0, $w-1, $h-1, $black);
    //随机绘制两条虚线，起干扰作用
    $style = array ($black,$black,$black,$black,$black,$gray,$gray,$gray,$gray,$gray);
    imagesetstyle($im, $style);
    $y1 = rand(0, $h);
    $y2 = rand(0, $h);
    $y3 = rand(0, $h);
    $y4 = rand(0, $h);
    imageline($im, 0, $y1, $w, $y3, IMG_COLOR_STYLED);
    imageline($im, 0, $y2, $w, $y4, IMG_COLOR_STYLED);
    //在画布上随机生成大量黑点，起干扰作用;
    for ($i = 0; $i < 80; $i++) {
      imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
    }
    //将数字随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
    $strx = rand(15, 20);
    for ($i = 0; $i < $num; $i++) {
      $strpos = rand(1, 6);
      imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
      $strx += rand(8, 12);
    }
    imagepng($im);
    imagedestroy($im);
  }
  
  /**
   * 数字 + 字母
   * 传入长度、宽和高
   */
  function number_char($num, $w, $h) {
    // 去掉了 0 1 O l 等
    $code = '';$str = "23456789abcdefghijkmnpqrstuvwxyz";
    for ($i = 0; $i < $num; $i++) {
      $code .= $str[mt_rand(0, strlen($str)-1)];
    }
    //将生成的验证码写入session，备验证页面使用
    $_SESSION["SaveCatcha_Code"] = $code;
    //创建图片，定义颜色值
    Header("Content-type: image/PNG");
    $im = imagecreate($w, $h);
    $black = imagecolorallocate($im, mt_rand(0, 200), mt_rand(0, 120), mt_rand(0, 120));
    $gray = imagecolorallocate($im, 118, 151, 199);
    $bgcolor = imagecolorallocate($im, 235, 236, 237);

    //画背景
    imagefilledrectangle($im, 0, 0, $w, $h, $bgcolor);
    //画边框
    imagerectangle($im, 0, 0, $w-1, $h-1, $gray);
    // imagefill($im, 0, 0, $bgcolor);
    //在画布上随机生成大量点，起干扰作用;
    for ($i = 0; $i < 80; $i++) {
      imagesetpixel($im, rand(0, $w), rand(0, $h), $black);
    }
    //将字符随机显示在画布上,字符的水平间距和位置都按一定波动范围随机生成
    $strx = rand(15, 20);
    for ($i = 0; $i < $num; $i++) {
      $strpos = rand(1, 6);
      imagestring($im, 5, $strx, $strpos, substr($code, $i, 1), $black);
      $strx += rand(8, 14);
    }
    imagepng($im);
    imagedestroy($im);
  }
  
  /**
   * 仿chrome验证码
   * 传入长度、宽和高
   */
  function imitate_chrome($length, $im_x, $im_y) {
    $text = "";$str = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    for($i=0;$i<$length;$i++){
      $num[$i] = rand(0,25);
      $text .= $str[$num[$i]];
    } // 得到 $text 字符串
    $_SESSION['SaveCatcha_Code'] = $text;
    
    $im = imagecreatetruecolor($im_x,$im_y);
    $text_c = ImageColorAllocate($im, mt_rand(0,100),mt_rand(0,100),mt_rand(0,100));
    $tmpC0=mt_rand(100,255);
    $tmpC1=mt_rand(100,255);
    $tmpC2=mt_rand(100,255);
    $buttum_c = ImageColorAllocate($im,$tmpC0,$tmpC1,$tmpC2);
    imagefill($im, 16, 13, $buttum_c);
    
    $font = 'usr/plugins/VariousCaptcha/SaveCatcha/font.ttf';
    for ($i=0;$i<strlen($text);$i++)
    {
      $tmp =substr($text,$i,1);
      $array = array(-1,1);
      $p = array_rand($array);
      $an = $array[$p]*mt_rand(1,10);//角度
      $size = 28;
      imagettftext($im, $size, $an, 15+$i*$size, 35, $text_c, $font, $tmp);
    }
    $distortion_im = imagecreatetruecolor ($im_x, $im_y);

    imagefill($distortion_im, 16, 13, $buttum_c);
    for ( $i=0; $i<$im_x; $i++) {
      for ( $j=0; $j<$im_y; $j++) {
        $rgb = imagecolorat($im, $i , $j);
        if( (int)($i+20+sin($j/$im_y*2*M_PI)*10) <= imagesx($distortion_im)&& (int)($i+20+sin($j/$im_y*2*M_PI)*10) >=0 ) {
          imagesetpixel ($distortion_im, (int)($i+10+sin($j/$im_y*2*M_PI-M_PI*0.1)*4) , $j , $rgb);
        }
      }
    }
    //加入干扰象素;
    $count = 160;//干扰像素的数量
    for($i=0; $i<$count; $i++){
      $randcolor = ImageColorallocate($distortion_im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
      imagesetpixel($distortion_im, mt_rand()%$im_x , mt_rand()%$im_y , $randcolor);
    }
    
    $rand = mt_rand(5,30);
    $rand1 = mt_rand(15,25);
    $rand2 = mt_rand(5,10);
    for ($yy=$rand; $yy<=+$rand+2; $yy++){
      for ($px=-80;$px<=80;$px=$px+0.1)
      {
        $x=$px/$rand1;
        if ($x!=0)
        {
          $y=sin($x);
        }
        $py=$y*$rand2;

        imagesetpixel($distortion_im, $px+80, $py+$yy, $text_c);
      }
    }
    //设置文件头;
    Header("Content-type: image/JPEG");
    //以PNG格式将图像输出到浏览器或文件;
    ImagePNG($distortion_im);
    //销毁一图像,释放与image关联的内存;
    ImageDestroy($distortion_im);
    ImageDestroy($im);
  }

  /**
   * 中文验证码
   * 传入长度、宽和高
   */
  function chinese($length, $w, $h) {
    header("Content-type: image/PNG");
    
    $font = 'usr/plugins/VariousCaptcha/SaveCatcha/font.ttf'; //字体文件
    $str = iconv('utf-8','gbk',"们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处己将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千胜细影济白格效置推空配刀叶率述今选养德话查差半敌始片施响收华觉备名红续均药标记难存测士身紧液派准斤角降维板许破述技消底床田势端感往神便贺村构照容非搞亚磨族火段算适讲按值美态黄易彪服早班麦削信排台声该击素张密害侯草何树肥继右属市严径螺检左页抗苏显苦英快称坏移约巴材省黑武培著河帝仅针怎植京助升王眼她抓含苗副杂普谈围食射源例致酸旧却充足短划剂宣环落首尺波承粉践府鱼随考刻靠够满夫失包住促枝局菌杆周护岩师举曲春元超负砂封换太模贫减阳扬江析亩木言球朝医校古呢稻宋听唯输滑站另卫字鼓刚写刘微略范供阿块某功套友限项余倒卷创律雨让骨远帮初皮播优占死毒圈伟季训控激找叫云互跟裂粮粒母练塞钢顶策双留误础吸阻故寸盾晚丝女散焊功株亲院冷彻弹错散商视艺灭版烈零室轻血倍缺厘泵察绝富城冲喷壤简否柱李望盘磁雄似困巩益洲脱投送奴侧润盖挥距星松送获兴独官混纪依未突架宽冬章湿偏纹吃执阀矿寨责熟稳夺硬价努翻奇甲预职评读背协损棉侵灰虽矛厚罗泥辟告卵箱掌氧恩爱停曾溶营终纲孟钱待尽俄缩沙退陈讨奋械载胞幼哪剥迫旋征槽倒握担仍呀鲜吧卡粗介钻逐弱脚怕盐末阴丰雾冠丙街莱贝辐肠付吉渗瑞惊顿挤秒悬姆烂森糖圣凹陶词迟蚕亿矩康遵牧遭幅园腔订香肉弟屋敏恢忘编印蜂急拿扩伤飞露核缘游振操央伍域甚迅辉异序免纸夜乡久隶缸夹念兰映沟乙吗儒杀汽磷艰晶插埃燃欢铁补咱芽永瓦倾阵碳演威附牙芽永瓦斜灌欧献顺猪洋腐请透司危括脉宜笑若尾束壮暴企菜穗楚汉愈绿拖牛份染既秋遍锻玉夏疗尖殖井费州访吹荣铜沿替滚客召旱悟刺脑措贯藏敢令隙炉壳硫煤迎铸粘探临薄旬善福纵择礼愿伏残雷延烟句纯渐耕跑泽慢栽鲁赤繁境潮横掉锥希池败船假亮谓托伙哲怀割摆贡呈劲财仪沉炼麻罪祖息车穿货销齐鼠抽画饲龙库守筑房歌寒喜哥洗蚀废纳腹乎录镜妇恶脂庄擦险赞钟摇典柄辩竹谷卖乱虚桥奥伯赶垂途额壁网截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔唐漏闻沈熔氯荒茎男凡抢像浆旁玻亦忠唱蒙予纷捕锁尤乘乌智淡允叛畜俘摸锈扫毕璃宝芯爷鉴秘净蒋钙肩腾枯抛轨堂拌爸循诱祝励肯酒绳穷塘燥泡袋朗喂铝软渠颗惯贸粪综墙趋彼届墨碍启逆卸航衣孙龄岭骗休借");
    $code = '';
    for($i=0;$i<4;$i++){
      $Xi=mt_rand(0,strlen($str)/2);
      if($Xi%2) $Xi+=1;
        $code .= substr($str,$Xi,2);
    }
    $_SESSION['SaveCatcha_Code'] = $code;
    $im=imagecreatetruecolor($w,$h);
    $bkcolor=imagecolorallocate($im,250,250,250);
    imagefill($im,0,0,$bkcolor);
    /***添加干扰***/
    for($i=0;$i<15;$i++){
      $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
      imagearc($im,mt_rand(-10,$w),mt_rand(-10,$h),mt_rand(30,300),mt_rand(20,200),55,44,$fontcolor);
    }
    for($i=0;$i<255;$i++){
      $fontcolor=imagecolorallocate($im,mt_rand(0,255),mt_rand(0,255),mt_rand(0,255));
      imagesetpixel($im,mt_rand(0,$w),mt_rand(0,$h),$fontcolor);
    }
    /***********内容*********/
    for($i=0;$i<4;$i++){
      $fontcolor=imagecolorallocate($im,mt_rand(0,120),mt_rand(0,120),mt_rand(0,120)); //这样保证随机出来的颜色较深。
      $codex=iconv("GB2312","UTF-8",substr($code,$i*2,2));
      imagettftext($im,mt_rand(14,18),mt_rand(-60,60),30*$i+20,mt_rand(30,35),$fontcolor,$font,$codex);
    }
    imagepng($im);
    imagedestroy($im);
  }

  /**
   * 检查验证码是否正确
   */
  function check($code)
	{
    $correct_code = false; //返回值
    $code = strtolower(trim($code)); // 去首尾空格并转换为小写
    
    if ( isset($_SESSION['SaveCatcha_Code']) ) { // session被设置
      $save_catcha_code = trim($_SESSION['SaveCatcha_Code']);
      $_SESSION['SaveCatcha_Code'] = '';//去首尾空格,可能是中文因此不转化为小写
    }
    
		if ( !empty($save_catcha_code) ) { // 结果不为空
      if ($_SESSION['SaveCatcha_Style'] == '3') { // 中文验证码检查
        if ( iconv('gbk','utf-8', $save_catcha_code) == $code ){
          $correct_code = true;
        }
      }elseif ( strtolower($save_catcha_code) == $code) { //其他验证码均转换为小写对比
 				$correct_code = true;
			}
		}
    return $correct_code;
	}
}
