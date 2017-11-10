<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');   
class wxPcPayInit {
	private $_appId;
    private $_appSecret;
    private $_callback;
	private $logDo;
	private $doMain;
	private $_mch_id;
	private $_key;
	private $values;
	const REPORT_LEVENL = 1;
	const CURL_PROXY_HOST = "0.0.0.0";//"10.152.18.220";
	const CURL_PROXY_PORT = 0;//8080;
	const SSLCERT_PATH = '../cert/apiclient_cert.pem';
	const SSLKEY_PATH = '../cert/apiclient_key.pem';
	const UNIFIED_ORDER= 'https://api.mch.weixin.qq.com/pay/unifiedorder';
	const ORDER_QUERY= 'https://api.mch.weixin.qq.com/pay/orderquery';
	
	public function config($config) {
		$this->_appId = $config['appid'];
		$this->_appSecret = $config['appsecret'];
		$this->_mch_id = $config['mch_id'];
		$this->_key = $config['key'];
		$this->_callback = $config['h5Pay_callback'];
	}
	public function unifiedorder($data){
		$data['appid']=$this->_appId;
		$data['mch_id']=$this->_mch_id;
		$data['nonce_str']=self::getNonceStr();
		$data['notify_url']=$this->_callback;
		$data['detail']='微信阅读币充值';
		$data['spbill_create_ip']=self::getIp();
		$data['sign']=$this->MakeSign($data);
		$xmlData=$this->ToXml($data);
		$response=self::postXmlCurl($xmlData,self::UNIFIED_ORDER);
		$result = $this->FromXml($response);
		//print_r($response);
		//print_r($result);
		return $result;
	}
	public function orderquery($out_trade_no){
		$data['appid']=$this->_appId;
		$data['mch_id']=$this->_mch_id;
		$data['nonce_str']=self::getNonceStr();
		$data['product_id']=$out_trade_no;
		$data['time_stamp']=time();
		$data['sign']=$this->MakeSign($data);
		$URI="weixin://wxpay/bizpayurl?appid={$data['appid']}&mch_id={$data['mch_id']}&nonce_str={$data['nonce_str']}&product_id={$data['product_id']}&time_stamp={$data['time_stamp']}&sign={$data['sign']}";
		return $URI;
	}
	/**
	 * 格式化参数格式化成url参数
	 */
	public function ToUrlParams($values){
		$buff = "";
		foreach ($values as $k => $v)
		{
			if($k != "sign" && $v != "" && !is_array($v)){
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
	public function MakeSign($values)
	{
		//签名步骤一：按字典序排序参数
		ksort($values);
		$string = $this->ToUrlParams($values);
		//签名步骤二：在string后加入KEY
		$string = $string . "&key=".$this->_key;
		//签名步骤三：MD5加密
		$string = md5($string);
		//签名步骤四：所有字符转为大写
		$result = strtoupper($string);
		return $result;
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
	/**
	 * 获取毫秒级别的时间戳
	 */
	private static function getMillisecond()
	{
		//获取毫秒的时间戳
		$time = explode ( " ", microtime () );
		$time = $time[1] . ($time[0] * 1000);
		$time2 = explode( ".", $time );
		$time = $time2[0];
		return $time;
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
	public function SetInterface_url($value)
	{
		$this->values['interface_url'] = $value;
	}
	/**
	* 设置接口耗时情况，单位为毫秒
	* @param string $value 
	**/
	public function SetExecute_time_($value)
	{
		$this->values['execute_time_'] = $value;
	}
	public function SetReturn_code($return_code)
	{
		$this->values['return_code'] = $return_code;
	}
	/**
	 * 
	 * 设置错误信息
	 * @param string $return_code
	 */
	public function SetReturn_msg($return_msg)
	{
		$this->values['return_msg'] = $return_msg;
	}
	/**
	* 设置结果信息描述
	* @param string $value 
	**/
	public function SetErr_code_des($value)
	{
		$this->values['err_code_des'] = $value;
	}
	/**
	* 设置商户系统内部的订单号，当没提供transaction_id时需要传这个。
	* @param string $value 
	**/
	public function SetOut_trade_no($value)
	{
		$this->values['out_trade_no'] = $value;
	}
	/**
	* 设置微信支付分配的终端设备号，商户自定义
	* @param string $value 
	**/
	public function SetDevice_info($value)
	{
		$this->values['device_info'] = $value;
	}
	/**
	* 设置SUCCESS/FAIL
	* @param string $value 
	**/
	public function SetResult_code($value)
	{
		$this->values['result_code'] = $value;
	}
	/**
	 * 
	 * 上报数据， 上报的时候将屏蔽所有异常流程
	 * @param string $usrl
	 * @param int $startTimeStamp
	 * @param array $data
	 */
	private static function reportCostTime($url, $startTimeStamp, $data)
	{
		//如果不需要上报数据
		if(self::REPORT_LEVENL == 0){
			return;
		} 
		//如果仅失败上报
		if(self::REPORT_LEVENL == 1 &&
			 array_key_exists("return_code", $data) &&
			 $data["return_code"] == "SUCCESS" &&
			 array_key_exists("result_code", $data) &&
			 $data["result_code"] == "SUCCESS")
		 {
		 	return;
		 }
		 
		//上报逻辑
		$endTimeStamp = self::getMillisecond();
		$this->SetInterface_url($url);
		$this->SetExecute_time_($endTimeStamp - $startTimeStamp);
		//返回状态码
		if(array_key_exists("return_code", $data)){
			$this->SetReturn_code($data["return_code"]);
		}
		//返回信息
		if(array_key_exists("return_msg", $data)){
			$this->SetReturn_msg($data["return_msg"]);
		}
		//业务结果
		if(array_key_exists("result_code", $data)){
			$this->SetResult_code($data["result_code"]);
		}
		
		//错误代码描述
		if(array_key_exists("err_code_des", $data)){
			$this->SetErr_code_des($data["err_code_des"]);
		}
		//商户订单号
		if(array_key_exists("out_trade_no", $data)){
			$this->SetOut_trade_no($data["out_trade_no"]);
		}
		//设备号
		if(array_key_exists("device_info", $data)){
			$this->SetDevice_info($data["device_info"]);
		}
		
		try{
			self::report($objInput);
		} catch (WxPayException $e){
			//不做任何处理
		}
	}
}
