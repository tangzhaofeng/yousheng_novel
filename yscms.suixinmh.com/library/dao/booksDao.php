<?php
class booksDao extends Dao{
	public $table_name = 'books';
	private $fields = "book_name,book_title,book_pic,type,level,isfirst,notice,descriptions,is_serial,is_publish,author_id,author_name,author_tape,give,book_case,pay_way,edit_time,create_time,erweima_url";
	public $table_chapter = 'books_chapter';
	private $fields_chapter = "book_id,chapter,title,sort,audio_url,is_vip,price,create_time";
	public $table_push = 'book_push';
	private $fields_push = "book_id,push_type,sort,create_time";
	public $table_click_count = 'books_click_count';
	private $fields_click_count = "book_id,click_count";
	public $table_give = 'book_give';
	private $fields_give = "book_id,user_id,create_time";
	public $table_case = 'book_case';
	private $fields_case = "book_id,user_id,create_time";
	public $table_slide = 'slide_img';
	private $fields_slide = "title,url,image,sort";
	public $table_spare = 'user_spare';
	private $fields_spare = 'book_id,user_id,chapter_id,create_time,type';

	public function insert($data) {
		$time=time();
		$data['edit_time']=$time;
		$data['create_time']=$time;
		$data = $this->init_db()->build_key($data, $this->fields);
		return $this->init_db()->insert($data, $this->table_name);
	}
	public function insertclickbook($data) {
		$data = $this->init_db()->build_key($data, $this->fields_click_count);
		return $this->init_db()->insert($data, $this->table_click_count);
	}
	public function getOneChapterField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_chapter);
	}
	public function insertChapter($chapterData) {
		$chapterData['create_time']=time();
		$chapterDatas = $this->init_db()->build_key($chapterData, $this->fields_chapter);
		$chapter_id=$this->init_db()->insert($chapterDatas, $this->table_chapter);
		if(!empty($chapterDatas['book_id'])){
			$data['edit_time']=time();
			$this->init_db()->update($chapterDatas['book_id'],$data,$this->table_name);
		}
		if($chapter_id){
			return $chapter_id;
		}else{
			return false;
		}
	}
	public function insertPush($data) {
		$data = $this->init_db()->build_key($data, $this->fields_push);
		return $this->init_db()->insert($data, $this->table_push);
	}

	public function upBooks($id,$data){
		if(empty($id) || empty($data)){
			return array();
			return $this->init_db()->update($id,$data,$this->table_name);
		}
		$data['edit_time']=time();
		return $this->init_db()->update($id,$data,$this->table_name);
	}
	public function upBooksClick($data,$book_id) {
		if($data['click_count']<=0 || empty($data['click_count'])){
			return false;
		}
		return $this->init_db()->update_by_field($data,array("book_id"=>$book_id),$this->table_click_count);
	}
	public function getPushBook($pushType,$pageNum,$p=1){
		$offest=($p-1)*$pageNum;
		$sql='select id,book_id,push_type,sort from '.$this->table_push.' where push_type="'.$pushType.'" order by sort asc limit '.$offest.','.$pageNum;
		$countSql='select count(*) as num from '.$this->table_push.' where push_type="'.$pushType.'" order by sort asc';
		$list=$this->init_db()->get_all_sql($sql);
		$count=$this->init_db()->get_one_sql($countSql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$bookInfo['pushsort']=$v['sort'];
			$bookInfo['push_type']=$v['push_type'];
			$bookInfo['book_id']=$v['book_id'];
			$bookInfo['pushid']=$v['id'];
			$list[$k]=$bookInfo;
		}
		$dataPush['list']=$list;
		$dataPush['count']=$count['num'];
		return $dataPush;
	}

	/**
	 * 通过ID获取一条数据
	 * @param unknown_type $id
	 */
	public function getBooks($id){
		if(empty($id)){
			return array();
		}
		$info=$this->init_db()->get_one($id, $this->table_name);
		$info['give']=$this->getGiveCount($info['id']);
		$info['book_case']=$this->getCaseCount($info['id']);
		$clickCount=$this->getBooksClick(array("book_id"=>$info['id']));
		$info['clickCount']=$clickCount['click_count'];
		return $info;
	}
	//统计点赞
	public function getGiveCount($bookId){
		if(empty($bookId)){
			return 0;
		}
		$sql='select count(*) as num from '.$this->table_give.' where book_id='.$bookId.' order by id desc';
		$COUNT=$this->init_db()->get_one_sql($sql);
		return $COUNT['num'];
	}
	//统计收藏
	public function getCaseCount($bookId){
		if(empty($bookId)){
			return 0;
		}
		$sql='select count(*) as num from '.$this->table_case.' where book_id='.$bookId.' order by id desc';
		$COUNT=$this->init_db()->get_one_sql($sql);
		return $COUNT['num'];
	}
	//获取书访问量
	public function getBooksClick($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_click_count);
	}
	public function getLikeBooks($where){
		if(!empty($where)){
			$where="where {$where}";
		}else{
			$where="where 1=1";
		}
		$sql="select * from ".$this->table_name." {$where} order by id desc";
		return $this->init_db()->get_one_sql($sql);
	}

	public function getChapter($id){
		if(empty($id)){
			return array();
		}
		$info =$this->init_db()->get_one($id, $this->table_chapter);
		return $info;
	}
	public function upChapter($where,$data){
		if(empty($where) || empty($data)){
			return array();
		}
		return $this->init_db()->update_by_field($data,$where,$this->table_chapter);
	}
	public function upSpare($where,$data){
	    if(empty($where) || empty($data)){
	        return array();
	    }
	    return $this->init_db()->update_by_field($data,$where,$this->table_spare);
	}
	public function getBooksList($pageNum,$where,$p=1) {
		if(!empty($where)){
			$where="where {$where}";
		}else{
			$where="where 1=1";
		}
		$offest=($p-1)*$pageNum;
		$sql="select * from ".$this->table_name." {$where} order by id desc limit {$offest},{$pageNum}";
		$booksList=$this->init_db()->get_all_sql($sql);
		foreach($booksList as $k=>$v){
			$typeStr=str_replace("type","",$v['type']);
			$typeStr=str_replace("T_","",$typeStr);
			$typeArray=explode(',',$typeStr);
			$typeArray=array_filter($typeArray);
			$booksList[$k]['type']=json_encode($typeArray);
			$booksList[$k]['countAll']=$this->getChapterCount("book_id={$v['id']}");
			$booksList[$k]['countVip']=$this->getChapterCount("book_id={$v['id']} and is_vip=1");
		}
		return $booksList;
	}
	public function getBooksCount($field=array()) {
		return $this->init_db()->get_count($this->table_name,$field);
	}
	public function getSpareCount($field=array()) {
	    return $this->init_db()->get_count($this->table_spare,$field);
	}
	public function getChapterList($bookId) {
		if(empty($bookId)){
			return array();
		}
		$sql="select * from ".$this->table_chapter." WHERE book_id={$bookId} order by SORT ASC";
		$chapterList=$this->init_db()->get_all_sql($sql);
		return $chapterList;
	}
	public function getSpareList($pageNum,$where,$p=1) {
	    if(!empty($where)){
	        $where="where {$where}";
	    }else{
	        $where="where 1=1";
	    }
	    $offest=($p-1)*$pageNum;
	    $sql="select * from ".$this->table_spare." {$where} order by id desc limit {$offest},{$pageNum}";
	    $spareList=$this->init_db()->get_all_sql($sql);
	    foreach($spareList as $k=>$v){
	        $books=$this->getbooks($v['book_id']);
	        $spareList[$k]['book_name']=$books['book_name'];
	    }
	    return $spareList;
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
	public function getOneChapter($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_chapter);
	}
	public function getChapterCount($where){
		if(!empty($where)){
			$where="where {$where}";
		}else{
			$where="where 1=1";
		}
		$sql="select count(*) as Cnum from ".$this->table_chapter." {$where} order by id desc";
		$countInfo=$this->init_db()->get_one_sql($sql);
		return $countInfo['Cnum'];
	}
	public function priceCompute($data,$type){
		$data=array_filter($data);
		if(empty($data['book_id']) || count($data)!=4){
			return false;
		}
		//print_r($data);die;
		$sqlA="UPDATE ".$this->table_chapter." set is_vip=0 where book_id={$data['book_id']} and sort<{$data['vipStart']}";
		$sqlB="UPDATE ".$this->table_chapter." set is_vip=1 where book_id={$data['book_id']} and sort>={$data['vipStart']}";
		if(empty($type) || $type=='size'){
			$sqlC="UPDATE ".$this->table_chapter." set price={$data['unitPrice']} where book_id={$data['book_id']} and sort>={$data['priceStart']}";
		}
		$sqlD="UPDATE ".$this->table_chapter." set price=0 where book_id={$data['book_id']} and sort<{$data['priceStart']}";
		$this->init_db()->transaction_start();
		$A=$this->init_db()->query($sqlA);
		$B=$this->init_db()->query($sqlB);
		$C=$this->init_db()->query($sqlC);
		$D=$this->init_db()->query($sqlD);
		if(!$A || !$B || !$C || !$D){
			$this->init_db()->transaction_rollback();
			return false;
		}else{
			$this->init_db()->transaction_commit();
			return true;
		}
	}
	public function insertSlide($data) {
		$data = $this->init_db()->build_key($data, $this->fields_slide);
		return $this->init_db()->insert($data, $this->table_slide);
	}
	public function upSlide($id,$data){
		if(empty($id) || empty($data)){
			return array();
		}
		return $this->init_db()->update($id,$data,$this->table_slide);
	}
	public function upRecommend($id,$data){
	    if(empty($id) || empty($data)){
	        return array();
	    }
	    return $this->init_db()->update($id, $data, $this->table_name);
	}
	public function upAuthorAudio($id,$data){
	    if(empty($id) || empty($data)){
	        return array();
	    }
	    return $this->init_db()->update($id, $data, $this->table_name);
	}
	public function getSlide(){
		$sql='select * from '.$this->table_slide.' order by sort asc';
		$list=$this->init_db()->get_all_sql($sql);
		return $list;
	}
	public function getOneSlide($id){
		if(empty($id)){
			return array();
		}
		return $this->init_db()->get_one($id, $this->table_slide);
	}
	public function delslide($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_slide,$id_key);
	}
	public function getAllSql($sql){
		return $this->init_db()->get_all_sql($sql);
	}
	public function getOneSql($sql) {
		return $this->init_db()->get_one_sql($sql);
	}
	public function delete($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_name,$id_key);
	}
	public function delChapter($id,$id_key = 'id') {
		if(empty($id)){
			return array();
		}
		return $this->init_db()->delete($id,$this->table_chapter,$id_key);
	}
	public function delSpare($id,$id_key = 'id') {
	    if(empty($id)){
	        return array();
	    }
	    return $this->init_db()->delete($id,$this->table_spare,$id_key);
	}
	public function deletepush($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_push,$id_key);
	}

	public function deleteByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->delete_by_field($whereArray,$this->table_name);
	}
}