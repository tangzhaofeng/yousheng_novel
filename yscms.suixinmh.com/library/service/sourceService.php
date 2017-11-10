<?php
class sourceService extends Service{
	private $sourceDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->sourceDao = InitPHP::getDao("source");
		$this->logDo = $this->getUtil('log');
	}
	public function insert($data){
		return $this->sourceDao->insert($data);
	}
	public function insertUrl($data){
		return $this->sourceDao->insertUrl($data);
	}
	public function upSource($id,$data){
		return $this->sourceDao->upSource($id,$data);
	}
	public function upSourceUrl($id,$data){
		return $this->sourceDao->upSourceUrl($id,$data);
	}
	/**
	 * 根据ID获取一条信息
	 * @param int $id
	 */
	public function getSource($id){
		if ($id < 1) {
			return array();
		}
		return $this->sourceDao->getSource($id);
	}
	public function getSourceUrl($id){
		if ($id < 1) {
			return array();
		}
		return $this->sourceDao->getSourceUrl($id);
	}
	public function getAllSource(){
		return $this->sourceDao->getAllSource();
	}
	public function getAllSourceUrl($sourceKey){
		return $this->sourceDao->getAllSourceUrl($sourceKey);
	}
}