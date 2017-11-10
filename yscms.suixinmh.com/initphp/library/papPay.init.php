<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
class papPayInit {
	private $_appId;
    private $_appSecret;
	private $_mch_id;
	private $_key;
	const ENTRUSTWEB = 'https://api.mch.weixin.qq.com/papay/entrustweb';
	
	public function config($config) {
		$this->_appId = $config['appid'];
		$this->_appSecret = $config['appsecret'];
		$this->_mch_id = $config['mch_id'];
		$this->_key = $config['key'];
	}
	
	public function fromPap($data){
		$data['appid']=$this->_appId;//公众账号id
		$data['mch_id']=$this->_mch_id;//商户号
		$data["version"]='1.0';
		$data["timestamp"]=time();
		//$this->printr($data);die;
		$data["sign"]=$this->MakeSign($data);
		$data["notify_url"]=urlencode($data["notify_url"]);
		ksort($data);
		$url=$this->ToUrlParams($data);
		return self::ENTRUSTWEB."?".$url;
	}
	public function printr($array){
		echo "<pre>";print_r($array);echo "<pre>";
	}
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams($Parameters){
		$buff = "";
		foreach ($Parameters as $k => $v){
			if($v != "" && !is_array($v)){
				$buff .= $k . "=" . $v . "&";
			}
		}
		$buff = trim($buff, "&");
		return $buff;
	}
	
	/**
	 * 生成签名
	 * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
	 */
	public function MakeSign($Parameters)
	{
		//签名步骤一：按字典序排序参数
		ksort($Parameters);
		$string = $this->ToUrlParams($Parameters);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->_key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
	}
	
	/**
	 * 
	 * 产生随机字符串，不长于32位
	 * @param int $length
	 * @return 产生的随机字符串
	 */
	public static function getNonceStr($length = 32) 
	{
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";  
		$str ="";
		for ( $i = 0; $i < $length; $i++ )  {  
			$str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);  
		} 
		return $str;
	}
	public static function getIp(){
		$cip="unknown";
		if($_SERVER['REMOTE_ADDR']){
			$cip=$_SERVER['REMOTE_ADDR'];
		}elseif(getenv('REMOTE_ADDR')){
			$cip=getenv('REMOTE_ADDR');
		}
		return $cip;
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
