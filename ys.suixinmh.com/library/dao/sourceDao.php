<?php
class sourceDao extends Dao{
	public $table_name = 'third_source_statistics';
	private $fields = "only_key,source,user_terminal,pid,book_id,chapter_id,ip,count,week,day,month,year,create_time";
	public $table_source = 'third_source';
	private $fields_source = "name,sourceKey";
	public $table_third_source_url = 'third_source_url';
	private $fields_third_source_url = "sourceKey,book_id,chapter,start_time";
	public $table_concern = 'concern';
	private $fields_concern = 'book_id,chapter_id,audio_url,display,user_url,erweima_url';
	/**
	 * 新增动态
	 * @param $user
	 */
	public function insert($data){
		$data['create_time']=time();
		$sql="INSERT INTO `".$this->table_name."` (`only_key`,`source`,`user_terminal`,`pid`,`book_id`,`chapter_id`,`ip`,`count`,`week`,`day`,`month`,`year`,`create_time`) VALUES ('{$data['only_key']}',{$data['source']},{$data['user_terminal']},{$data['pid']},{$data['book_id']},{$data['chapter_id']},'{$data['ip']}',{$data['count']},{$data['week']},{$data['day']},{$data['month']},{$data['year']},{$data['create_time']}) ON DUPLICATE KEY UPDATE `count` =count+1";
		return $this->init_db()->query($sql);
	}
	public function getOneSource($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_source);
	}
	public function getSourceUrl($id){
		if(empty($id)){
			return array();
		}
		$Info=$this->init_db()->get_one($id, $this->table_third_source_url);
		return $Info;
	}
	/**
	 * SQL操作-通过条件语句获取一条信息
	 */
	public function getOneByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_name);
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
	public function getConcern($whereArray){
	    if(empty($whereArray)){
	        return array();
	    }
	    return $this->init_db()->get_one_by_field($whereArray,$this->table_concern);
	}
}