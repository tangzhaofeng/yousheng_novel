<?PHP
class doController extends BaseAdminController{
	public $initphp_list = array('bookTypeAdd','bookTypeEdit','addChapter','editChapter','bookAdd','bookEdit','erweima','slideAdd','slideEdit','recommendEdit', 'delChapter','sourceAdd','sourceEdit','sourceUrlAdd','sourceUrlEdit','authorAudioEdit');
	public $publicFunction;
	public $sessionDo;
	public $configDo;
	public $doMain;
	public $thisUrl;
	public $admin_id;
	public $wxRedis;
	public $excelDo;
	public $imgMain;
	public function __construct(){
		parent::__construct();
		$this->configDo = InitPHP::getConfig();
		$this->admin_id=parent::before();
		$this->doMain=$this->configDo['url'];
		$this->imgMain=$this->configDo['img_url'];
		//$this->wxRedis = $this->getLibrary('redis');
		$config=$this->configDo['redis']['default'];
		//$this->wxRedis->init($config);
		$this->view->assign('doMain',$this->doMain);

	}
	public function sourceAdd(){
		if($this->admin_id){
			$data['name']=$this->controller->get_post('name');
			$data['sourceKey'] =$this->controller->get_post('sourceKey');
			$sourceService = InitPHP::getService("source");
			if($sourceService->insert($data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=source",0);
			}
		}
	}
	public function sourceEdit(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$data['name']=$this->controller->get_post('name');
			$data['sourceKey'] =$this->controller->get_post('sourceKey');
			$sourceService = InitPHP::getService("source");
			if($sourceService->upSource($id,$data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=source",0);
			}
		}
	}
	public function sourceUrlAdd(){
		if($this->admin_id){
			$data['sourceKey']=$this->controller->get_post('sourceKey');
			$data['book_id'] =$this->controller->get_post('book_id');
			$data['chapter'] =$this->controller->get_post('chapter');
			$data['start_time'] =$this->controller->get_post('start_time');
			$sourceService = InitPHP::getService("source");
			if($sourceService->insertUrl($data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=source",0);
			}
		}
	}
	public function sourceUrlEdit(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$data['sourceKey']=$this->controller->get_post('sourceKey');
			$data['book_id'] =$this->controller->get_post('book_id');
			$data['chapter'] =$this->controller->get_post('chapter');
			$data['start_time'] =$this->controller->get_post('start_time');
			$sourceService = InitPHP::getService("source");
			if($sourceService->upSourceUrl($id,$data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=source",0);
			}
		}
	}
	public function bookTypeAdd(){
		if($this->admin_id){
			$data['typeName']=$this->controller->get_post('typeName');
			$book_pic=$this->controller->get_post('book_pic');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>1,'bookPic'=>$book_pic,'mulu'=>'type'));
			if(!empty($bookPic)){
				$data['ico']=$bookPic;
			}
			$data['parentId'] =$this->controller->get_post('parentId');
			$data['state'] =$this->controller->get_post('state');
			$bookTypeService = InitPHP::getService("bookType");
			if($bookTypeService->insert($data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=bookType",0);
			}
		}
	}
	public function bookTypeEdit(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$data['typeName']=$this->controller->get_post('typeName');
			$book_pic=$this->controller->get_post('book_pic');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>1,'bookPic'=>$book_pic,'mulu'=>'type'));
			if(!empty($bookPic)){
				$data['ico']=$bookPic;
			}
			$data['parentId'] =$this->controller->get_post('parentId');
			$data['state'] =$this->controller->get_post('state');
			$bookTypeService = InitPHP::getService("bookType");
			if($bookTypeService->upType($id,$data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=bookType",0);
			}
		}
	}
	public function slideAdd(){
		if($this->admin_id){
			$data['title']=$this->controller->get_post('title');
			$book_pic=$this->controller->get_post('image');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>1,'bookPic'=>$book_pic,'mulu'=>'huandeng'));
			if(!empty($bookPic)){
				$data['image']=$bookPic;
			}
			$data['url'] =$this->controller->get_post('url');
			$data['sort'] =$this->controller->get_post('sort');
			$booksService = InitPHP::getService("books");
			if($booksService->insertSlide($data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=slide",0);
			}
		}
	}
	public function slideEdit(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$data['title']=$this->controller->get_post('title');
			$book_pic=$this->controller->get_post('image');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>1,'bookPic'=>$book_pic,'mulu'=>'huandeng'));
			if(!empty($bookPic)){
				$data['image']=$bookPic;
			}
			$data['url'] =$this->controller->get_post('url');
			$data['sort'] =$this->controller->get_post('sort');
			$booksService = InitPHP::getService("books");
			if($booksService->upSlide($id,$data)){
				$this->controller->redirect($this->doMain."index.php?c=main&a=slide",0);
			}
		}
	}
	public function recommendEdit(){
	    if($this->admin_id){
	        $id=$this->controller->get_post('id');
	        $book_title=$this->controller->get_post('book_title');
	        if(!empty($book_title)){
	            $data['book_title']=$book_title;
	        }else{
	            $data['book_title']='';
	        }
	        $booksService = InitPHP::getService("books");
	        if($booksService->upRecommend($id,$data)){
	            $this->controller->redirect($this->doMain."index.php?c=main&a=pushbook&tag=chapRec",0);
	        }
	    }
	}
	public function authorAudioEdit(){
	    if($this->admin_id){
	        $id=$this->controller->get_post('id');
	        $authorAudio=$this->controller->get_post('audio_url');
	        if(!empty($authorAudio)){
	            $data['authorAudio']=$authorAudio;
	        }else{
	            $data['authorAudio']='';
	        }
	        $booksService = InitPHP::getService("books");
	        if($booksService->upAuthorAudio($id,$data)){
	            $this->controller->redirect($this->doMain."index.php?c=main&a=bookList",0);
	        }
	    }
	}
	public function bookAdd(){
		if($this->admin_id){
			$click_count=$this->controller->get_post('click_count');
			$data['book_name']=$this->controller->get_post('book_name');
			$type=$this->controller->get_post('type');
			$typeSte='T_';
			foreach($type as $k=>$v){
				$typeSte.='type'.$v.',';
			}
			$data['type']     =$typeSte;
			$data['author_id']=$this->controller->get_post('author_id');
			$book_pic=$this->controller->get_post('book_pic');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>$data['author_id'],'bookPic'=>$book_pic));
			if(!empty($bookPic)){
				$data['book_pic']=$bookPic;
			}
			$data['author_name']=$this->controller->get_post('author_name');
			$data['author_tape']=$this->controller->get_post('author_tape');
			$data['book_case']=$this->controller->get_post('book_case');
			$data['give']=$this->controller->get_post('give');
			$data['descriptions'] =$this->controller->get_post('descriptions');
			$data['notice'] =$this->controller->get_post('notice');
			$data['is_serial']=$this->controller->get_post('is_serial');
			$data['state']=$this->controller->get_post('state');
			$booksService = InitPHP::getService("books");
			$bookId=$booksService->insert($data);
			$booksService->insertclickbook(array("book_id"=>$bookId,"click_count"=>$click_count));
			if($bookId){
				header("location:".$this->doMain."index.php?c=main&a=bookList");
				//$this->controller->redirect($this->doMain."index.php?c=main&a=bookList",0);
			}
		}
	}
	public function bookEdit(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$click_count=$this->controller->get_post('click_count');
			$data['book_name']=$this->controller->get_post('book_name');
			$type=$this->controller->get_post('type');
			$typeSte='T_';
			foreach($type as $k=>$v){
				$typeSte.='type'.$v.',';
			}
			$data['type']     =$typeSte;
			$data['author_id']=$this->controller->get_post('author_id');
			$book_pic=$this->controller->get_post('book_pic');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>$data['author_id'],'bookPic'=>$book_pic));
			if(!empty($bookPic)){
				$data['book_pic']=$bookPic;
			}
			$data['author_name']=$this->controller->get_post('author_name');
			$data['author_tape']=$this->controller->get_post('author_tape');
			$data['book_case']=$this->controller->get_post('book_case');
			$data['give']=$this->controller->get_post('give');
			$data['descriptions'] =$this->controller->get_post('descriptions');
			$data['notice'] =$this->controller->get_post('notice');
			$data['is_serial']=$this->controller->get_post('is_serial');
			$data['state']=$this->controller->get_post('state');
			$data['authorAudio']=$this->controller->get_post('authorAudio');
			$booksService = InitPHP::getService("books");
			$booksService->upBooksClick(array("click_count"=>$click_count),$id);
			if($booksService->upBooks($id,$data)){
				header("location:".$this->doMain."index.php?c=main&a=bookList");
				//$this->controller->redirect($this->doMain."index.php?c=main&a=bookList",0);
			}
		}
	}
	public function erweima(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$book_pic=$this->controller->get_post('erweima_url');
			$visible_erweima_chapter=$this->controller->get_post('visible_erweima_chapter');
			$bookPic=self::post($this->imgMain."picDo.php",array('sgin'=>'a7ed7a10dfb05e4d5c0622136d227534','id'=>$id,'bookPic'=>$book_pic,'mulu'=>'erweima'));
			if(!empty($bookPic)){
				$data['erweima_url']=$bookPic;
			}
			if(!empty($visible_erweima_chapter)) {
			    $data['visible_erweima_chapter']=$visible_erweima_chapter;
			}
			$booksService = InitPHP::getService("books");
			if($booksService->upBooks($id,$data)){
				header("location:".$this->doMain."index.php?c=main&a=bookList");
				//$this->controller->redirect($this->doMain."index.php?c=main&a=bookList",0);
			}else{
			    header("location:".$this->doMain."index.php?c=main&a=error");
		    }
		}
	}
	public function addChapter(){
		if($this->admin_id){
			$data['book_id']=$this->controller->get_post('book_id');
			$chapter=$this->controller->get_post('chapter');
			$data['title']=$this->controller->get_post('title');
			$data['audio_url']=$this->controller->get_post('audio_url');
			$data['price']=$this->controller->get_post('price');
			/*if(empty($data['audio_url'])){
				die;
			}*/
			$chapter=self::_cnum2num($chapter);
			$data['chapter']=$chapter;
			$data['sort']=$chapter;
			$booksService = InitPHP::getService("books");
			if($booksService->insertChapter($data)){
			    header("location:".$this->doMain."index.php?c=main&a=chapterList&bookId=".$data['book_id']);
				//$this->controller->redirect($this->doMain."index.php?c=main&a=chapterList&bookId=".$data['book_id'],0);
			}
		}
	}
	public function editChapter(){
		if($this->admin_id){
			$id=$this->controller->get_post('id');
			$book_id=$this->controller->get_post('book_id');
			$chapter=$this->controller->get_post('chapter');
			$data['title']=$this->controller->get_post('title');
			$data['price']=$this->controller->get_post('price');
			$data['audio_url']=$this->controller->get_post('audio_url');
			/*if(empty($data['audio_url'])){
				die("");
			}*/
			$chapter=self::_cnum2num($chapter);
			$data['chapter']=$chapter;
			$data['sort']=$chapter;
			$booksService = InitPHP::getService("books");
			if($booksService->upChapter(array("id"=>$id),$data)){
				header("location:".$this->doMain."index.php?c=main&a=chapterList&bookId=".$book_id);
				//$this->controller->redirect($this->doMain."index.php?c=main&a=chapterList&bookId=".$book_id,0);
			}
		}
	}
	public function delChapter(){
		if($this->admin_id){
			$id=$this->controller->get_post("id");
			$booksService = InitPHP::getService("books");
			if($booksService->delChapter($id)){
				echo '{"res":true,"msg":"删除成功"}';
			}else{
				echo '{"res":false,"msg":"删除失败"}';
			}
		}
	}
	public static function Chapter2Sort($content){
		preg_match('/第\s*(一|二|三|四|五|六|七|八|九|十|零|百|千|万||0|1|2|3|4|5|6|7|8|9)*\s*(章|卷|节)/',$content,$matches);//提取章节号
		//去除多余字
		unset($content);
		unset($matches[1]);
		$content=preg_replace("/\s*/","",$matches[0]);
		$content=preg_replace("/第/","",$content);
		$content=preg_replace("/(章|卷|节)*/","",$content);
		return self::_cnum2num($content);//转化成数字
	}
	public static function _cnum2num($m){
		if(!preg_match('/^\d*$/',$m)){
			mb_internal_encoding("UTF-8");
			$num = 0;
			if(mb_strpos($m, '亿') !== false){
				$s = self::get_front_str($m, '亿');
				$num += self::cnum2num_recu($s) * 100000000;
				$m = self::skip_to_str($m, '亿');
			}
			if(strlen($m) && mb_strpos($m, '万') !== false){
				$s = self::get_front_str($m, '万');
				$num += self::cnum2num_recu($s) * 10000;
				$m = self::skip_to_str($m, '万');
			}
			if(strlen($m)){
				$num += self::cnum2num_recu($m);
			}
		}else{
			$num=$m;
		}
		return $num;
	}
	public static function cnum2num_recu($m){
		mb_internal_encoding("UTF-8");
		$cnum_basic_str = array("零","一","二","三","四","五","六","七","八","九");
		$num = 0;
		if(mb_strpos($m,'千') !== false){
			$s1 = self::get_front_str($m,'千');
			$num += intval(array_search($s1,$cnum_basic_str))*1000;
			$m = self::skip_to_str($m,'千');
		}
		if(strlen($m) && mb_strpos($m, '百') !== false){
			$s1 = self::get_front_str($m, '百');
			if(mb_substr($s1,0,1)=='零') $s1 = mb_substr($s1, 1);
			$num += intval(array_search($s1, $cnum_basic_str))*100;
			$m = self::skip_to_str($m, '百');
		}
		if(strlen($m) && mb_strpos($m, '十')!==false){
		if(mb_substr($m,0,1)=='十'){
			$m="一".$m;
		}
		$s1 = self::get_front_str($m, '十');
		if(mb_substr($s1,0,1)=='零')
			$s1 = mb_substr($s1, 1);
			$num += intval(array_search($s1,$cnum_basic_str))*10;
			$m = self::skip_to_str($m, '十');
		}
		if(strlen($m)){
			$s1 = str_replace(array('第','张','章','卷'), "" ,$m);//去除多余的，可能出现的无用字符
			if(mb_substr($s1,0,1)=='零') $s1 = mb_substr($s1, 1);
			$num += intval(array_search($s1, $cnum_basic_str));
		}
		return $num;
	}
	// 截取 $data 到 $s
	public static function skip_to_str($data,$s) {
		 mb_internal_encoding("UTF-8");
		 return mb_substr($data,mb_strpos($data,$s) + mb_strlen($s));
	}
	 // 取得 $s 前的字符
	public static function get_front_str($data,$s) {
		mb_internal_encoding("UTF-8");
		return mb_substr($data,0,mb_strpos($data,$s));
	}
    /**
     * 模拟POST请求
     *
     * @param string $url
     * @param array $fields
     * @param string $data_type
     *
     * @return mixed
     *
     * Examples:
     * ```
     * HttpCurl::post('http://api.example.com/?a=123', array('abc'=>'123', 'efg'=>'567'), 'json');
     * HttpCurl::post('http://api.example.com/', '这是post原始内容', 'json');
     * 文件post上传
     * HttpCurl::post('http://api.example.com/', array('abc'=>'123', 'file1'=>'@/data/1.jpg'), 'json');
     * ```
     */
    static public function post($url, $fields, $data_type='text') {
        $cl = curl_init();
        if(stripos($url, 'https://') !== FALSE) {
            curl_setopt($cl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($cl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($cl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($cl, CURLOPT_URL, $url);
        curl_setopt($cl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($cl, CURLOPT_POST, true);
        curl_setopt($cl, CURLOPT_POSTFIELDS, $fields);
        $content = curl_exec($cl);
        $status = curl_getinfo($cl);
        curl_close($cl);
        if (isset($status['http_code']) && $status['http_code'] == 200) {
            if ($data_type == 'json') {
                $content = json_decode($content);
            }
            return $content;
        } else {
            return FALSE;
        }
    }
}
