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
	private $userDao;
	private $logDo;
	public function __construct() {
		parent::__construct();
		$this->booksDao = InitPHP::getDao("books");
		$this->bookTypeDao = InitPHP::getDao("bookType");
		$this->userDao = InitPHP::getDao("user");
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
		return $this->booksDao->getBooks($id);
	}
	public function upBooksClick($data,$book_id){
		return $this->booksDao->upBooksClick($data,$book_id);
	}
	public function getBooksClick($whereArray){
		return $this->booksDao->getBooksClick($whereArray);
	}
	public function getLikeBooks($where){
		return $this->booksDao->getLikeBooks($where);
	}
	public function upChapter($where,$data){
		return $this->booksDao->upChapter($where,$data);
	}
	/**
	 * 创建一条信息
	 */
	public function insert($data){
		return $this->booksDao->insert($data);
	}
	public function insertclickbook($data){
		return $this->booksDao->insertclickbook($data);
	}
	public function insertPush($data){
		return $this->booksDao->insertPush($data);
	}
	public function getOneChapterField($whereArray) {
		return $this->booksDao->getOneChapterField($whereArray);
	}
	public function insertChapter($chapterData) {
		return $this->booksDao->insertChapter($chapterData);
	}
	public function upBooks($id,$data){
		return $this->booksDao->upBooks($id,$data);
	}
	/**
	 * SQL操作-通过条件语句获取一条信息
	 */
	public function getOneByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->booksDao->getOneByField($whereArray);
	}
	public function getOneChapter($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->booksDao->getOneChapter($whereArray);
	}

	public function getBooksList($pageNum,$where='',$p=1) {
		$booksList=$this->booksDao->getBooksList($pageNum,$where,$p);
		foreach($booksList as $k=>$v){
			$typeList=json_decode($v['type'],true);
			foreach ($typeList as $kt =>$vt){
				$typeInfo=$this->bookTypeDao->getType($vt);
				$typeList[$kt]=$typeInfo;
			}
			$booksList[$k]['type']=$typeList;
		}
		return $booksList;
	}
	public function getChapterList($bookId) {
		$ChapterList=$this->booksDao->getChapterList($bookId);
		return $ChapterList;
	}
	public function getChapter($id) {
		$info=$this->booksDao->getChapter($id);
		return $info;
	}
	//获取收入统计
	public function bookSummoney($data) {
		$bookSummoney=$this->userDao->orderPaySum($data);
		return $bookSummoney;
	}
	//计算章节价格和设置开始计费章节
	public function priceCompute($data,$type) {
		$priceCompute=$this->booksDao->priceCompute($data,$type);
		return $priceCompute;
	}
	public function getBooksCount($field=array()) {
		return $this->booksDao->getBooksCount($field);
	}
	public function getPushBook($pushType,$pageNum,$p=1){
		return $this->booksDao->getPushBook($pushType,$pageNum,$p);
	}
	public function deletepush($ids,$id_key = 'id') {
		return $this->booksDao->deletepush($ids,$id_key);
	}
	public function delChapter($id,$id_key = 'id'){
		return $this->booksDao->delChapter($id,$id_key);
	}
	public function getManOrWomenBooks($pageNum,$where='',$p=1,$pid){
		if(!empty($pid)){
			$where='where exists(select id from book_type as t where b.type=t.id and t.parentId='.$pid.' order by t.id asc) and '.$where;
		}else{
			$where='where exists(select id from book_type as t where b.type=t.id order by t.id asc) and '.$where;
		}
		$offest=($p-1)*$pageNum;
		$sql="select * from books as b {$where} order by b.sort asc,b.id desc limit {$offest},{$pageNum} ";
		return $this->booksDao->getAllSql($sql);
	}
	public function insertSlide($data){
		return $this->booksDao->insertSlide($data);
	}
	public function upSlide($id,$data){
		return $this->booksDao->upSlide($id,$data);
	}
	public function getOneSlide($id){
		return $this->booksDao->getOneSlide($id);
	}
	public function getSlide(){
		return $this->booksDao->getSlide($data);
	}
	public function delslide($ids) {
		return $this->booksDao->delslide($ids);
	}
	public function upRecommend($id,$data){
	    return $this->booksDao->upRecommend($id,$data);
	}
	public function upAuthorAudio($id,$data){
	    return $this->booksDao->upAuthorAudio($id,$data);
	}
}
