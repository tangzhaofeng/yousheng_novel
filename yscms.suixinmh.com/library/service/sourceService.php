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
	public function getConcern($id){
	    if ($id < 1) {
	        return array();
	    }
	    return $this->sourceDao->getConcern($id);
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
	public function getConcernList(){
	    return $this->sourceDao->getConcernList();
	}
	public function setConcern($data){
	    return $this->sourceDao->setConcern($data);
	}
	public function upConcern($id,$data) {
	    return $this->sourceDao->upConcern($id,$data);
	}
	public function delConcern($id,$id_key = 'id'){
	    return $this->booksDao->delConcern($id,$id_key = 'id');
	}
}