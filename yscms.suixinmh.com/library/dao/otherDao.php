<?php 
class otherDao extends Dao{
	public $table_name = 'us_info';
	private $fields = "lm,title,content";
	public $table_friend = 'friend';
	private $fields_friend = "web_name,url";

	public function getOne($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_name);
	}
	public function upOther($id,$data){
		if(empty($id) || empty($data)){
			return array();
		}
		return $this->init_db()->update($id,$data,$this->table_name);
	}
	public function otherList(){
		$sql='select * from '.$this->table_name.' ORDER BY id ASC';
		$list=$this->init_db()->get_all_sql($sql);
		return $list;
	}
	public function insert($data) {
		$data = $this->init_db()->build_key($data, $this->fields_friend);
		return $this->init_db()->insert($data, $this->table_friend);
	}
	public function friendList(){
		$sql='select * from '.$this->table_friend.' ORDER BY id ASC';
		$list=$this->init_db()->get_all_sql($sql);
		return $list;
	}
	public function delFriend($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_friend,$id_key);
	}
}
