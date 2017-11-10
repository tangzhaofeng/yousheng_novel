<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
class papPayInit {
	private $_appId;
    private $_appSecret;
	private $_mch_id;
	private $_key;
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	const ENTRUSTWEB = 'https://api.mch.weixin.qq.com/papay/entrustweb';//签约
	const PAPPAYAPPLY = 'https://api.mch.weixin.qq.com/pay/pappayapply';//扣费
	const DELETECONTRACT = 'https://api.mch.weixin.qq.com/papay/deletecontract';//解约
	
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
	public function deletecontract($data){
		$data['appid']=$this->_appId;
		$data['mch_id']=$this->_mch_id;
		$data['contract_termination_remark']='不想图书包月了';
		$data['version ']='1.0';
		$data['sign']=$this->MakeSign($data);
		$xmlData=$this->To2Xml($data);
		
		$content=$xmlData;
		$time=time();
        $log_str="$time   \n$content\n------------------\n";
        $file_n="DELETECONTRACT.txt";
        $file=fopen("./data/$file_n", "a+");
        fwrite($file, $log_str);
        fclose($file);

		
		$response=self::postXmlCurl($xmlData,self::DELETECONTRACT);
		return $response;
	}
	public function pappayapply($data,$ip=false){
		$data['appid']=$this->_appId;
		$data['mch_id']=$this->_mch_id;
		$data['nonce_str']=self::getNonceStr();
		if(!empty($ip)){
			$data['spbill_create_ip']=$ip;
		}else{
			$data['spbill_create_ip']=self::getIp();
		}
		$data['trade_type']='PAP';
		$data['sign']=$this->MakeSign($data);
		$xmlData=$this->To2Xml($data);
		$response=self::postXmlCurl($xmlData,self::PAPPAYAPPLY);
		return $response;
	}
	public function printr($array){
		echo "<pre>";print_r($array);echo "<pre>";
	}
	/**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
	public function FromXml($xml){	
		if(!$xml){
			die("xml数据异常！");
		}
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $values;
	}
	/**
	 * 输出xml字符
	 * @throws WxPayException
	**/
	public function ToXml($values){
		if(!is_array($values) || count($values) <= 0){
    		die("数组数据异常！");
    	}
    	$xml = '<xml>';
    	foreach ($values as $key=>$val){
    		if (is_numeric($val)){
    			$xml.="<".$key.">".$val."</".$key.">";
    		}else{
    			$xml.='<'.$key.'><![CDATA['.$val.']]></'.$key.'>';
    		}
        }
        $xml.='</xml>';
        return $xml; 
	}
	public function To2Xml($values){
		if(!is_array($values) || count($values) <= 0){
    		die("数组数据异常！");
    	}
    	$xml = '<xml>';
    	foreach ($values as $key=>$val){
    		$xml.="<".$key.">".$val."</".$key.">";
        }
        $xml.='</xml>';
        return $xml; 
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
	/**
	 * 以post方式提交xml到对应的接口url
	 * 
	 * @param string $xml  需要post的xml数据
	 * @param string $url  url
	 * @param bool $useCert 是否需要证书，默认不需要
	 * @param int $second   url执行超时时间，默认30s
	 * @throws WxPayException
	 */
	private static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
	{		
		$ch = curl_init();
		//设置超时
		curl_setopt($ch, CURLOPT_TIMEOUT, $second);
		
		//如果有配置代理这里就设置代理
		if(self::CURL_PROXY_HOST != "0.0.0.0" 
			&& self::CURL_PROXY_PORT != 0){
			curl_setopt($ch,CURLOPT_PROXY, self::CURL_PROXY_HOST);
			curl_setopt($ch,CURLOPT_PROXYPORT, self::CURL_PROXY_PORT);
		}
		curl_setopt($ch,CURLOPT_URL, $url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TRUE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,2);//严格校验
		//设置header
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		//要求结果为字符串且输出到屏幕上
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	
		if($useCert == true){
			//设置证书
			//使用证书：cert 与 key 分别属于两个.pem文件
			curl_setopt($ch,CURLOPT_SSLCERTTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLCERT, self::SSLCERT_PATH);
			curl_setopt($ch,CURLOPT_SSLKEYTYPE,'PEM');
			curl_setopt($ch,CURLOPT_SSLKEY, self::SSLKEY_PATH);
		}
		//post提交方式
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		//运行curl
		$data = curl_exec($ch);
		//返回结果
		if($data){
			curl_close($ch);
			return $data;
		} else { 
			$error = curl_errno($ch);
			curl_close($ch);
			die("curl出错，错误码:$error");
		}
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
