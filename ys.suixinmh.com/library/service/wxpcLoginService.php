<?php
/**
 * @author gaoguofeng
 */
class wxpcLoginService extends Service{
    private $_appId;
    private $_appSecret;
    private $_callback;
	private $logDo;
	private $doMain;
	private $wxConfig;
	const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect';
	const OAUTH_AUTHORIZE_URL = '/qrconnect?';
	const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com';
	const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
	const OAUTH_USERINFO_URL = '/sns/userinfo?';
	public function __construct() {
		parent::__construct();
		$config = InitPHP::getConfig();
		$this->doMain=$config['www_url'];
		$this->wxConfig=$config['wxLoginConfig'];
        $this->_appId =$this->wxConfig['appid'];
        $this->_appSecret =$this->wxConfig['appsecret'];
        $this->_callback = 'http://www.xiaoshuokong.com/wxLogin.php';
		$this->logDo = $this->getUtil('log');
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
	public function getOauthRedirect($scope='snsapi_login',$redirect_uri=false){
		$redirect_uri=empty($redirect_uri)?$this->_callback:$redirect_uri;
		$stats= $this->getRandChar(16);
		return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->_appId.'&redirect_uri='.urlencode($redirect_uri).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
	}
    
	public function getOauthAccessToken($code){
		$code = isset($_GET['code'])?$_GET['code']:$code;
		if (!$code) return false;
		$result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->_appId.'&secret='.$this->_appSecret.'&code='.$code.'&grant_type=authorization_code');
		if ($result){
			$json = json_decode($result,true);
			$data['access_token'] = $json['access_token'];
			$data['openid'] = $json['openid'];
			return $data;
		}
		return false;
	}
    public function getOauthUserinfo($access_token,$openid){
		$result = $this->http_get(self::API_BASE_URL_PREFIX.self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);
		if ($result){
			$json = json_decode($result,true);
			return $json;
		}
		return false;
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