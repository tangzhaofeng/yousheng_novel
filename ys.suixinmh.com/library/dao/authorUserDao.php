<?php 
class authorUserDao extends Dao{
	public $table_name = 'user';
	private $fields = "openid,unionid,phone,password,source,third_source,state,vip,create_time";
	public $table_info = 'user_info';
	private $fields_info = "user_id,name,sex,headPic,moneyCoin,up_time";
	public $table_pay_record = 'pay_record';
	private $fields_pay_record = "source,out_trade_no,trade_no,money,moneyCoin,give,scale,user_id,create_time";
	public $table_book_order = 'book_order';
	private $fields_book_order = "book_id,chapter,user_id,price,create_time";
	public $table_gratuity_order = 'gratuity_order';
	private $fields_gratuity_order = "user_id,book_id,chapter_id,type,price,create_time";
	public $table_user_look_pay_page = 'user_look_pay_page';
	private $fields_user_look_pay_page = "user_id,book_id,cid,pageNum,year,month,day";

	public function insertUser($data){
		$infodata['name']=empty($data['nickname'])?"HY".date("ymdhis",time()):$data['nickname'];
		$infodata['sex']=$data['sex'];
		$infodata['headPic']=empty($data['headPic'])?'':$data['headPic'];
		$infodata['moneyCoin']=empty($data['moneyCoin'])?0:$data['moneyCoin'];
		$infodata['create_time']=time();
		unset($data['nickname']);
		unset($data['sex']);
		unset($data['headPic']);
		unset($data['moneyCoin']);
		$this->init_db()->transaction_start();
		$admindata = $this->init_db()->build_key($data, $this->fields);
		$user_id=$this->init_db()->insert($admindata, $this->table_name);
		$infodata['user_id']=$user_id;
		$infodata = $this->init_db()->build_key($infodata, $this->fields_info);
		$info_id=$this->init_db()->insert($infodata, $this->table_info);
		if($user_id && $info_id){
			$this->init_db()->transaction_commit();
			return $user_id;
		}else{
			$this->init_db()->transaction_rollback();
			return false;
		}
	}
	/**
	 * 用户充值与记录
	 * @param $user
	 */
	public function userLookPayPage($user_id,$bookId=0,$cid=0){
		if(!empty($bookId)){
			$data['book_id']=$bookId;
		}
		if(!empty($cid)){
			$data['cid']=$cid;
		}
		$data['user_id']=$user_id;
		$data['year']=date("Y");
		$data['month']=date("m");
		$data['day']=date("d");
		//print_r($data);
		$Info=$this->init_db()->get_one_by_field($data,$this->table_user_look_pay_page);
		if(!empty($Info)){
			$datas['pageNum']=$Info['pageNum']+1;
			$id=$this->init_db()->update_by_field($datas,$data,$this->table_user_look_pay_page);
		}else{
			$data['pageNum']=1;
			$datas = $this->init_db()->build_key($data, $this->fields_user_look_pay_page);
			$id=$this->init_db()->insert($datas, $this->table_user_look_pay_page);
		}
		return $id;
	}
	/**
	 * 用户充值与记录
	 * @param $user
	 */
	public function userPayRecord($payData){
		$user_id=$payData['user_id'];
		$userInfo=$this->init_db()->get_one_by_field(array("user_id"=>$user_id),$this->table_info);
		$this->init_db()->transaction_start();
		$data['moneyCoin']=$userInfo['moneyCoin']+$payData['moneyCoin']+$payData['give'];
		$userMoneyDo=$this->init_db()->update_by_field($data,array("user_id"=>$user_id),$this->table_info);
		$payData = $this->init_db()->build_key($payData, $this->fields_pay_record);
		$pay_id=$this->init_db()->insert($payData, $this->table_pay_record);
		if($userMoneyDo && $pay_id){
			$this->init_db()->transaction_commit();
			return $pay_id;
		}else{
			$this->init_db()->transaction_rollback();
			return false;
		}
	}
	
	/**
	 * 用户消费并记录
	 * @param $user
	 */
	public function userPayOrder($orderData,$userMoneyCoin){
		if($userMoneyCoin<$orderData['price'] || empty($orderData['user_id']) || empty($orderData['book_id'])){
			return false;
		}
		$orderData['create_time']=time();
		$this->init_db()->transaction_start();
		//用户余额=用户现有余额+充值人民币*兑换比例；
		$data['moneyCoin']=$userMoneyCoin-$orderData['price'];
		$userMoneyDo=$this->init_db()->update_by_field($data,array('user_id'=>$orderData['user_id']),$this->table_info);
		$orderData = $this->init_db()->build_key($orderData, $this->fields_book_order);
		$order_id=$this->init_db()->insert($orderData, $this->table_book_order);
		if($userMoneyDo && $order_id){
			$this->init_db()->transaction_commit();
			return $order_id;
		}else{
			$this->init_db()->transaction_rollback();
			return false;
		}
	}
	/**
	 * 用户打赏记录
	 * @param $user
	 */
	public function userGratuityOrder($orderData,$userMoneyCoin){
		if($userMoneyCoin<$orderData['price'] || empty($orderData['user_id']) || empty($orderData['book_id'])){
			return false;
		}
		$orderData['create_time']=time();
		$this->init_db()->transaction_start();
		$data['moneyCoin']=$userMoneyCoin-$orderData['price'];
		$userMoneyDo=$this->init_db()->update_by_field($data,array('user_id'=>$orderData['user_id']),$this->table_info);
		$orderData = $this->init_db()->build_key($orderData, $this->fields_gratuity_order);
		$order_id=$this->init_db()->insert($orderData, $this->table_gratuity_order);
		if($userMoneyDo && $order_id){
			$this->init_db()->transaction_commit();
			return $order_id;
		}else{
			$this->init_db()->transaction_rollback();
			return false;
		}
	}
	public function getGratuityAll($pageNum=20,$p=1){
		$offest=($p-1)*$pageNum;
		$sql="select * from {$this->table_gratuity_order} ORDER BY id desc LIMIT {$offest},{$pageNum}";
		return $this->init_db()->get_all_sql($sql);
	}
	//图书订单消费统计
	public function orderPaySum($data){
		if(!empty($data['start_date']) && !empty($data['end_date'])){
			$andDate=' and b.create_time>='.strtotime($data['start_date']." 00:00:00").' and b.create_time<='.strtotime($data['end_date']." 23:59:59");
		}
		if(!empty($data['user_id'])){
			$andUserId='u.id='.$data['user_id'];
		}else{
			$andUserId='u.id=b.user_id';
		}
		if(!empty($data['third_source'])){
			$andSource=' and u.third_source="'.$data['third_source'].'"';
		}
		if(!empty($data['book_id'])){
			$andBookId=' and b.book_id='.$data['book_id'];
		}
		if(!empty($data['chapter_id'])){
			$andChapterId=' and b.chapter='.$data['chapter_id'];
		}
		$sql='SELECT SUM(b.price) as priceSum FROM book_order AS b WHERE exists(select id from `user` as u where '.$andUserId.$andSource.')'.$andBookId.$andChapterId.$andDate;
		$priceSum=$this->init_db()->get_one_sql($sql);
		return empty($priceSum['priceSum'])?0:$priceSum['priceSum'];
	}
	/**
	 * 通过ID获取一条数据
	 * @param unknown_type $id
	 */
	public function upUser($val,$data,$openid=false){
		if(empty($openid)){
			if(empty($val) || empty($data)){
				return array();
			}
			$return=$this->init_db()->update($val,$data,$this->table_name);
		}else{
			if(empty($val) || empty($data)){
				return array();
			}
			$return=$this->init_db()->update_by_field($data,array("openid"=>$val),$this->table_name);
		}
		return $return;
	}
	public function upPassword($phone,$data){
		if(empty($phone) || empty($data)){
			return array();
		}
		return $this->init_db()->update_by_field($data,array("phone"=>$phone),$this->table_name);
	}
	public function upUserInfo($user_id,$data){
		if(empty($user_id) || empty($data)){
			return array();
		}
		return $this->init_db()->update_by_field($data,array("user_id"=>$user_id),$this->table_info);
	}
	public function getRecordAll($pageNum,$p=1,$where) {
		if(empty($where)){
			$where="1=1";
		}
		$offest=($p-1)*$pageNum;
		$sql="select * from {$this->table_pay_record} where {$where} ORDER BY id desc LIMIT {$offest},{$pageNum}";
		$Csql="select count(*) as num from {$this->table_pay_record} where {$where} ORDER BY id desc";
		$count= $this->init_db()->get_one_sql($Csql);
		$list=$this->init_db()->get_all_sql($sql);
		$data['count']=$count['num'];
		$data['list']=$list;
		return $data;
	}
	public function getUserOrder($pageNum,$p=1,$where){
		if(empty($where)){
			$where="chapter>0";
		}
		$offest=($p-1)*$pageNum;
		$sql="select * from {$this->table_book_order} where {$where} order BY id desc limit {$offest},{$pageNum}";
		$csql="select count(*) as num from {$this->table_book_order} where {$where}";
		$data['list']= $this->init_db()->get_all_sql($sql);
		$count= $this->init_db()->get_one_sql($csql);
		$data["count"]=$count['num'];
		unset($count);
		return $data;
	}

	/**
	 * SQL操作-通过条件语句获取一条信息
	 */
	public function getOneByField($whereArray,$unionid=false) {
		if(empty($whereArray)){
			return array();
		}
		$userInfo=$this->init_db()->get_one_by_field($whereArray,$this->table_name);
		if(empty($userInfo['unionid']) && !empty($whereArray['openid']) && !empty($unionid)){
			$this->upUser($whereArray['openid'],array("unionid"=>$unionid),true);
		}
		return $userInfo;
	}
	public function getOnePayRecord($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_pay_record);
	}
	/**
	 * 通过ID获取一条数据
	 * @param unknown_type $id
	 */
	public function getUser($id){
		if(empty($id)){
			return array();
		}
		$sql="select openid,phone,vip,state from ".$this->table_name." where id={$id}";
		return $this->init_db()->get_one_sql($sql);
	}
	/**
	 * 通过ID获取一条数据
	 * @param unknown_type $id
	 */
	public function getopenidUser($openid){
		if(empty($openid)){
			return array();
		}
		$sql="select id,vip,state from ".$this->table_name." where openid='".$openid."'";
		return $this->init_db()->get_one_sql($sql);
	}
	public function getUserBook($userId,$pageNum=10,$p=1){
		if(empty($userId)){
			return false;
		}
		$offest=($p-1)*$pageNum;
		$sql="select count(*) AS chapterNum,max(create_time) AS createTime,sum(price) as allprice,book_id from ".$this->table_book_order." where user_id={$userId} GROUP BY book_id limit {$offest},{$pageNum}";
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$sqlInfo="select chapter from ".$this->table_book_order." where user_id={$userId} and book_id={$v['book_id']} and create_time={$v['createTime']}";//获取最新看的章节
			$order=$this->init_db()->get_one_sql($sqlInfo);
			$list[$k]['chapter']=$order['chapter'];
		}
		return $list;
	}
	public function getUserBookCount($userId){
		$sql="select book_id from ".$this->table_book_order." where user_id={$userId} GROUP BY book_id";
		$order=$this->init_db()->get_all_sql($sql);
		return count($order);
	}
	public function getUserOrderOne($whereArray){
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_book_order);
	}
	public function updo() {
		$sql="select * from user_pap";
		$userPapL=$this->init_db()->get_all_sql($sql);
		foreach($userPapL as $k=>$v){
			
			$data['user_id']=$v['user_id'];
			$this->init_db()->update_by_field($data,array("out_trade_no"=>$v['contract_code'],"money"=>39),$this->table_pay_record);
		}

	}
	public function getUserInfo($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_info);
	}
	/**
	 * SQL操作-获取所有数据
	 * DAO中使用方法：$this->dao->db->get_all_sql($sql)
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function getAllSql($sql){
		return $this->init_db()->get_all_sql($sql);
	}
	/**
	 * SQL操作-获取单条信息-sql语句方式
	 * DAO中使用方法：$this->dao->db->get_one_sql($sql)
	 * @param  string $sql 数据库语句
	 * @return array
	 */
	public function getOneSql($sql) {
		return $this->init_db()->get_one_sql($sql);
	}
	/**
	 * SQL操作-删除数据
	 * DAO中使用方法：$this->dao->db->delete($ids, $table_name, $id_key = 'id')
	 * @param  int|array $ids 单个id或者多个id
	 * @param  string $table_name 表名
	 * @param  string $id_key 主键名
	 * @return bool
	 */
	public function delete($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_name,$id_key);
	}
	
	/**
	 * SQL操作-通过条件语句删除数据
	 * DAO中使用方法：$this->dao->db->delete_by_field($field, $table_name)
	 * @param  array  $field 条件数组
	 * @param  string $table_name 表名
	 * @return bool
	 */
	public function deleteByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->delete_by_field($whereArray,$this->table_name);
	}
}
