<?php
/**
 * @author gaoguofeng
 *
 */
class userService extends Service{
	private $userDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->userDao = InitPHP::getDao("user");
		$this->logDo = $this->getUtil('log');
	}
	public function userList($pageNum,$p=1,$field=array("1"=>1),$other=false){
		return $this->userDao->userList($pageNum,$p,$field,$other);
	}
	public function userPapList($pageNum,$p=1,$field=array("1"=>1),$other=false){
		return $this->userDao->userPapList($pageNum,$p,$field,$other);
	}
	public function userPayList($userWhere,$recordWhere,$pageNum=20,$p=1){
		$list=$this->userDao->userPayList($userWhere,$recordWhere,$pageNum,$p);
		foreach($list['list'] as $k=>$v){
			$userInfo=$this->userDao->getUser($v['user_id']);
			$list['list'][$k]['priceSource']=$userInfo['third_source'];
		}
		return $list;
	}
	//统计渠道用户数
	public function thirdSourceUser($third_source,$start_time,$end_time){
		return $this->userDao->thirdSourceUser($third_source,$start_time,$end_time);
	}
	//统计渠道用户充值
	public function thirdSourcePayRecord($third_source,$start_time,$end_time){
		return $this->userDao->thirdSourcePayRecord($third_source,$start_time,$end_time);
	}
	//统计充值用户数
	public function thirdSourcePayUser($third_source,$start_time,$end_time){
		return $this->userDao->thirdSourcePayUser($third_source,$start_time,$end_time);
	}
	//统计渠道访问数
	public function thirdSourceAllSum($third_source,$start_time,$end_time){
		return $this->userDao->thirdSourceAllSum($third_source,$start_time,$end_time);
	}
	public function userLookPage($start_time,$end_time,$book_id,$p=1,$pageNum=25){
		return $this->userDao->userLookPage($start_time,$end_time,$book_id,$p,$pageNum);
	}
	public function userPaySum($userWhere,$recordWhere){
		return $this->userDao->userPaySum($userWhere,$recordWhere);
	}
	public function userPaySumLook($user_id,$start_time,$end_time){
		return $this->userDao->userPaySumLook($user_id,$start_time,$end_time);
	}
	public function lookOrderPay($user_id,$time){
		return $this->userDao->lookOrderPay($user_id,$time);
	}
	public function historyBook($chapter_id,$third_source){
		return $this->userDao->historyBook($chapter_id,$third_source);
	}
	public function getsourceUserAll($third_source){
		return $this->userDao->getsourceUserAll($third_source);
	}
	public function regSum($dataTime,$third_source){
		return $this->userDao->regSum($dataTime,$third_source);
	}
	public function thirdSourceSum($dataTime,$third_source){
		return $this->userDao->thirdSourceSum($dataTime,$third_source);
	}
	public function TJpaySum($dataTime,$third_source,$pay_source){
		return $this->userDao->TJpaySum($dataTime,$third_source,$pay_source);
	}
	public function thirdSourceList(){
		return $this->userDao->thirdSourceList();
	}
	//统计书籍并以销售记录排序
	public function bookOrderSumList($data,$bookId=0){
		return $this->userDao->bookOrderSumList($data,$bookId);
	}
	public function bookOrderUserCount($bookId,$data){
		return $this->userDao->bookOrderUserCount($bookId,$data);
	}
    public function sendsms($mobile,$content){
    }

    public function post($curlPost,$url){
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
