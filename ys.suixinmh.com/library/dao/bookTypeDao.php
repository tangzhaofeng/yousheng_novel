<?php 
class bookTypeDao extends Dao{
	public $table_name = 'book_type';
	private $fields = "parentId,typeName,ico,state";

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
	public function getAllSql($pid=false){
		if(empty($pid)){
			$sql="select * from ".$this->table_name." where parentId=0 and state=1 order by id";
		}else{
			$sql="select * from ".$this->table_name." where parentId='".$pid."' and state=1 order by id";
		}
		return $this->init_db()->get_all_sql($sql);
	}
}