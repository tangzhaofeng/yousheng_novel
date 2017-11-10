<?php
class otherService extends Service{
	private $otherDao;
	public function __construct() {
		parent::__construct();
		$this->otherDao = InitPHP::getDao("other");
	}
	public function getOne($whereArray){
		return $this->otherDao->getOne($whereArray);
	}
	public function upOther($id,$data){
		return $this->otherDao->upOther($id,$data);
	}
	public function otherList(){
		return $this->otherDao->otherList();
	}
	public function insert($data) {
		return $this->otherDao->insert($data);
	}
	public function friendList(){
		return $this->otherDao->friendList();
	}
	public function delFriend($ids,$id_key = 'id') {
		return $this->otherDao->delFriend($ids,$id_key);
	}
}