<?php 
class bookTypeDao extends Dao{
	public $table_name = 'book_type';
	private $fields = "parentId,typeName,ico,state";

	public function insert($data) {
		$data = $this->init_db()->build_key($data, $this->fields);
		return $this->init_db()->insert($data, $this->table_name);
	}
	public function upType($id,$data) {
		if($id<=0 || empty($data)){
			return false;
		}
		return $this->init_db()->update_by_field($data,array("id"=>$id),$this->table_name);
	}
	/**
	 * 通过ID获取一条数据
	 * @param unknown_type $id
	 */
	public function getType($id){
		if(empty($id)){
			return array();
		}
		$typeInfo=$this->init_db()->get_one($id, $this->table_name);
		return $typeInfo;
	}
	public function getAllSql($where="1=1"){
		$sql="select * from ".$this->table_name." where {$where} order by id";
		return $this->init_db()->get_all_sql($sql);
	}
}