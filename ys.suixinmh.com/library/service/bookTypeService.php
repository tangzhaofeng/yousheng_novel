<?php
class bookTypeService extends Service{
	private $bookTypeDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->bookTypeDao = InitPHP::getDao("bookType");
		$this->logDo = $this->getUtil('log');
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
	public function getAll() {
		$list=$this->bookTypeDao->getAllSql();
		foreach($list as $k=>$v){
			$minType=$this->bookTypeDao->getAllSql($v['id']);
			$list[$k]['minType']=$minType;
		}
		return $list;
	}
}