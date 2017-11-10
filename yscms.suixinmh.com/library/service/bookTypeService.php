<?php
class bookTypeService extends Service{
	private $bookTypeDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->bookTypeDao = InitPHP::getDao("bookType");
		$this->logDo = $this->getUtil('log');
	}
	public function insert($data){
		return $this->bookTypeDao->insert($data);
	}
	public function upType($id,$data){
		return $this->bookTypeDao->upType($id,$data);
	}
	/**
	 * 根据ID获取一条信息
	 * @param int $id
	 */
	public function getType($id) {
		if ($id < 1) {
			return array();
		}
		return $this->bookTypeDao->getType($id);
	}
	public function getAll($where="1=1"){
		return $this->bookTypeDao->getAllSql($where);
	}
}