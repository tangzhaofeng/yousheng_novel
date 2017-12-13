<?php
/**
 * @author gaoguofeng
 */
class weixinLoginService extends Service{
    private $_appId;
    private $_appSecret;
    private $_callback;
	private $logDo;
	private $doMain;
	private $wxConfig;
	const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
	const OAUTH_CGI_URL = '/cgi-bin/user/info?';
	const OAUTH_AUTHORIZE_URL = '/authorize?';
	const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com';
	const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
	const OAUTH_USERINFO_URL = '/sns/userinfo?';
	public function __construct() {
		parent::__construct();
		$config = InitPHP::getConfig();
		$this->doMain=$config['url'];
		$this->wxConfig=$config['wxConfig'];
        $this->_appId =$this->wxConfig['appid'];
        $this->_appSecret =$this->wxConfig['appsecret'];
        $this->_callback = $config['url'].'index.php?c=login&a=weixin';//$this->doMain.
		$this->logDo = $this->getUtil('log');
	}
	public function callback($callback){//设置其他的回调地址
		$this->_callback =$callback;
	}
    /**
     * 提交微信登录请求
     */
    public function wxLogin(){
		if(isset($_GET['code'])){
			$data=$this->getOauthAccessToken();
			$userInfo=$this->getOauthUserinfo($data['access_token'],$data['openid']);
		}else{
			$stats= $this->getRandChar(16);//该参数可用于防止csrf攻击（跨站请求伪造攻击）
			$toUrl=$this->getOauthRedirect($this->_callback,$stats,'snsapi_userinfo');
			header("Location:".$toUrl);die;
		}

		return $userInfo;
    }
//生成随机数,length长度
   public function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<$length;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        return $str;
    }
    /**
	 * oauth 授权跳转接口
	 * @param string $callback 回调URI
	 * @return string
	 */
	public function getOauthRedirect($scope='snsapi_userinfo',$redirect_uri=false){
		$redirect_uri=empty($redirect_uri)?$this->_callback:$redirect_uri;
		$stats= $this->getRandChar(16);
		return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->_appId.'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
	}

	public function getOauthAccessToken($code){
		$code = isset($_GET['code'])?$_GET['code']:$code;
		if (!$code) return false;
		$result = $this->httpRequest(self::API_BASE_URL_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->_appId.'&secret='.$this->_appSecret.'&code='.$code.'&grant_type=authorization_code');
		if ($result){
			$json = json_decode($result,true);
			$data['access_token'] = $json['access_token'];
			$data['openid'] = $json['openid'];
			return $data;
		}
		return false;
	}
    public function getOauthUserinfo($access_token,$openid){
		$result = $this->httpRequest(self::API_BASE_URL_PREFIX.self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
		if ($result){
			$json = json_decode($result,true);
			return $json;
		}
		return false;
	}
	/**
	 *调用的接口不同，用于获取subscribe，判断是否关注
	 */
	public function getOauthUserInfoCGI($access_token,$openid){
	    $result = $this->httpRequest(self::API_BASE_URL_PREFIX.self::OAUTH_CGI_URL.'access_token='.$access_token.'&openid='.$openid);
	    if ($result){
	        $json = json_decode($result,true);
	        return $json;
	    }
	    return false;
	}

	/**
	 * CURL请求
	 * @param $url 请求url地址
	 * @param $method 请求方法 get post
	 * @param null $postfields post数据数组
	 * @param array $headers 请求header信息
	 * @param bool|false $debug  调试开启 默认false
	 * @return mixed
	 */
	private function httpRequest($url, $method="GET", $postfields = null, $headers = array(), $debug = false) {
		$method = strtoupper($method);
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ci, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.2; WOW64; rv:34.0) Gecko/20100101 Firefox/34.0");
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, 60); /* 在发起连接前等待的时间，如果设置为0，则无限等待 */
		curl_setopt($ci, CURLOPT_TIMEOUT, 7); /* 设置cURL允许执行的最长秒数 */
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, true);
		switch ($method) {
			case "POST":
				curl_setopt($ci, CURLOPT_POST, true);
				if (!empty($postfields)) {
					$tmpdatastr = is_array($postfields) ? http_build_query($postfields) : $postfields;
					curl_setopt($ci, CURLOPT_POSTFIELDS, $tmpdatastr);
				}
				break;
			default:
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, $method); /* //设置请求方式 */
				break;
		}
		$ssl = preg_match('/^https:\/\//i',$url) ? TRUE : FALSE;
		curl_setopt($ci, CURLOPT_URL, $url);
		if($ssl){
			curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE); // https请求 不验证证书和hosts
			curl_setopt($ci, CURLOPT_SSL_VERIFYHOST, FALSE); // 不从证书中检查SSL加密算法是否存在
		}
		//curl_setopt($ci, CURLOPT_HEADER, true); /*启用时会将头文件的信息作为数据流输出*/
		curl_setopt($ci, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ci, CURLOPT_MAXREDIRS, 2);/*指定最多的HTTP重定向的数量，这个选项是和CURLOPT_FOLLOWLOCATION一起使用的*/
		curl_setopt($ci, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ci, CURLINFO_HEADER_OUT, true);
		/*curl_setopt($ci, CURLOPT_COOKIE, $Cookiestr); * *COOKIE带过去** */
		$response = curl_exec($ci);
		$requestinfo = curl_getinfo($ci);
		$http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		if ($debug) {
			echo "=====post data======\r\n";
			var_dump($postfields);
			echo "=====info===== \r\n";
			print_r($requestinfo);
			echo "=====response=====\r\n";
			print_r($response);
		}
		curl_close($ci);
		return $response;
		//return array($http_code, $response,$requestinfo);
	}

    private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
}