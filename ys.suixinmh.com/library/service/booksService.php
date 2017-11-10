<?php
/**
 * @author gaoguofeng
 */
class booksService extends Service{
	/**
	 * 全局使用方法：InitPHP::getDao($daoname, $path = '')
	 * @param string $daoname 服务名称
	 * @param string $path 模块名称
	 * @return object
	 */
	private $booksDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->booksDao = InitPHP::getDao("books");
		$this->bookTypeDao = InitPHP::getDao("bookType");
		$this->logDo = $this->getUtil('log');
	}
	/**
	 * 根据ID获取一条信息
	 * @param int $id
	 */
	public function getBooks($id) {
		if ($id < 1) {
			return array();
		}
		$info=$this->booksDao->getBooks($id);
		$typeList=json_decode($info['type'],true);
		foreach ($typeList as $k=>$v){
			$typeInfo=$this->bookTypeDao->getType($v);
			$typeList[$k]=$typeInfo;
		}
		$info['type']=$typeList;
		return $info;
	}
	public function getUpBook($p=1,$pageNum=10){
		if ($p < 1 || $pageNum<1) {
			return array();
		}
		return $this->booksDao->getUpBook($p,$pageNum);
	}
	public function getNewBook($p=1,$pageNum=10){
		if ($p < 1 || $pageNum<1) {
			return array();
		}
		return $this->booksDao->getNewBook($p,$pageNum);
	}
	public function getBookList($p=1,$pageNum=20,$keyWord,$orderType='desc',$serial=0,$type=0,$time=0){
		if ($p < 1 || $pageNum<1) {
			return array();
		}
		$booksList=$this->booksDao->getBookList($p,$pageNum,$keyWord,$orderType,$serial,$type,$time);
		foreach($booksList['list'] as $k=>$v){
			$typeList=json_decode($v['type'],true);
			foreach ($typeList as $kt =>$vt){
				$typeInfo=$this->bookTypeDao->getType($vt);
				$typeList[$kt]=$typeInfo;
			}
			$booksList['list'][$k]['type']=$typeList;
		}
		return $booksList;
	}
	public function getTypeBookCount($type=0){
		if (empty($type)) {
			return 0;
		}
		return $this->booksDao->getTypeBookCount($type);
	}
	public function getChapter($bookId,$orderType='asc',$pageNum,$p=1){
		if ($bookId < 1 || $pageNum<1 || $p<1 || empty($orderType)) {
			return array();
		}
		return $this->booksDao->getChapter($bookId,$orderType,$pageNum,$p);
	}
	public function getChapterCount($bookId){
		if ($bookId < 1) {
			return array();
		}
		return $this->booksDao->getChapterCount($bookId);
	}
	public function getOneChapter($id){
		if ($id < 1) {
			return array();
		}
		return $this->booksDao->getOneChapter($id);
	}
	public function getPlayChapter($id,$book_id){
		if ($id < 1) {
			return array();
		}
		return $this->booksDao->getPlayChapter($id,$book_id);
	}
	public function getGiveCount($bookId){
		if ($bookId < 1) {
			return array();
		}
		return $this->booksDao->getGiveCount($bookId);
	}
	public function getGiveList($userId){
		if ($userId < 1) {
			return array();
		}
		return $this->booksDao->getGiveList($userId);
	}
	public function getCaseList($userId){
		if ($userId < 1) {
			return array();
		}
		return $this->booksDao->getCaseList($userId);
	}
	public function getCaseCount($bookId){
		if ($bookId < 1) {
			return array();
		}
		return $this->booksDao->getCaseCount($bookId);
	}
	public function insertGive($data){
		return $this->booksDao->insertGive($data);
	}
	public function insertCase($data){
		return $this->booksDao->insertCase($data);
	}
	public function insertBooksClick($data){
		return $this->booksDao->insertBooksClick($data);
	}
	public function insertHistory($data){
		return $this->booksDao->insertHistory($data);
	}
	public function getHistoryList($userId){
		return $this->booksDao->getHistoryList($userId);
	}
	public function getPushBook($pushType,$pageNum,$p=1){
		$list=$this->booksDao->getPushBook($pushType,$pageNum,$p);
		foreach($list as $k=>$v){
			$typeList=json_decode($v['type'],true);
			foreach ($typeList as $kt =>$vt){
				$typeInfo=$this->bookTypeDao->getType($vt);
				$typeList[$kt]=$typeInfo;
			}
			$list[$k]['type']=$typeList;
		}
		return $list;
	}

	public function T($time){
		//获取今天凌晨的时间戳
		$day = strtotime(date('Y-m-d',time()));
		//获取昨天凌晨的时间戳
		$pday = strtotime(date('Y-m-d',strtotime('-1 day')));
		//获取现在的时间戳
		$nowtime = time();
		$tc = $nowtime-$time;
		if($time<$pday){
			$str = date('Y-m-d',$time);
		}elseif($time<$day && $time>$pday){
			$str = "昨天 ".date('H:i',$time);
		}elseif($tc>60*60){
			$str = floor($tc/(60*60))."小时前";
		}elseif($tc>60){
			$str = floor($tc/60)."分钟前";
		}else{
			$str = "刚刚";
		}
		return $str;
	}
}
