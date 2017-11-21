<?php
/**
 * @author gaoguofeng
 */
class authorUserService extends Service{
	/**
	 * 全局使用方法：InitPHP::getDao($daoname, $path = '')
	 * @param string $daoname 服务名称
	 * @param string $path 模块名称
	 * @return object
	 */
	private $authorUserDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->authorUserDao = InitPHP::getDao("authorUser");//实例并且单例模式获取Dao
		$this->logDo = $this->getUtil('log');
	}
	public function insert($data) {
		return $this->authorUserDao->insertUser($data);
	}
	public function userLookPayPage($user_id,$bookId=0,$cid=0){
		if(empty($user_id)){
			return array();
		}
		return $this->authorUserDao->userLookPayPage($user_id,$bookId,$cid);
	}
	public function getAuthorInfo($whereArray){
		if(empty($whereArray)){
			return array();
		}
		return $this->authorUserDao->getAuthorInfo($whereArray);
	}
	public function orderPaySum($data) {
		return $this->authorUserDao->orderPaySum($data);
	}
	public function userPayRecord($payData) {
		if(empty($payData)){
			return false;
		}
		return $this->authorUserDao->userPayRecord($payData);
	}
	public function userPayOrder($orderData,$userMoneyCoin) {
        if(empty($orderData) || $userMoneyCoin<0 ){
			return false;
		}
		$orderId=$this->authorUserDao->userPayOrder($orderData,$userMoneyCoin);
		return $orderId;
	}
	public function userGratuityOrder($orderData,$userMoneyCoin) {
        if(empty($orderData) || $userMoneyCoin<0 ){
			return false;
		}
		$orderId=$this->authorUserDao->userGratuityOrder($orderData,$userMoneyCoin);
		return $orderId;
	}
	public function upUser($id,$data,$openid=false){
		return $this->authorUserDao->upUser($id,$data,$openid);
	}
	public function upUserInfo($user_id,$data,$openid){
		if ($user_id < 1) {
			return array();
		}
		return $this->authorUserDao->upUserInfo($user_id,$data);
	}
	public function getUserBook($userId,$pageNum=10,$p=1){
		if(empty($userId)){
			return false;
		}
		return $this->authorUserDao->getUserBook($userId,$pageNum,$p);
	}
	public function getUserBookCount($userId){
		if(empty($userId)){
			return false;
		}
		return $this->authorUserDao->getUserBookCount($userId);
	}
	public function getOneByField($whereArray,$unionid=false){
		if(empty($whereArray)){
			return array();
		}
		return $this->authorUserDao->getOneByField($whereArray,$unionid);
	}
	public function getRecordAll($pageNum,$p=1,$where){
		return $this->authorUserDao->getRecordAll($pageNum,$p,$where);
	}
	public function getUserOrder($pageNum,$p=1,$where){
		return $this->authorUserDao->getUserOrder($pageNum,$p,$where);
	}
	public function getUserOrderOne($whereArray){
		return $this->authorUserDao->getUserOrderOne($whereArray);
	}
	public function getOnePayRecord($whereArray){
		if(empty($whereArray)){
			return array();
		}
		return $this->authorUserDao->getOnePayRecord($whereArray);
	}
	public function getUser($id,$openid=false){
		if(empty($id)){
			return array();
		}
		if(empty($openid)){
			return $this->authorUserDao->getUser($id);
		}else{
			return $this->authorUserDao->getopenidUser($id);
		}

	}
	public function updo(){
		$this->authorUserDao->updo();
	}
	public function getUserInfo($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->authorUserDao->getUserInfo($whereArray);
	}

	public function getAuthorMessage($book_id){
	    if(empty($book_id)){
	        return  array();
	    }
	    return  $this->authorUserDao->getAuthorMessage($book_id);
	}

	public function getMoney($user_id) {
		if(empty($user_id)){
			return false;
		}
		$sql="select moneyCoin from user_info where user_id={$user_id}";
		return $this->authorUserDao->getOneSql($sql);
	}
	public function upPassword($phone,$data){
		if(empty($phone)){
			return false;
		}
		return $this->authorUserDao->upPassword($phone,$data);
	}

	public function smtEmail($to,$title,$body) {
		$smtpserver = "smtp.mxhichina.com";//SMTP服务器
		$smtpserverport =25;//SMTP服务器端口
		$smtpusermail = "postmaster@qimiaoer.com";//SMTP服务器的用户邮箱
		$smtpemailto = $to;//发送给谁
		$smtpuser = "postmaster@qimiaoer.com";//SMTP服务器的用户帐号
		$smtppass = "Shenhai456";//SMTP服务器的用户密码
		$mailsubject = $title;//邮件主题
		$mailbody = $body;//邮件内容
		$mailtype = "HTML";//邮件格式（HTML/TXT）,TXT为文本邮件
		$smtp = $this->getLibrary('email');//加载email类
		$smtp->config($smtpserver,$smtpserverport,true,$smtpuser,$smtppass);
		$smtp->debug = FALSE;//是否显示发送的调试信息
		if($smtp->sendmail($smtpemailto, $smtpusermail, $mailsubject, $mailbody, $mailtype)){
			return true;
		}else{
			return false;
		}
	}

    /*发送短信*/
	public function sendsms($phone,$code,$template){
		$url='http://sms.xiaoshuokong.com/smsDo.php';
		$SMSPost['phone']=$phone;
		$SMSPost['code']=$code;
		$SMSPost['template']=$template;
		$gets =self::httpPost($SMSPost,$url);
		return $gets;
	}

    public static function httpPost($curlPost,$url){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
		$return_str = curl_exec($curl);
		curl_close($curl);
		return $return_str;
	}

}
