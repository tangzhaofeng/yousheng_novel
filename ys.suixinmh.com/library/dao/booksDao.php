<?php
class booksDao extends Dao{
	public $table_name = 'books';
	private $fields = "book_name,book_title,book_pic,type,level,isfirst,notice,descriptions,is_serial,is_publish,author_id,author_name,author_tape,give,book_case,pay_way,edit_time,create_time,erweima_url";
	public $table_chapter = 'books_chapter';
	private $fields_chapter = "book_id,chapter,title,sort,audio_url,is_vip,price,create_time";
	public $table_push = 'book_push';
	private $fields_push = "book_id,push_type,sort,create_time";
	public $table_give = 'book_give';
	private $fields_give = "book_id,user_id,create_time";
	public $table_case = 'book_case';
	private $fields_case = "book_id,user_id,create_time";
	public $table_click_count = 'books_click_count';
	private $fields_click_count = "book_id,click_count";
	public $table_history = 'book_history';
	private $fields_history = "book_id,chapter_id,user_id,create_time";
	public $table_slide = 'slide_img';
	private $fields_slide = "title,url,image,sort";
	public $table_spare = 'user_spare';
	private $fields_spare = 'book_id,chapter_id,spare,create_time,playtime,user_id,disable,user_name';
    public $table_tip = 'user_tip';
    private $fields_tip = 'book_id,chapter_id,spare,create_time,playtime,user_id,disable,user_name,price';
	public function getBookType($type){
		$typeStr=str_replace("type","",$type);
		$typeStr=str_replace("T_","",$typeStr);
		$typeArray=explode(',',$typeStr);
		$typeArray=array_filter($typeArray);
		return json_encode($typeArray);
	}
	//获取单本书
	public function getBooks($id){
		if(empty($id)){
			return array();
		}
		$info=$this->init_db()->get_one($id, $this->table_name);
		$give=$this->getGiveCount($info['id']);

		$info['give']=$give<$info['give']?$info['give']:$give;

		$case=$this->getCaseCount($info['id']);
		$info['book_case']=$case<$info['book_case']?$info['book_case']:$case;

		$clickCount=$this->getBooksClick(array("book_id"=>$info['id']));
		$info['clickCount']=$clickCount['click_count']<$info['click_count']?$info['click_count']:$clickCount['click_count'];
		$rad=substr($info['create_time'],-3)+1000;
		$info['give']=$info['give']+$rad+235;
		$info['book_case']=$info['book_case']+$rad+32;
		$info['clickCount']=$info['clickCount']+$rad+12;
		$info['type']=$this->getBookType($info['type']);
		return $info;
	}
	//更新图书
	public function getUpBook($p,$pageNum){
		$offest=($p-1)*$pageNum;
		$sql='select * from '.$this->table_name.' where state=1 order by edit_time desc limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){

			$give=$this->getGiveCount($v['id']);
			$list[$k]['give']=$give<$v['give']?$v['give']:$give;
			$case=$this->getCaseCount($v['id']);
			$list[$k]['book_case']=$case<$list[$k]['book_case']?$list[$k]['book_case']:$case;
			//$clickCount=$this->getBooksClick(array("book_id"=>$v['id']));
			//$list[$k]['clickCount']=$clickCount['click_count']<$v['click_count']?$v['click_count']:$clickCount['click_count'];
			$rad=substr($list[$k]['create_time'],-3)+1000;
			$list[$k]['give']=$list[$k]['give']+$rad+235;
			$list[$k]['book_case']=$list[$k]['book_case']+$rad+32;
			//$list[$k]['clickCount']=$list[$k]['clickCount']+$rad+12;
		}
		return $list;
	}
	//更新图书
	public function getNewBook($p,$pageNum){
		$offest=($p-1)*$pageNum;
		$sql='select * from '.$this->table_name.' where state=1 order by create_time desc limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$give=$this->getGiveCount($v['id']);
			$list[$k]['give']=$give<$v['give']?$v['give']:$give;
			$clickCount=$this->getBooksClick(array("book_id"=>$v['id']));
			$list[$k]['clickCount']=$clickCount['click_count']<$v['click_count']?$v['click_count']:$clickCount['click_count'];
		}
		return $list;
	}
	//图书列表
	public function getBookList($p=1,$pageNum=20,$keyWord,$orderType='desc',$serial=0,$type=0,$time=0){
		$where='state=1';
		if(!empty($type)){
			$where.=' and type like "T_%type'.$type.',%"';
		}
		if(!empty($keyWord)){
			$where.=' and book_name like "%'.$keyWord.'%"';
		}
		if(empty($orderType)){
			$orderType='desc';
		}
		if($serial!=''){
			$where.=' and is_serial='.$serial;
		}
		if(!empty($time)){
			$time=date("Y-m-d",strtotime("-1 day"));
			$edit_time=strtotime($time);
			$where.=' and edit_time>'.$edit_time;
		}
		$offest=($p-1)*$pageNum;
		$sql='select * from '.$this->table_name.' where '.$where.' order by id '.$orderType.' limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		$Csql='select count(*) as num from '.$this->table_name.' where '.$where.' order by id '.$orderType;
		$COUNT=$this->init_db()->get_one_sql($Csql);
		foreach($list as $k=>$v){
			$give=$this->getGiveCount($v['id']);
			$list[$k]['give']=$give<$v['give']?$v['give']:$give;
			$clickCount=$this->getBooksClick(array("book_id"=>$v['id']));
			$list[$k]['clickCount']=$clickCount['click_count']<$v['click_count']?$v['click_count']:$clickCount['click_count'];
			$list[$k]['type']=$this->getBookType($v['type']);
		}
		$bookList['list']=$list;
		$bookList['count']=$COUNT['num'];
		return $bookList;
	}
	//获取类型书的个数
	public function getTypeBookCount($type=0){
		$where='state=1';
		if(!empty($type)){
			$where.=' and type like "T_%type'.$type.',%"';
		}
		$Csql='select count(*) as num from '.$this->table_name.' where '.$where.' order by id';
		$COUNT=$this->init_db()->get_one_sql($Csql);
		return empty($COUNT['num'])?0:$COUNT['num'];
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
	//添加赞
	public function insertGive($data){
		if(empty($data)){
			return false;
		}
		$info=$this->init_db()->get_one_by_field($data,$this->table_give);
		if(empty($info)){
			$data['create_time']=time();
			$givedata = $this->init_db()->build_key($data, $this->fields_give);
			return $this->init_db()->insert($givedata, $this->table_give);
		}else{
			return true;
		}
	}
	public function insetSapre($data){
	    if(empty($data)){
	        return  false;
	    }
	    $info=$this->init_db()->get_one_by_field($data,$this->table_spare);
	    if(empty($info)){
	        $sparedata = $this->init_db()->build_key($data, $this->fields_spare);
	        return $this->init_db()->insert($sparedata, $this->table_spare);
	    }else{
	        return true;
	    }
	}
	public function insetTip($data){
	    if(empty($data)){
	        return  false;
	    }
	    $info=$this->init_db()->get_one_by_field($data,$this->table_tip);
	    if(empty($info)){
	        $sparedata = $this->init_db()->build_key($data, $this->fields_tip);
	        return $this->init_db()->insert($sparedata, $this->table_tip);
	    }else{
	        return true;
	    }
	}
	//点赞图书
	public function getGiveList($userId){
		if(empty($userId)){
			return array();
		}
		$sql='select * from '.$this->table_give.' where user_id='.$userId.' order by id desc';
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$list[$k]=$bookInfo;
		}
		return $list;
	}
	//添加收藏
	public function insertCase($data){
		if(empty($data)){
			return false;
		}
		$info=$this->init_db()->get_one_by_field($data,$this->table_case);
		if(empty($info)){
			$data['create_time']=time();
			$givedata = $this->init_db()->build_key($data, $this->fields_case);
			return $this->init_db()->insert($givedata, $this->table_case);
		}else{
			return true;
		}
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
	//点赞图书
	public function getCaseList($userId){
		if(empty($userId)){
			return array();
		}
		$sql='select * from '.$this->table_case.' where user_id='.$userId.' order by id desc';
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$list[$k]=$bookInfo;
		}
		return $list;
	}
	//获取书访问量
	public function getBooksClick($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->get_one_by_field($whereArray,$this->table_click_count);
	}
	//更新书访问量
	public function insertBooksClick($data) {
		if($data['click_count']<=0 || empty($data['click_count']) || empty($data['book_id'])){
			return false;
		}
		$sql="INSERT INTO {$this->table_click_count} ({$this->fields_click_count}) VALUES ({$data['book_id']},{$data['click_count']}) ON DUPLICATE KEY UPDATE click_count=click_count+{$data['click_count']}";
		return $this->init_db()->query($sql,false);
	}
	//记录听书记录
	public function insertHistory($data){
		if(empty($data)){
			return false;
		}
		$info=$this->init_db()->get_one_by_field(array("book_id"=>$data['book_id'],"user_id"=>$data['user_id']),$this->table_history);
		if(empty($info)){
			$data['create_time']=time();
			$Historydata = $this->init_db()->build_key($data, $this->fields_history);
			return $this->init_db()->insert($Historydata, $this->table_history);
		}else{
			return $this->init_db()->update_by_field(array("chapter_id"=>$data['chapter_id'],"create_time"=>time()),array("book_id"=>$data['book_id'],"user_id"=>$data['user_id']),$this->table_history);
		}
	}
	//获取听书记录
	public function getHistoryList($userId){
		if(empty($userId)){
			return array();
		}
		$sql='select * from '.$this->table_history.' where user_id='.$userId.' order by create_time desc';
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$bookInfo['chapter_id']=$v['chapter_id'];
			$list[$k]=$bookInfo;
		}
		return $list;
	}
	public function getChapter($bookId,$orderType='asc',$pageNum,$p=1){
		$offest=($p-1)*$pageNum;
		$sql='select * from '.$this->table_chapter.' where book_id='.$bookId.' order by sort '.$orderType.' limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		return $list;
	}
	public function getChapterCount($bookId){
		$sql='select count(*) as num from '.$this->table_chapter.' where book_id='.$bookId.' order by sort asc';
		$list=$this->init_db()->get_one_sql($sql);
		return empty($list['num'])?0:$list['num'];
	}
	public function getOneChapter($id){
		$sql='select * from '.$this->table_chapter.' where id='.$id;
		return $this->init_db()->get_one_sql($sql);
	}
	public function getPlayChapter($id,$book_id){
		$Chapter=$this->getOneChapter($id);
		$sql='select count(*) as num from '.$this->table_chapter.' where book_id='.$book_id.' and sort<='.$Chapter['sort'];
		$count=$this->init_db()->get_one_sql($sql);
		return empty($count['num'])?0:$count['num'];
	}
	public function getPushBook($pushType,$pageNum,$p=1){
		$offest=($p-1)*$pageNum;
		$sql='select book_id from '.$this->table_push.' where push_type="'.$pushType.'" order by sort asc limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$list[$k]=$bookInfo;
		}
		return $list;
	}
	public function getPushBooks($pushTypeArray,$pageNum,$p=1){
		if (!is_array($pushTypeArray) || empty($pushTypeArray)) return array();
		$offest=($p-1)*$pageNum;
		$num=count($pushTypeArray);
		$sql='SELECT * from (';
		$Csql='SELECT COUNT(*) AS num from (';
		for($i=0;$i<$num;$i++){
			if($num-1==$i){
				$sql.='select book_id,sort from '.$this->table_push.' where push_type="'.$pushTypeArray[$i].'"';
				$Csql.='select book_id,sort from '.$this->table_push.' where push_type="'.$pushTypeArray[$i].'"';
			}else{
				$sql.='select book_id,sort from '.$this->table_push.' where push_type="'.$pushTypeArray[$i].'" union ';
				$Csql.='select book_id,sort from '.$this->table_push.' where push_type="'.$pushTypeArray[$i].'" union ';
			}
		}
		$sql.=') AS p ORDER BY p.sort ASC limit '.$offest.','.$pageNum;
		$Csql.=') AS p ORDER BY p.sort ASC';
		$list=$this->init_db()->get_all_sql($sql);
		$COUNT=$this->init_db()->get_one_sql($Csql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBooks($v['book_id']);
			$list[$k]=$bookInfo;
		}
		$data['list']=$list;
		$data['count']=$COUNT['num'];
		return $data;
	}
	public function getBookTimeFree($pageNum=20,$p=1){
		$offest=($p-1)*$pageNum;
		$thisTime=time();
		$sql='select book_id,sort from '.$this->table_book_timefree.' where start_time<='.$thisTime.' and end_time>='.$thisTime.' ORDER BY sort ASC limit '.$offest.','.$pageNum;
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$bookInfo=$this->getBookAndAuthor($v['book_id']);
			$list[$k]['type']=$bookInfo['type'];
			$list[$k]['book_name']=$bookInfo['book_name'];
			$list[$k]['nickName']=$bookInfo['nickName'];
			$list[$k]['book_pic']=$bookInfo['book_pic'];
			$list[$k]['desc']=$bookInfo['descriptions'];
		}
		return $list;
	}
	public function getSpareList($id,$chapter,$userid){
	    if (empty($id) or empty($chapter)) {
	        return array();
	    }
	    if ($userid != 0){
	        $sql='select spare,playtime,user_id from '.$this->table_spare.' where (book_id='.$id.' and chapter_id='.$chapter.' and user_id='.$userid.') or (book_id='.$id.' and chapter_id='.$chapter.' and disable=1) order by playtime';

	    }else{
	        $sql='select spare,playtime,user_id from '.$this->table_spare.' where book_id='.$id.' and chapter_id='.$chapter.' and disable=1 order by playtime';

	    }
	    $list=$this->init_db()->get_all_sql($sql);

	    return $list;
	}
	public function getTipList($id,$chapter,$userid){
	    if (empty($id) or empty($chapter)) {
	        return array();
	    }
	    if ($userid != 0){
	        $sql='select spare,playtime,user_id,price from '.$this->table_tip.' where (book_id='.$id.' and chapter_id='.$chapter.' and user_id='.$userid.') or (book_id='.$id.' and chapter_id='.$chapter.' and disable=1) order by playtime';

	    }else{
	        $sql='select spare,playtime,user_id,price from '.$this->table_tip.' where book_id='.$id.' and chapter_id='.$chapter.' and disable=1 order by playtime';

	    }
	    $list=$this->init_db()->get_all_sql($sql);

	    return $list;
	}

	public function isTimeFreeBooks($bookID){
		$thisTime=time();
		$sql='select book_id from '.$this->table_book_timefree.' where book_id='.$bookID.' and start_time<='.$thisTime.' and end_time>='.$thisTime.' ORDER BY sort ASC';
		$TimeFreeInfo=$this->init_db()->get_one_sql($sql);
		return $TimeFreeInfo;
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
	public function getSlide($path){
		$sql='select * from '.$this->table_slide.' order by sort asc';
		$list=$this->init_db()->get_all_sql($sql);
		foreach($list as $k=>$v){
			$list[$k]['image']=$path.$v['image'];
			$list[$k]['thumb']=$path.$v['image'];
			$list[$k]['mark']=0;
		}
		return $list;
	}
	/**
	 * SQL操作-获取所有数据
	 * DAO中使用方法：$this->dao->db->get_all_sql($sql)
	 * @param string $sql SQL语句
	 * @return array
	 */
	public function getAllSql($sql){
		return $this->init_db()->get_all_sql($sql);
	}
	/**
	 * SQL操作-获取单条信息-sql语句方式
	 * DAO中使用方法：$this->dao->db->get_one_sql($sql)
	 * @param  string $sql 数据库语句
	 * @return array
	 */
	public function getOneSql($sql) {
		return $this->init_db()->get_one_sql($sql);
	}
	/**
	 * SQL操作-删除数据
	 * DAO中使用方法：$this->dao->db->delete($ids, $table_name, $id_key = 'id')
	 * @param  int|array $ids 单个id或者多个id
	 * @param  string $table_name 表名
	 * @param  string $id_key 主键名
	 * @return bool
	 */
	public function delete($ids,$id_key = 'id') {
		if(empty($ids)){
			return array();
		}
		return $this->init_db()->delete($ids,$this->table_name,$id_key);
	}

	/**
	 * SQL操作-通过条件语句删除数据
	 * DAO中使用方法：$this->dao->db->delete_by_field($field, $table_name)
	 * @param  array  $field 条件数组
	 * @param  string $table_name 表名
	 * @return bool
	 */
	public function deleteByField($whereArray) {
		if(empty($whereArray)){
			return array();
		}
		return $this->init_db()->delete_by_field($whereArray,$this->table_name);
	}
}