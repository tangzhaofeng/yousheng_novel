<?php 
class userDao extends Dao{
	public $table_name = 'user';
	private $fields = "openid,unionid,phone,password,source,third_source,state,vip,create_time";
	public $table_info = 'user_info';
	private $fields_info = "user_id,name,sex,headPic,moneyCoin,up_time";
	public $table_out_login = 'user_out_login';
	private $out_login_info = "user_id,only_val,source,create_time";
	public $table_pay_record = 'pay_record';
	private $fields_pay_record = "source,out_trade_no,trade_no,money,moneyCoin,give,scale,user_id,create_time";
	public $table_book_order = 'book_order';
	private $fields_book_order = "book_id,chapter,user_id,price,create_time";
	public $table_book_history = 'book_history';
	private $fields_book_history = "book_id,chapter_id,user_id,create_time";
	public $table_third_source = 'third_source';
	private $fields_third_source = "name,sourceKey";
	public $table_user_look_pay_page = 'user_look_pay_page';
	private $fields_user_look_pay_page = "user_id,pageNum,year,month,day";

	public function userList($pageNum,$p=1,$field=array("1"=>1),$other=false){
		$offest=($p-1)*$pageNum;
		$usersinfo=$this->init_db()->get_all($this->table_name,$pageNum,$offest,$field,$other);
		foreach($usersinfo[0] as $k=>$v){
			$usersinfo[0][$k]['Info']=$this->getUserInfo(array("user_id"=>$v['id']));
		}
		return $usersinfo;
	}

	//$userWhere:用户表条件，$recordWhere充值表条件
	public function userPaySum($userWhere,$recordWhere){
		$sql='SELECT SUM(p.money) as moneySum,SUM(p.moneyCoin) as coin FROM pay_record AS p';
		$pulikWhere=' WHERE ';
		if(!empty($userWhere)){
			$userWhereSql=$pulikWhere.'exists(select * from `user` as u where u.id=p.user_id';
			$fuhaoWhere=')';
			if(!empty($userWhere['user_id'])){
				$userWhereSql.=' and u.id='.$userWhere['user_id'];
			}
			if(!empty($userWhere['third_source'])){
				$userWhereSql.=' and u.third_source="'.$userWhere["third_source"].'"';
			}
			if(!empty($userWhere['reg_type'])){
				$userWhereSql.=' and u.source="'.$userWhere["reg_type"].'"';
			}
			if(!empty($userWhere['start_time'])){
				$userWhereSql.=' and u.create_time>='.strtotime($userWhere['start_time']);
				if(!empty($userWhere['end_time'])){
					$userWhereSql.=' and u.create_time<='.strtotime($userWhere['end_time']);
				}else{
					$userWhereSql.=' and u.create_time<='.time();
				}
			}
			$sql.=$userWhereSql.$fuhaoWhere;
		}
		if(!empty($recordWhere)){
			if(empty($userWhere)){
				$recordWhereSql=$pulikWhere.'1=1';
			}else{
				$recordWhereSql='';
			}
			if(!empty($recordWhere['source'])){
				$recordWhereSql.=' and p.source='.$recordWhere['source'];
			}
			//}else{
				if(!empty($recordWhere['start_time'])){
					$recordWhereSql.=' and p.create_time>='.strtotime($recordWhere['start_time']);
					if(!empty($recordWhere['end_time'])){
						$recordWhereSql.=' and p.create_time<='.strtotime($recordWhere['end_time']);
					}else{
						$recordWhereSql.=' and p.create_time<='.time();
					}
				}
			//}
		}
		$sql.=$recordWhereSql;
		$SUM=$this->init_db()->get_one_sql($sql);
		$SUM['sql']=$sql;
		$SUM['moneySum']=empty($SUM['moneySum'])?0:$SUM['moneySum'];
		$SUM['coin']=empty($SUM['coin'])?0:$SUM['coin'];
		return $SUM;
	}
	public function userPayList($userWhere,$recordWhere,$pageNum=20,$p=1){
		$offest=($p-1)*$pageNum;
		$sql='SELECT * FROM pay_record AS p';
		$countSql='SELECT COUNT(*) AS NUM FROM pay_record AS p';
		$pulikWhere=' WHERE ';
		if(!empty($userWhere)){
			$userWhereSql=$pulikWhere.'exists(select * from `user` as u where u.id=p.user_id';
			$fuhaoWhere=')';
			if(!empty($userWhere['user_id'])){
				$userWhereSql.=' and u.id='.$userWhere['user_id'];
			}
			if(!empty($userWhere['third_source'])){
				$userWhereSql.=' and u.third_source="'.$userWhere["third_source"].'"';
			}
			$sql.=$userWhereSql.$fuhaoWhere;//用户表条件结束
			$countSql.=$userWhereSql.$fuhaoWhere;//用户表条件结束
			
			if(!empty($recordWhere['source'])){
				$recordWhereSql.=' and p.source="'.$recordWhere["source"].'"';
			}
			if(!empty($recordWhere['start_time'])){
				$recordWhereSql.=' and p.create_time>='.strtotime($recordWhere['start_time']);
				if(!empty($recordWhere['end_time'])){
					$recordWhereSql.=' and p.create_time<='.strtotime($recordWhere['end_time']);
				}else{
					$recordWhereSql.=' and p.create_time<='.time();
				}
			}
			$sql.=$recordWhereSql;
			$countSql.=$recordWhereSql;
		}else{
			$recordWhereSql=$pulikWhere.'1=1';
			if(!empty($recordWhere['source'])){
				$recordWhereSql.=' and p.source='.$recordWhere["source"];
			}
			//print_r($recordWhere);
			if(!empty($recordWhere['start_time'])){
				$recordWhereSql.=' and p.create_time>='.strtotime($recordWhere['start_time']);
				if(!empty($recordWhere['end_time'])){
					$recordWhereSql.=' and p.create_time<='.strtotime($recordWhere['end_time']);
				}else{
					$recordWhereSql.=' and p.create_time<='.time();
				}
			}
/*			$sql.=$recordWhereSql;
			$limit=' order by p.id desc limit '.$offest.','.$pageNum;
			$sql.=$limit;
			$countSql.=$recordWhereSql;
*/		}
		$sql.=$recordWhereSql;
		$limit=' order by p.id desc limit '.$offest.','.$pageNum;
		$sql.=$limit;
		$countSql.=$recordWhereSql;
		$list=$this->init_db()->get_all_sql($sql);
		$count=$this->init_db()->get_one_sql($countSql);
		$userPay['sql']=$sql;
		$userPay['list']=$list;
		$userPay['count']=$count['NUM'];
		return $userPay;
	}
	
	public function userPaySumLook($user_id,$start_time,$end_time){
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$where.="and create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$where.="and create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$where.="and create_time<={$end_time} ";
		}
		$sql="SELECT SUM(money) as moneySum,create_time FROM pay_record where user_id={$user_id} order by create_time desc";
		$SUM=$this->init_db()->get_one_sql($sql);
		$info['moneySum']=empty($SUM['moneySum'])?0:$SUM['moneySum'];
		$info['create_time']=$SUM['create_time'];
		return $info;
	}
	
	public function bookOrderSumList($data,$bookId=0){
		if(!empty($bookId)){
			$where.="book_id={$bookId} and";
		}
		if(empty($data['startTime']) && !empty($data['endTime'])){
			$endTime=strtotime($data['endTime'].' 23:59:59');
			$sql="SELECT sum(price) as priceSum,book_id from book_order where {$where} create_time<={$endTime} GROUP BY book_id HAVING priceSum>0 ORDER BY priceSum desc";
		}
		if(!empty($data['startTime']) && empty($data['endTime'])){
			$startTime=strtotime($data['startTime'].' 00:00:00');
			$endTime=time();
			$sql="SELECT sum(price) as priceSum,book_id from book_order where {$where} create_time>={$startTime} and create_time<={$endTime} GROUP BY book_id HAVING priceSum>0 ORDER BY priceSum desc";
		}
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$startTime=strtotime($data['startTime'].' 00:00:00');
			$endTime=strtotime($data['endTime'].' 23:59:59');
			$sql="SELECT sum(price) as priceSum,book_id from book_order where {$where} create_time>={$startTime} and create_time<={$endTime} GROUP BY book_id HAVING priceSum>0 ORDER BY priceSum desc";
		}
		if(empty($data['startTime']) && empty($data['endTime'])){
			$sql="SELECT sum(price) as priceSum,book_id from book_order where {$where} 1=1 GROUP BY book_id HAVING priceSum>0 ORDER BY priceSum desc";
		}
		return $this->init_db()->get_all_sql($sql);
	}
	
	public function bookOrderUserCount($bookId,$data){
		if(empty($data['startTime']) && !empty($data['endTime'])){
			$endTime=strtotime($data['endTime'].' 23:59:59');
			$sql="SELECT count(*) as num from book_order where book_id={$bookId} and create_time<={$endTime}";
		}
		if(!empty($data['startTime']) && empty($data['endTime'])){
			$startTime=strtotime($data['startTime'].' 00:00:00');
			$endTime=time();
			$sql="SELECT count(*) as num from book_order where book_id={$bookId} and create_time>={$startTime} and create_time<={$endTime} ";
		}
		if(!empty($data['startTime']) && !empty($data['endTime'])){
			$startTime=strtotime($data['startTime'].' 00:00:00');
			$endTime=strtotime($data['endTime'].' 23:59:59');
			$sql="SELECT count(*) as num from book_order where book_id={$bookId} and create_time>={$startTime} and create_time<={$endTime}";
		}
		if(empty($data['startTime']) && empty($data['endTime'])){
			$sql="SELECT count(*) as num from book_order where book_id={$bookId}";
		}
		$list=$this->init_db()->get_one_sql($sql);
		return $list['num'];
	}
	
	public function regSum($dataTime,$third_source){
		if(!empty($dataTime)){
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}else{
			$dataTime=date("Y-m-d",time());
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}
		$sql='select count(*) as regNum from '.$this->table_name.' where create_time>='.$start_time.' and create_time<='.$end_time;
		if(!empty($third_source)){
			$sql.=' and third_source="'.$third_source.'"';
		}
		$count=$this->init_db()->get_one_sql($sql);
		return $count['regNum'];
	}
	public function thirdSourceSum($dataTime,$third_source){
		if(!empty($dataTime)){
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}else{
			$dataTime=date("Y-m-d",time());
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}
		$sql='select count(*) as qudaoNum from third_source_statistics where create_time>='.$start_time.' and create_time<='.$end_time;
		if(!empty($third_source)){
			$sql.=' and source="'.$third_source.'"';
		}
		$count=$this->init_db()->get_one_sql($sql);
		return $count['qudaoNum'];
	}
	public function TJpaySum($dataTime,$third_source,$pay_source){
		if(!empty($dataTime)){
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}else{
			$dataTime=date("Y-m-d",time());
			$start_time=strtotime($dataTime." 00:00:00");
			$end_time=strtotime($dataTime." 23:59:59");
		}
		if(!empty($third_source)){
			$usql=' and u.third_source="'.$third_source.'"';
		}
		if(!empty($pay_source)){
			$psql=' and p.source='.$pay_source;
		}
		$sql='SELECT SUM(p.money) as moneySum FROM pay_record AS p WHERE exists(select id from `user` as u where u.id=p.user_id'.$usql.') AND p.create_time>='.$start_time.' and p.create_time<='.$end_time.$psql;
		$moneySum=$this->init_db()->get_one_sql($sql);
		return empty($moneySum['moneySum'])?0:$moneySum['moneySum'];
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
	
	public function lookOrderPay($user_id,$time){
		$sql="SELECT * FROM book_order WHERE user_id={$user_id} and create_time>={$time} order by create_time desc";
		$info=$this->init_db()->get_one_sql($sql);
		return $info;
	}
	public function historyBook($chapter_id,$third_source){
		if(!empty($third_source)){
			$userWhere="and third_source={$third_source}";
		}
		$sql="SELECT * FROM book_history AS p WHERE exists(select id from `user` as u where u.id=p.user_id {$userWhere}) AND p.chapter_id={$chapter_id} GROUP BY p.user_id;";
		$info=$this->init_db()->get_all_sql($sql);
		return count($info);
	}
	public function getsourceUserAll($third_source){
		if(!empty($third_source)){
			$userWhere="where third_source='".$third_source."'";
		}
		$sql="select count(*) as num from `user` {$userWhere}";
		$count=$this->init_db()->get_one_sql($sql);
		return $count['num'];
	}
	
	public function thirdSourceList(){
		$sql='select * from '.$this->table_third_source;
		$list=$this->init_db()->get_all_sql($sql);
		return $list;
	}
	
	
	
	
	
	
	//统计渠道注册用户数
	public function thirdSourceUser($third_source,$start_time,$end_time){
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$where.="and create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$where.="and create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$where.="and create_time<={$end_time} ";
		}
		$sql="SELECT count(*) as num from {$this->table_name} where third_source='".$third_source."' ".$where;
		$count=$this->init_db()->get_one_sql($sql);
		return $count['num'];
	}
	//统计渠道充值用户数
	public function thirdSourcePayUser($third_source,$start_time,$end_time){
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$where.="and p.create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$where.="and p.create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$where.="and p.create_time<={$end_time} ";
		}
		$sql="SELECT * FROM pay_record AS p WHERE exists(select id from `user` as u where u.id=p.user_id and third_source='".$third_source."') {$where} group by p.user_id desc";
		$info=$this->init_db()->get_all_sql($sql);
		return count($info);
	}
	//统计渠道充值金额
	public function thirdSourcePayRecord($third_source,$start_time,$end_time){
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$where.="and p.create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$where.="and p.create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$where.="and p.create_time<={$end_time} ";
		}
		$sql="SELECT SUM(p.money) as moneySum FROM pay_record AS p WHERE exists(select id from `user` as u where u.id=p.user_id and third_source='".$third_source."') {$where} ";
		$info=$this->init_db()->get_one_sql($sql);
		return $info['moneySum'];
	}
	//统计渠道访问数
	public function thirdSourceAllSum($third_source,$start_time,$end_time){
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$where.="and create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$where.="and create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$where.="and create_time<={$end_time} ";
		}
		$sql='select count(*) as qudaoNum from third_source_statistics where source="'.$third_source.'" '.$where;
		$count=$this->init_db()->get_one_sql($sql);
		return $count['qudaoNum'];
	}
	
	public function userLookPage($start_time,$end_time,$book_id,$p=1,$pageNum=25){
		$offest=($p-1)*$pageNum;
		$where="where 1=1 ";
		if(!empty($book_id)){
			$where.="and `book_id`={$book_id} ";
		}
		if(!empty($start_time) && empty($end_time)){
			$startData=explode('-',$start_time);
			$where.="and `year`>={$startData[0]} and `month`>={$startData[1]} and `day`>={$startData[2]}";
		}
		if(!empty($start_time) && !empty($end_time)){
			$startData=explode('-',$start_time);
			$endData=explode('-',$end_time);
			$where.="and `year`>={$startData[0]} and `year`<={$endData[0]} and `month`>={$startData[1]} and `month`<={$endData[1]} and `day`>={$startData[2]} and `day`<={$endData[2]}";
		}
		if(empty($start_time) && !empty($end_time)){
			$endData=explode('-',$end_time);
			$where.="and `year`<={$endData[0]} and `month`<={$endData[1]} and `day`<={$endData[2]}";
		}
		$sql="SELECT *,sum(pageNum) as lookNum FROM {$this->table_user_look_pay_page} {$where} group by user_id order by id desc limit {$offest},{$pageNum}";
		$list=$this->init_db()->get_all_sql($sql);
		
		if(!empty($start_time)){
			$start_time=strtotime($start_time." 00:00:00");
			$paywhere.="and create_time>={$start_time} ";
		}
		if(!empty($end_time)){
			$end_time=strtotime($end_time." 23:59:59");
			$paywhere.="and create_time<={$end_time} ";
		}
		if(empty($end_time)){
			$end_time=time();
			$paywhere.="and create_time<={$end_time} ";
		}
		foreach ($list as $k=>$v) {
			$sqls="SELECT book_id,cid FROM {$this->table_user_look_pay_page} {$where} and user_id={$v['user_id']} order by id desc";
			$list[$k]['bookList']=$this->init_db()->get_all_sql($sqls);
			$paysql="SELECT SUM(money) as moneySum FROM pay_record WHERE user_id={$v['user_id']} {$paywhere}";
			$moneySum=$this->init_db()->get_one_sql($paysql);
			$list[$k]['moneySum']=empty($moneySum['moneySum'])?0:$moneySum['moneySum'];
		}
		return $list;
	}	
	
	
	public function getOneByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_name);
	}
	public function getOnePayRecord($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_pay_record);
	}
	public function getUser($id){
		if(empty($id)){
			return array();
		}
		$sql="select openid,phone,state,third_source from ".$this->table_name." where id={$id}";
		return $this->init_db()->get_one_sql($sql);
	}
	public function getUserInfo($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_info);
	}
	public function delete($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_name,$id_key);
	}
	
	public function deleteByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->delete_by_field($whereArray,$this->table_name);
	}
}
