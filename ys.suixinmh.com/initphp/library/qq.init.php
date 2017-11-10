<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
class qqInit {
	private $confing=array("appkey"=>"101374536","appsecretkey"=>"4d1bf119ff693b9cda15b66696defc3d","callback"=>"http://m.xiaoshuokong.com");
	/*public function init() {
		$this->confing =array("appkey"=>"101326590","appsecretkey"=>"2370d59440d4b4c9d7f9a0063221587d","callback"=>"http://wap.manyuedu.cn");
		return $this->confing;
	}*/
	private function get_access_token($code,$state){
        //if($state == $_SESSION['state']) {
		$url = "https://graph.qq.com/oauth2.0/token";
		if (empty($_COOKIE["refresh_token"])){
			$param = array(
				"grant_type"    =>    "authorization_code",
				"client_id"     =>    $this->confing["appkey"],
				"client_secret" =>    $this->confing["appsecretkey"],
				"code"          =>    $code,
				"redirect_uri"  =>    $this->confing["callback"]
			);
		}else{
			$param = array(
				"grant_type"    =>    "refresh_token",
				"client_id"     =>    $this->confing["appkey"],
				"client_secret" =>    $this->confing["appsecretkey"],
				"refresh_token"  =>   $_COOKIE["init_refresh_token"]
			);
		}
        $response =$this->get($url, $param);
        if($response == false) {
			return false;
        }
        $params = array();
        parse_str($response, $params);
		setcookie("init_refresh_token",$params["refresh_token"],2419200,'/','m.xiaoshuokong.com');
        return $params["access_token"];
        //} else {
    //        exit("The state does not match. You may be a victim of CSRF.");
      //  }
    }

    private function get_openid($code,$state) {//$access_token
		$access_token=$this->get_access_token($code,$state);
        $url = "https://graph.qq.com/oauth2.0/me"; 
        $param = array(
            "access_token"    => $access_token
        );
        $response  = $this->get($url, $param);
        if($response == false) {
            return false;
        }
        if (strpos($response, "callback") !== false) {
            $lpos = strpos($response, "(");
            $rpos = strrpos($response, ")");
            $response  = substr($response, $lpos + 1, $rpos - $lpos -1);
        }
        $user = json_decode($response);
        if (isset($user->error) || $user->openid == "") {
            return false;
        }
		
        return array("openid"=>$user->openid,"token"=>$access_token);
    }
    public function get_user_info($code,$state,$format = "json") {//$token, $openid,$format = "json"
		$info=$this->get_openid($code,$state);
        $url = "https://graph.qq.com/user/get_user_info";
        $param = array(
            "access_token"      =>    $info["token"],
            "oauth_consumer_key"=>    $this->confing["appkey"],
            "openid"            =>    $info["openid"],
            "format"            =>    $format
        );
        $response = $this->get($url, $param);
        if($response == false) {
            return false;
        }
        $user = json_decode($response, true);
		$user['openid']=$info["openid"];
        return $user;
    }
    public function login($scope='') {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $login_url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=" 
            . $this->confing["appkey"]."&redirect_uri=".urlencode($this->confing["callback"])
            . "&state=".$_SESSION['state']
            . "&scope=".$scope;
		header("Location:$login_url");
    }
    public function callback($appsecretkey) {
        $code = $_GET['code'];
        $state = $_SESSION['state'];

        $token = $this->get_access_token($code,$state);
        $openid = $this->get_openid($token);
        if(!$token || !$openid) {
            exit('get token or openid error!');
        }

        return array('openid' => $openid, 'token' => $token);
    }
	/*
	 * HTTP GET Request
	*/
	private function get($url, $param = null) {
		if($param != null) {
			$url = $url."?";
			$valueArr = array();
			foreach($param as $key => $val){
				$valueArr[] = "$key=$val";
			}
			$keyStr = implode("&",$valueArr);
			$url .= ($keyStr);
			//$query = http_build_query($param);
			//$url = $url . '?' . $query;
		}
		$ch = curl_init();
		if(stripos($url, "https://") !== false){
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		}   
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );
		$content = curl_exec($ch);
		$status = curl_getinfo($ch);
		curl_close($ch);
		if(intval($status["http_code"]) == 200) {
			return $content;
		}else{
			echo $status["http_code"];
			return false;
		}   
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

}
