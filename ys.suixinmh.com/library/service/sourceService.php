<?php
/**
 * @author gaoguofeng
 */
class sourceService extends Service{
	private $sourceDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->sourceDao = InitPHP::getDao("source");
		$this->logDo = $this->getUtil('log');
	}
	/**
	 * 创建一条信息
	 */
	public function insert($data){
		return $this->sourceDao->insert($data);
	}
	public function getOneSource($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->sourceDao->getOneSource($whereArray);
	}
	public function getSourceUrl($id){
		if(empty($id)){
			return array();
		}
		return $this->sourceDao->getSourceUrl($id);
	}
	/**
	 * SQL操作-通过条件语句获取一条信息
	 */
	public function getOneByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->sourceDao->getOneByField($whereArray);
	}
	public function getSourceList($pageNum,$where='',$p=1) {
		if(!empty($where)){
			$where="where {$where}";
		}
		$offest=($p-1)*$pageNum;
		$sql="select * from third_source_statistics {$where} order by id desc limit {$offest},{$pageNum} ";
		return $this->sourceDao->getAllSql($sql);
	}
}