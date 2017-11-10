<?php
/**
 * @author gaoguofeng
 */
class authorAdminService extends Service{
	private $authorAdminDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->authorAdminDao = InitPHP::getDao("authorAdmin");//实例并且单例模式获取Dao
		$this->logDo = $this->getUtil('log');
	}
	public function insert($data) {
		return $this->authorAdminDao->insertUser($data);
	}
	public function login($data){
		if(empty($data)){
			return false;
		}
		return $this->authorAdminDao->login($data);
	}
}
