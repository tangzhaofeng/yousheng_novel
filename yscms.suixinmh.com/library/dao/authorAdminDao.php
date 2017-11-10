<?php 
class authorAdminDao extends Dao{
	public $table_name = 'admin';
	private $fields = "userName,password,salt,userNumber,name,ip,loginTime,createTime";
	/**
	 * 新增动态和修改密码
	 * @param $user
	 */
	public function insert($data){
		$data['password']=md5($data['password'].$data['salt']);
		$data['loginTime']=time();
		$data['createTime']=$data['loginTime'];
		$sql="INSERT INTO `".$this->table_name."` (`userName`,`password`,`salt`,`userNumber`,`name`,`ip`,`loginTime`,`createTime`) VALUES ('{$data['userName']}',{$data['password']},{$data['salt']},{$data['userNumber']},{$data['name']},'{$data['ip']}',{$data['loginTime']},{$data['createTime']}) ON DUPLICATE KEY UPDATE `password` ={$data['password']},salt={$data['salt']}";
		return $this->init_db()->query($sql);
	}
	public function login($data){
		if(empty($data)){
			return false;
		}
		$info=$this->getOneByField(array("userName"=>$data['userName']));
		$data['password']=md5($data['password'].$info['salt']);
		$login['ip']=$data['ip'];
		unset($data['ip']);
		$loginInfo=$this->getOneByField($data);
		if(empty($loginInfo)){
			return false;
		}
		$login['loginTime']=time();
		$this->init_db()->update($loginInfo['id'],$login,$this->table_name);
		return $loginInfo;
	}
	public function getOneByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_name);
	}
	public function getAllSql($sql){
		return $this->init_db()->get_all_sql($sql);
	}
	public function getOneSql($sql) {
		return $this->init_db()->get_one_sql($sql);
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
