<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
/*********************************************************************************
 * InitPHP 3.8.2 国产PHP开发框架   扩展类库-方法库
 *-------------------------------------------------------------------------------
 * 版权所有: CopyRight By initphp.com
 * 您可以自由使用该源码，但是在使用过程中，请保留作者信息。尊重他人劳动成果就是尊重自己
 *-------------------------------------------------------------------------------
 * Author:zhuli Dtime:2014-11-25 
***********************************************************************************/
class functionInit {

	/**
	 * 方法库-sign签名方法
	 * @param $array 需要加密的参数 
	 * @param $secret 秘钥
	 * @param $signName sign的名称，sign不会进行加密
	 */
	public function sign($array, $secret, $signName = "sign") {
		if (count($array) == 0) {
			return "";
		}
		ksort($array); //按照升序排序
		$str = "";
		foreach ($array as $key => $value) {
			if ($signName == $key) continue;
			$str .= $key . "=" . $value . "&";
		}
		$str = rtrim($str, "&");
		return md5($str . $secret);
	}

	/**
	 * 方法库-获取随机值
	 * @return string  
	 */
	public function get_rand($str, $len) {
		return substr(md5(uniqid(rand()*strval($str))),0, (int) $len);
	}
	
	/**
	 * 方法库-获取随机Hash值 
	 * @return string
	 */
	public function get_hash($length = 13) { 
		$chars = '0123456789abcdefghijklmnopqrstuvwxyz';
    	$max = strlen($chars) - 1;
    	mt_srand((double)microtime() * 1000000);
		for ($i=0; $i<$length; $i++) {
			$hash .= $chars[mt_rand(0, $max)];
		}
		return $hash;
	}
	
	/**
	 * 方法库-截取字符串-【该函数作者未知】
	 * @param string  $string 字符串  
	 * @param int     $length 字符长度
	 * @param string  $dot    截取后是否添加...
	 * @param string  $charset编码
	 * @return string
	 */
	public function cutstr($string, $length, $dot = ' ...', $charset = 'utf-8') {
		if (strlen($string) <= $length) {
			return $string;
		}
		$string = str_replace(array('&amp;', '&quot;', '&lt;', '&gt;'), array('&', '"', '<', '>'), $string);
		$strcut = '';
		if (strtolower($charset) == 'utf-8') {
			$n = $tn = $noc = 0;
			while ($n < strlen($string)) {
				$t = ord($string[$n]);				//ASCIIֵ
				if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
					$tn = 1; $n++; $noc++;
				} elseif (194 <= $t && $t <= 223) {
					$tn = 2; $n += 2; $noc += 2;
				} elseif (224 <= $t && $t < 239) {
					$tn = 3; $n += 3; $noc += 2;
				} elseif (240 <= $t && $t <= 247) {
					$tn = 4; $n += 4; $noc += 2;
				} elseif (248 <= $t && $t <= 251) {
					$tn = 5; $n += 5; $noc += 2;
				} elseif ($t == 252 || $t == 253) {
					$tn = 6; $n += 6; $noc += 2;
				} else {
					$n++;
				}
				if($noc >= $length) {
					break;
				}
			}
			if ($noc > $length) {
				$n -= $tn;
			}
			$strcut = substr($string, 0, $n);
		} else {
			for ($i = 0; $i < $length; $i++) {
				$strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
			}
		}
		$strcut = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $strcut);
		return $strcut.$dot;
	}
	
	/**
	 * 方法库-字符串是否存在
	 * @param string $str :字符或字符串
	 * @param string $string :字符串
	 * @return string 例子: $str='34' $string='1234' 返回 TRUE
	 */
	public function is_str_exist($str, $string) {
		$string = (string) $string;
		$str = (string) $str;
		return strstr($string,$str)===false ? false : true;
	}
	
	/**
	 * 方法库-token使用
	 * @param string $type :encode-加密方法|decode-解密方法
	 * @return string|bool
	 */
	public function token($type = 'encode') {
		session_start();
		if ($type == 'encode') {
			$key = $this->get_hash(5);
			$_SESSION['init_token'] = $key;
			return '<input name="init_token" type="hidden" value="'.$_SESSION['init_token'].'"/>';
		} else {
			$value = trim($_POST['init_token']);
			if ($value !== $_SESSION['init_token']) return false;
			return true;
		}
	}
	
	/**
	 * 方法库-压缩函数，主要是发送http页面内容过大的时候应用
	 * @param string $content 内容
	 * @return string
	 */
	public function gzip(&$content) {
		if(!headers_sent()&&extension_loaded("zlib")&&strstr($_SERVER["HTTP_ACCEPT_ENCODING"],"gzip")){
			$content = gzencode($content,2);
			header("Content-Encoding: gzip");
			header("Vary: Accept-Encoding");
			header("Content-Length: ".strlen($content));
		}
		return $content;
	}
	
	/**
	 * 方法库-向父串中插入子串
	 * @param string $string  : 父串
	 * @param number $sublen  : 长度
	 * @param string $str     : 子串
	 * @param string $code    : 编码
	 * @return string
	 */
	public function insert_str($string, $sublen=10, $str="<br/>", $code='UTF-8'){
		if ($code == 'UTF-8') {
			$pa ="/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
			preg_match_all($pa, $string, $t_string);
			$n = count($t_string[0]);
			$floor = ceil($n / $sublen);
			if ($n > $sublen) {
				for ($i=0; $i < $floor; $i++) {
					array_splice($t_string[0], ($sublen * ($i+1))-1, 0, $str);
				}
				return implode('',  $t_string[0]);
			} else {
				array_splice($t_string[0], $sublen, 0);
				return implode('', $t_string[0]);
			}
		}
	}
	
	/**
	 * 方法库-加密解密函数
	 * @param string $string  加密的字符串
	 * @param number $key     加密的密钥
	 * @param string $type    加密的方法-ENCODE|加密 DECODE|解密
	 * @return string
	 */
	public function str_code($string, $key, $type = 'ENCODE') {
		$string = ($type == 'DECODE') ? base64_decode($string) : $string;
		$key_len = strlen($key);
		$key     = md5($key);
		$string_len = strlen($string);
		for ($i=0; $i<$string_len; $i++) {
			$j = ($i * $key_len) % 32;
			$code .= $string[$i] ^ $key[$j];
		}
		return ($type == 'ENCODE') ? base64_encode($code) : $code;
	}
	
	/**
	 * 方法库-输出钱的格式
	 * @param string $num  数值
	 * @return string
	 */
	public function format_number($num){
   		return number_format($num, 2, ".", ",");
	}
	
	/**
	 * 方法库-字节转换-转换成MB格式等
	 * @param string $num  数值
	 * @return string
	 */
	public function bitsize($num) {
		if(!preg_match("/^[0-9]+$/", $num)) return 0;
		$type = array( "B", "KB", "MB", "GB", "TB", "PB" );
		$j = 0;
		while($num >= 1024) {
    		if( $j >= 5 ) return $num.$type[$j];
    		$num = $num / 1024;
    		$j++;
   		}
   		return $num.$type[$j];
	}
	
	/**
	 * 方法库-数组去除空值
	 * @param string $num  数值
	 * @return string
	 */
	public function array_remove_empty(&$arr, $trim = true) {
		if (!is_array($arr)) return false;
		foreach($arr as $key => $value){
			if (is_array($value)) {
				self::array_remove_empty($arr[$key]);
			} else {
				$value = ($trim == true) ? trim($value) : $value;
				if ($value == "") {
					unset($arr[$key]);
				} else {
					$arr[$key] = $value;
				}
			}
		}
	}
	
   /**
	* 生成 options html 代码
	* @param array  $arr 健值数组
	* @param string $default 默认值
	* @return string htmlcode
	*/
	function generateHtmlOptions($arr, $default = null){
		$string = '';
		if (!is_array($arr) && count($arr)) return $string;
		foreach ($arr as $key => $val) {
			$selected = ($default == $key) ? 'selected' : '';
			$string .= '<option value="'.$key.'" '.$selected.'>';
			$string .=  $val;
			$string .= '</option>';
		}
		return $string;
	}
	
	/**
	 * 清空数组中的空值
	 * @param array $array
	 * @return array
	 */
	public function clear_array_null(&$array) {
		foreach($array as $k => $v){
			if(empty($v)) unset($array[$k]);
		}
		return $array;
	}
	
	/**
	 * 左边清空
	 * @param string $string
	 * @return string
	 */
	public function rltrim($string) {
		if (is_string($string)) return trim($string);
		foreach($string as $k => $v){
			$string[$k] = trim($v);
		}
		return $string;
	}
	
	/**
	 * 生成唯一的订单号 20110809111259232312
	 * 2011-年日期
	 * 08-月份
	 * 09-日期
	 * 11-小时
	 * 12-分
	 * 59-秒
	 * 2323-微秒
	 * 12-随机值
	 * @return string
	 */
	public function trade_no() {
		list($usec, $sec) = explode(" ", microtime());
		$usec = substr(str_replace('0.', '', $usec), 0 ,4);
		$str  = rand(10,99);
		return date("YmdHis").$usec.$str;
	}
	
	/**
     * 获取接受JS传递中文编码函数
     * 作者：Min
     * @param string $str
     * @return string 
     */
    public function js_unescape($str){  
        $ret = '';  
        $len = strlen($str);  
      
        for ($i = 0; $i < $len; $i++) {  
            if ($str[$i] == '%' && $str[$i+1] == 'u') {  
                $val = hexdec(substr($str, $i+2, 4));  
                if ($val < 0x7f) $ret .= chr($val);  
                else if($val < 0x800) $ret .= chr(0xc0|($val>>6)).chr(0x80|($val&0x3f));  
                else $ret .= chr(0xe0|($val>>12)).chr(0x80|(($val>>6)&0x3f)).chr(0x80|($val&0x3f));  
                $i += 5;  
            } else if ($str[$i] == '%') {  
                $ret .= urldecode(substr($str, $i, 3));  
                $i += 2;  
            }  
            else $ret .= $str[$i];  
        }  
        return $ret;  
    }
	/**
	 * 是否移动端访问访问
	 * @return bool
	 */
	public function isMobile(){ 
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		} 
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA'])){ 
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		} 
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT'])){
			$clientkeywords = array ('nokia',
				'sony',
				'ericsson',
				'mot',
				'samsung',
				'htc',
				'sgh',
				'lg',
				'sharp',
				'sie-',
				'philips',
				'panasonic',
				'alcatel',
				'lenovo',
				'iphone',
				'ipod',
				'blackberry',
				'meizu',
				'android',
				'netfront',
				'symbian',
				'ucweb',
				'windowsce',
				'palm',
				'operamini',
				'operamobi',
				'openwave',
				'nexusone',
				'cldc',
				'midp',
				'wap',
				'mobile'
				); 
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(".implode('|',$clientkeywords).")/i",strtolower($_SERVER['HTTP_USER_AGENT']))){
				return true;
			} 
		} 
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])){ 
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
				return true;
			} 
		} 
		return false;
	}

	/**
 * 简单对称加密算法之加密
 * @param String $string 需要加密的字串
 * @param String $skey 加密EKY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
 public function encode($string = '', $skey = 'cxphp') {
    $strArr = str_split(base64_encode($string));
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key < $strCount && $strArr[$key].=$value;
    return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
 }
 /**
 * 简单对称加密算法之解密
 * @param String $string 需要解密的字串
 * @param String $skey 解密KEY
 * @author Anyon Zou <zoujingli@qq.com>
 * @date 2013-08-13 19:30
 * @update 2014-10-10 10:10
 * @return String
 */
 public function decode($string = '', $skey = 'cxphp') {
    $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
    $strCount = count($strArr);
    foreach (str_split($skey) as $key => $value)
        $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
    return base64_decode(join('', $strArr));
 }
	public function isAjax(){
		if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
			return true;
		}else{
			return false;
		}
	}

	public function get_url(){
		$sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
		$php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		$path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
		$relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self.(isset($_SERVER['QUERY_STRING']) ? '?'.$_SERVER['QUERY_STRING'] : $path_info);
		return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '').$relate_url;
	}
	//传入图片地址,得到图片的Base64编码
	public function img2Base64($url){
		 //据说 CURL 能缓存DNS 效率比 socket 高 
        $ch = curl_init($url); 
        // 超时设置 
        curl_setopt($ch, CURLOPT_TIMEOUT, 5); 
        //取前面 168 个字符 通过四张测试图读取宽高结果都没有问题,若获取不到数据可适当加大数值 
        //curl_setopt($ch, CURLOPT_RANGE, '0-20480'); 
        //跟踪301跳转 
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1); 
        // 返回结果 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
        $dataBlock = curl_exec($ch); 
        curl_close($ch); 
        if (!$dataBlock) return false; 
    // 将读取的图片信息转化为图片路径并获取图片信息,经测试,这里的转化设置 jpeg 对获取png,gif的信息没有影响,无须分别设置 
    // 有些图片虽然可以在浏览器查看但实际已被损坏可能无法解析信息  
		$img_base64='data:image/jpeg;base64,'.base64_encode($dataBlock); 
		return $img_base64;
	}
}