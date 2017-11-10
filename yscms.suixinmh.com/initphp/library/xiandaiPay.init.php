<?php
if (!defined('IS_INITPHP')) exit('Access Denied!');
class xiandaiPayInit{
	static $appId="1466697906007330";//商户的应用ID
    static $secure_key="GbKPd02BEf15yapDDIyqt18MbtyXkCbl";//商户的秘钥
    static $timezone="Asia/Shanghai";//时间时区
    static $trade_time_out="3600";
    static $front_notify_url="http://wap.manyuedu.cn/frontxiandai.php";
    static $back_notify_url="http://wap.manyuedu.cn/notify_xiandai.php";
        
    const TRADE_URL="http://api.ipaynow.cn";
    const QUERY_URL="http://api.ipaynow.cn";
    const TRADE_FUNCODE="WP001";
    const QUERY_FUNCODE="MQ001";
    const NOTIFY_FUNCODE="N001";
    const FRONT_NOTIFY_FUNCODE="N002";
    const TRADE_TYPE="01";
    const TRADE_CURRENCYTYPE="156";
    const TRADE_CHARSET="UTF-8";
    const TRADE_DEVICE_TYPE="06";
    const TRADE_SIGN_TYPE="MD5";
    const TRADE_QSTRING_EQUAL="=";
    const TRADE_QSTRING_SPLIT="&";
    const TRADE_FUNCODE_KEY="funcode";
    const TRADE_DEVICETYPE_KEY="deviceType";
    const TRADE_SIGNTYPE_KEY="mhtSignType";
    const TRADE_SIGNATURE_KEY="mhtSignature";
    const SIGNATURE_KEY="signature";
    const SIGNTYPE_KEY="signType";
    const VERIFY_HTTPS_CERT=false;

	public function trade(Array $params) {
		$params["funcode"]=self::TRADE_FUNCODE;
        $params["appId"]=self::$appId;//应用ID
        $params["mhtOrderType"]=self::TRADE_TYPE;
        $params["mhtCurrencyType"]=self::TRADE_CURRENCYTYPE;
        $params["mhtOrderTimeOut"]=self::$trade_time_out;
        $params["mhtOrderStartTime"]=date("YmdHis");
        $params["notifyUrl"]=self::$back_notify_url;
        $params["frontNotifyUrl"]=self::$front_notify_url;
        $params["mhtCharset"]=self::TRADE_CHARSET;
        $params["deviceType"]=self::TRADE_DEVICE_TYPE;
        $params["mhtReserved"]="test";
        $params["mhtSignature"]=self::buildSignature($params);
        $params["mhtSignType"]=self::TRADE_SIGN_TYPE;
		return self::createLinkString($params, false, true);
    }
    public function query(Array $params,Array &$resp) {
		$req_str=self::buildReq($params);
		$resp_str=self::sendMessage($req_str, self::QUERY_URL);
		return self::verifyResponse($resp_str, $resp);
    }
	public static function buildSignature(Array $params) {
		$filteredReq=self::paraFilter($params);
		return self::CorebuildSignature($filteredReq);
	}
	private static function buildReq(Array $params) {
		return self::createLinkString($params, false, true);
	}
	public function verifySignature($para){
		$respSignature=$para[self::SIGNATURE_KEY];
		$filteredReq=self::paraFilter($para);
		unset($filteredReq[self::SIGNATURE_KEY]);
		unset($filteredReq[self::SIGNTYPE_KEY]);
		$signature=self::CorebuildSignature($filteredReq);
		if ($respSignature!=""&&$respSignature==$signature) {
			return TRUE;
		}else {
			return FALSE;
		}
    }
	public static function verifyResponse($resp_str,&$resp){
		if ($resp_str!="") {
			parse_str($resp_str,$para);
			$signIsValid=$this->verifySignature($para);
			$resp=$para;
            if ($signIsValid) {
				return TRUE;
            }else{
				return FALSE;
            }
        }
    }
	public static function paraFilter($params){
		$result=array();
            $flag=$params[self::TRADE_FUNCODE_KEY];
            foreach($params as $key => $value){
                if (($flag==self::TRADE_FUNCODE)&&!($key==self::TRADE_FUNCODE_KEY||$key==self::TRADE_DEVICETYPE_KEY ||$key==self::TRADE_SIGNTYPE_KEY||$key==self::TRADE_SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if(($flag==self::NOTIFY_FUNCODE||$flag==self::FRONT_NOTIFY_FUNCODE)&&!($key==self::SIGNTYPE_KEY||$key==self::SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
                if (($flag==self::QUERY_FUNCODE)&&!($key==self::TRADE_SIGNTYPE_KEY || $key==self::TRADE_SIGNATURE_KEY || $key==self::SIGNTYPE_KEY ||$key==self::SIGNATURE_KEY)){
                    $result[$key]=$value;
                    continue;
                }
            }
            return $result;
        }
        
        public static function CorebuildSignature(Array $para){
            $prestr=self::createLinkString($para, true, false);
            $prestr.=self::TRADE_QSTRING_SPLIT.md5(self::$secure_key);
            return md5($prestr);
        }
        public static function createLinkString(Array $para,$sort,$encode) {
            if ($sort) {
                $para=self::argSort($para);
            }
            foreach ($para as $key => $value){
                if ($encode) {
                    $value=urlencode($value);
                }
                $linkStr.=$key.self::TRADE_QSTRING_EQUAL.$value.self::TRADE_QSTRING_SPLIT;
            }
            $linkStr=substr($linkStr, 0,count($linkStr)-2);
            return $linkStr;
        }
        private static function argSort($para) {
            ksort($para);
            reset($para);
            return $para;
        }
		static function sendMessage($req_content,$url) {
            if(function_exists("curl_init")){
                $curl=  curl_init();
                $option=array(
                    CURLOPT_POST=>1,
                    CURLOPT_POSTFIELDS=>$req_content,
                    CURLOPT_URL=>$url,
                    CURLOPT_RETURNTRANSFER=>1,
                    CURLOPT_HEADER=>0,
                    CURLOPT_SSL_VERIFYPEER=>  self::VERIFY_HTTPS_CERT,
                    CURLOPT_SSL_VERIFYHOST=>  self::VERIFY_HTTPS_CERT
                );
                curl_setopt_array($curl, $option);
                $resp_data=  curl_exec($curl);
                if($resp_data==FALSE){
                    curl_close($curl);
                }else{
                    curl_close($curl);
                    return $resp_data;
                }
            }
        }
		
		public function QueryOrder() {
            $req=array();
            $req["funcode"]=self::QUERY_FUNCODE;
            $req["appId"]=self::$appId;
            $req["mhtOrderNo"]="";//商户欲查询交易订单号
            $req["mhtCharset"]=self::TRADE_CHARSET;
            $req["mhtSignature"]=self::buildSignature($req);
            $req["mhtSignType"]=self::TRADE_SIGN_TYPE;
           
            $resp=array();
            self::query($req, $resp);
            print_r($resp);
        }
}
