<?PHP
class mainController extends BaseAdminController{
	public $initphp_list = array(
	    'home',
	    'bookList',
	    'buyBook',
	    'bookAdd',
	    'bookEdit',
	    'erweima',
	    'chapterList',
	    'addChapter',
	    'editChapter',
	    'bookSummoney',
	    'priceCompute',
	    'bookInfo',
	    'userList',
	    'slide',
	    'slideEdit',
	    'slideAdd',
	    'delslide',
	    'caiwuList',
	    'bookPush',
	    'pushbook',
	    'delPush',
	    'setOneCp',
	    'DoOther',
	    'countWord',
	    'delOrderCsv',
	    'timediff',
	    'bookType',
	    'bookTypeAdd',
	    'bookTypeEdit',
	    'source',
	    'sourceAdd',
	    'sourceEdit',
	    'sourceUrl',
	    'sourceUrlEdit',
	    'userLook',
	    'cread');
	public $publicFunction;
	public $sessionDo;
	public $configDo;
	public $doMain;
	public $authorMain;
	public $imgMain;
	public $bookPicMain;
	public $thisUrl;
	public $admin_id;
	public $wxRedis;
	public function __construct(){
		parent::__construct();
		$this->configDo = InitPHP::getConfig();
		$this->admin_id=parent::before();
		$this->publicFunction = $this->getLibrary('function');
		$this->sessionDo = $this->getUtil('session');
		//$this->wxRedis = $this->getLibrary('redis');
		$config=$this->configDo['redis']['default'];
		//$this->wxRedis->init($config);
		$this->doMain=$this->configDo['url'];
		$this->authorMain=$this->configDo['author_url'];
		$this->imgMain=$this->configDo['img_url'];
		$this->bookPicMain=$this->imgMain.'Uploads/books/';
		$this->view->assign('doMain',$this->doMain);
		$this->view->assign('authorMain',$this->authorMain);
		$this->view->assign('bookPicMain',$this->bookPicMain);
		$this->thisUrl=$this->publicFunction->get_url();
		$this->view->assign('thisUrl',$this->thisUrl);

	}
	public function index(){
		if($this->admin_id){
			$this->view->set_tpl("main/index");
			$this->view->display();
		}else{
			header("Location:".$this->doMain."index.php?c=login&a=index");
		}
	}
	public function home(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$this->view->set_tpl("main/home");
		$this->view->display();
	}
	public function buyBook(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$book_name=$this->controller->get_get("book_name");
		$data['startTime']=$this->controller->get_get("start_time");
		$data['endTime']  =$this->controller->get_get("end_time");
		$booksService = InitPHP::getService("books");
		if(!empty($book_name)){
			$info=$booksService->getLikeBooks("book_name like '%".$book_name."%'");
			$bookId=$info['id'];
		}else{
			$bookId=0;
		}
		$userService = InitPHP::getService("user");
		$list=$userService->bookOrderSumList($data,$bookId);

		foreach($list as $k=>$v){
			$orderCount=$userService->bookOrderUserCount($v['book_id'],$data);
			$bookInfo=$booksService->getBooks($v['book_id']);
			$list[$k]['info']=$bookInfo;
			$list[$k]['orderCount']=$orderCount;
		}
		$this->view->assign('book_name',$book_name);
		$this->view->assign('data',$data);
		$this->view->assign('bookList',$list);
		$this->view->set_tpl("main/buyBook");
		$this->view->display();
	}
	public function bookList(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$p=$this->controller->get_get("page");
		$book_name=$this->controller->get_get("book_name");
		$typeId=$this->controller->get_get("typeId");
		$pageNum=20;
		if(empty($p)){
			$p=1;
		}
		$where='1=1';
		if(!empty($book_name)){
			$url.="&book_name=".$book_name;
			$where.=" and book_name like '%".$book_name."%'";
			$this->view->assign('book_name',$book_name);
		}
		if(!empty($typeId)){
			$url.="&typeId=".$typeId;
			$where.=' and type like "T_%type'.$typeId.',%"';
			$this->view->assign('typeId',$typeId);
		}
		$bookTypeService = InitPHP::getService("bookType");
		$typeList=$bookTypeService->getAll();
		$this->view->assign('typeList',$typeList);
		$booksService = InitPHP::getService("books");
		$bookList=$booksService->getBooksList($pageNum,$where,$p);
		$booksCount=$booksService->getBooksCount();
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($booksCount,$pageNum,$this->thisUrl,true);
		$this->view->assign('bookList',$bookList);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("main/bookList");
		$this->view->display();
	}
	public function bookAdd(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookTypeService = InitPHP::getService("bookType");
		$typeList=$bookTypeService->getAll("parentId>0");
		$this->view->assign('typeList',$typeList);
		$this->view->set_tpl("main/bookAdd");
		$this->view->display();
	}
	public function bookEdit(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookId=$this->controller->get_get("bookId");
		$booksService = InitPHP::getService("books");
		$bookInfo=$booksService->getBooks($bookId);
		$clickInfo=$booksService->getBooksClick(array("book_id"=>$bookId));
		$bookTypeService = InitPHP::getService("bookType");
		$typeList=$bookTypeService->getAll("parentId>0");
		$typeStr=str_replace("type","",$bookInfo['type']);
		$typeStr=str_replace("T_","",$typeStr);
		$typeArray=explode(',',$typeStr);
		$typeArray=array_filter($typeArray);
		$this->view->assign('typeArray',$typeArray);
		$this->view->assign('typeList',$typeList);
		$this->view->assign('bookInfo',$bookInfo);
		$this->view->assign('clickInfo',$clickInfo);
		$this->view->set_tpl("main/bookEdit");
		$this->view->display();
	}
	public function erweima(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookId=$this->controller->get_get("bookId");
		$booksService = InitPHP::getService("books");
		$bookInfo=$booksService->getBooks($bookId);
		$this->view->assign('bookInfo',$bookInfo);
		$this->view->set_tpl("main/erweima");
		$this->view->display();
	}

	public function chapterList(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookId=$this->controller->get_get("bookId");
		if(empty($bookId)){
			return false;
		}
		$booksService = InitPHP::getService("books");
		$chapterList=$booksService->getChapterList($bookId);
		$this->view->assign('bookId',$bookId);
		$this->view->assign('chapterList',$chapterList);
		$this->view->set_tpl("main/chapterList");
		$this->view->display();
	}
	public function addChapter(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_get("id");
		$this->view->assign('book_id',$id);
		$this->view->set_tpl("main/addChapter");
		$this->view->display();
	}
	public function editChapter(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_get("id");
		$booksService = InitPHP::getService("books");
		$chapterInfo=$booksService->getChapter($id);
		$this->view->assign('book_id',$id);
		$this->view->assign('chapterInfo',$chapterInfo);
		$this->view->set_tpl("main/editChapter");
		$this->view->display();
	}

	public function delslide(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_post("id");
		$booksService = InitPHP::getService("books");
		if($booksService->delslide($id)){
			echo '{"res":true,"msg":"删除成功"}';
		}else{
			echo '{"res":false,"msg":"删除失败"}';
		}
	}
	public function slide(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$booksService = InitPHP::getService("books");
		$slideList=$booksService->getSlide();
		$this->view->assign('slideList',$slideList);
		$this->view->set_tpl("main/slide");
		$this->view->display();
	}
	public function slideAdd(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$this->view->set_tpl("main/slideAdd");
		$this->view->display();
	}
	public function slideEdit(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_get("id");
		$booksService = InitPHP::getService("books");
		$info=$booksService->getOneSlide($id);
		$this->view->assign('info',$info);
		$this->view->set_tpl("main/slideEdit");
		$this->view->display();
	}
	public function bookType(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookTypeService = InitPHP::getService("bookType");
		$typeList=$bookTypeService->getAll("parentId=0");
		foreach($typeList as $k=>$v){
			$typeList[$k]['list']=$bookTypeService->getAll("parentId=".$v['id']);
		}
		$this->view->assign('typeList',$typeList);
		$this->view->set_tpl("main/bookType");
		$this->view->display();
	}
	public function bookTypeAdd(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookTypeService = InitPHP::getService("bookType");
		$typeList=$bookTypeService->getAll("parentId=0");
		$this->view->assign('typeList',$typeList);
		$this->view->set_tpl("main/bookTypeAdd");
		$this->view->display();
	}
	public function bookTypeEdit(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_get("id");
		$bookTypeService = InitPHP::getService("bookType");
		$info=$bookTypeService->getType($id);
		$typeList=$bookTypeService->getAll("parentId=0");
		$this->view->assign('typeList',$typeList);
		$this->view->assign('info',$info);
		$this->view->set_tpl("main/bookTypeEdit");
		$this->view->display();
	}
	public function priceCompute(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$data['book_id']=$this->controller->get_post("bookId");
		$data['vipStart']=$this->controller->get_post("vipStart");
		$data['priceStart']=$this->controller->get_post("priceStart");
		$data['unitPrice']=$this->controller->get_post("unitPrice");
		$booksService = InitPHP::getService("books");
		$booksService->priceCompute($data,$type);
		header("Location:".$this->doMain."index.php?c=main&a=chapterList&bookId=".$data['book_id']);
	}
	public function bookSummoney(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$data['start_date']=$this->controller->get_get("sDate");
		$data['end_date']=$this->controller->get_get("eDate");
		$data['user_id']=$this->controller->get_get("uId");
		$data['third_source']=$this->controller->get_get("Ts");
		$data['book_id']=$this->controller->get_get("bId");
		$data['chapter_id']=$this->controller->get_get("cId");
		$booksService = InitPHP::getService("books");
		$Summoney=$booksService->bookSummoney($data);
		$ChapterData['book_id']=$data['book_id'];
		$ChapterData['id']=$data['chapter_id'];
		if(count(array_filter($ChapterData))==2){
			$ChapterInfo=$booksService->getOneChapter($ChapterData);
			$csvName='./upFile/'.$data['book_id'].'_'.$data['start_date'].'_'.$data['end_date'].'.csv';
			$file = fopen($csvName,'a+');
			$title=iconv('utf-8','gbk','第'.$ChapterInfo['chapter'].'章'.$ChapterInfo['title']);
			$chapter=array($title,$Summoney);
			fputcsv($file,$chapter);
			fclose($file);
		}
		echo "￥ ".$Summoney;
	}
	public function pushbook(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$pushType=$this->controller->get_get("tag");
		if(empty($pushType)){
			$pushType='jxtj';
		}
		$p=$this->controller->get_get("page");
		$pageNum=20;
		if(empty($p)){
			$p=1;
		}
		$booksService = InitPHP::getService("books");
		$pushList=$booksService->getPushBook($pushType,$pageNum,$p);
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($pushList['count'],$pageNum,$this->doMain."index.php?c=main&a=pushbook&tag=".$pushType,true);
		$this->view->assign('pushType',$pushType);
		$this->view->assign('pushList',$pushList['list']);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("main/pushbook");
		$this->view->display();
	}

	public function userList(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$p=$this->controller->get_get("page");
		$user_id=$this->controller->get_get("user_id");
		$third_source=$this->controller->get_get("third_source");
		$reg_type=$this->controller->get_get("reg_type");
		$start_time=$this->controller->get_get("start_time");
		$end_time=$this->controller->get_get("end_time");
		if(empty($p)){
			$p=1;
		}
		$pageNum=20;
		$field=array();
		if(!empty($user_id)){
			$field['id']=$userWhere['user_id']=$user_id;
			$pageUrl.='&user_id='.$user_id;
		}
		if(!empty($reg_type)){
			$field['source']=$userWhere['reg_type']=$reg_type;
			$pageUrl.='&reg_type='.$reg_type;
		}
		if(!empty($third_source)){
			$field['third_source']=$userWhere['third_source']=$third_source;
			$pageUrl.='&third_source='.$third_source;
		}
		$other=false;
		if(!empty($start_time)){
			$other='create_time>='.strtotime($start_time);
			$userWhere['start_time']=$start_time;
			$pageUrl.='&start_time='.$start_time;
			if(!empty($end_time)){
				$userWhere['end_time']=$end_time;
				$other.=' and create_time<='.strtotime($end_time);
				$pageUrl.='&end_time='.$end_time;
			}else{
				$userWhere['end_time']=date("Y-m-d H:i:s",time());
				$other.=' and create_time<='.time();
				$pageUrl.='&end_time='.date("Y-m-d H:i:s",time());
			}
		}
		$userService = InitPHP::getService("user");
		$userPaySum=$userService->userPaySum($userWhere);
		$thirdList=$userService->thirdSourceList();
		$userList=$userService->userList($pageNum,$p,$field,$other);
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($userList[1],$pageNum,$this->doMain."index.php?c=main&a=userList".$pageUrl,true);
		$userMoney=$userPaySum['moneySum'];
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('userList',$userList[0]);
		$this->view->assign('userCount',$userList[1]);
		$this->view->assign('userMoney',$userMoney);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("main/userList");
		$this->view->display();
	}

	public function caiwuList(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$p=$this->controller->get_get("page");
		$user_id=$this->controller->get_get("user_id");
		$third_source=$this->controller->get_get("third_source");
		$source=$this->controller->get_get("source");
		$start_time=$this->controller->get_get("start_time");
		$end_time=$this->controller->get_get("end_time");
		if(empty($p)){
			$p=1;
		}
		$pageNum=20;
		if(!empty($user_id)){
			$userWhere['user_id']=$user_id;
			$pageUrl.='&user_id='.$user_id;
		}
		if(!empty($source)){
			$recordWhere['source']=$source;
			$pageUrl.='&source='.$source;
		}
		if(!empty($third_source)){
			$userWhere['third_source']=$third_source;
			$pageUrl.='&third_source='.$third_source;
		}
		$other=false;
		if(!empty($start_time)){
			$recordWhere['start_time']=$start_time;
			$pageUrl.='&start_time='.$start_time;
			if(!empty($end_time)){
				$recordWhere['end_time']=$end_time;
				$pageUrl.='&end_time='.$end_time;
			}else{
				$recordWhere['end_time']=date("Y-m-d H:i:s",time());
				$pageUrl.='&end_time='.date("Y-m-d H:i:s",time());
			}
		}
		$userService = InitPHP::getService("user");
		$userPayList=$userService->userPayList($userWhere,$recordWhere,$pageNum,$p);
		$userPaySum=$userService->userPaySum($userWhere,$recordWhere);
		$thirdList=$userService->thirdSourceList();
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($userPayList['count'],$pageNum,$this->doMain."index.php?c=main&a=caiwuList".$pageUrl,true);
		$userMoney=$userPaySum['moneySum'];
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('userPayList',$userPayList['list']);
		$this->view->assign('userPayCount',$userPayList['count']);
		$this->view->assign('userMoney',$userMoney);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('recordWhere',$recordWhere);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("main/caiwuList");
		$this->view->display();
	}


	public function bookInfo(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$book_id=$this->controller->get_get("id");
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('list',$list);
		$this->view->set_tpl("index/bookInfo");
		$this->view->display();
	}
	public function bookPush(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$push['book_id']=$this->controller->get_post("book_id");
		$push['sort']=$this->controller->get_post("sort");
		$push['push_type']=$this->controller->get_post("pushType");
		$push['create_time']=time();
		if(count(array_filter($push))<4){
			header("Location:".$this->doMain."index.php?c=main&a=pushbook&tag=".$push['push_type']);
		}else{
			$booksService = InitPHP::getService("books");
			if($booksService->insertPush($push)){
				header("Location:".$this->doMain."index.php?c=main&a=pushbook&tag=".$push['push_type']);
			}
		}
	}
	public function delPush(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$id=$this->controller->get_post("id");
		$booksService = InitPHP::getService("books");
		if($booksService->deletepush($id)){
			echo '{"res":true,"msg":"删除成功"}';
		}else{
			echo '{"res":false,"msg":"删除失败"}';
		}
	}
	public function countWord(){
		$start_time=$this->controller->get_get("start_time");
		$end_time=$this->controller->get_get("end_time");
		if(empty($start_time) && empty($end_time)){
			$start_time=date("Y-m-d",time());
			$end_time=$start_time;
		}
		$third_source=$this->controller->get_get("third_source");
		$source=$this->controller->get_get("source");
		$userService = InitPHP::getService("user");
		$userWhere['third_source']=$third_source;
		$userWhere['start_time']=$start_time;
		$userWhere['end_time']=$end_time;
		$Tsource=$third_source;
        $thirdList=$userService->thirdSourceList();
		if($third_source==''){
			foreach ($thirdList as $k=>$v) {
				$list[$k]['third_source']=$v['name'];
				$list[$k]['regNum']=$userService->thirdSourceUser($v['sourceKey'],$start_time,$end_time);
				$list[$k]['patText']=$userService->thirdSourcePayRecord($v['sourceKey'],$start_time,$end_time);
				$list[$k]['userPayNum']=$userService->thirdSourcePayUser($v['sourceKey'],$start_time,$end_time);
				$list[$k]['dianji']=$userService->thirdSourceAllSum($v['sourceKey'],$start_time,$end_time);
			}
		}else{
				$list[0]['third_source']=$third_source;
				$list[0]['regNum']=$userService->thirdSourceUser($third_source,$start_time,$end_time);
				$list[0]['patText']=$userService->thirdSourcePayRecord($third_source,$start_time,$end_time);
				$list[0]['userPayNum']=$userService->thirdSourcePayUser($third_source,$start_time,$end_time);
				$list[0]['dianji']=$userService->thirdSourceAllSum($third_source,$start_time,$end_time);
		}
		/*if(empty($start_time) && empty($end_time)){
			$dayInfo['day']=6;
		}else{
			$dayInfo=$this->timediff($start_time,$end_time);
		}
		for($i=0;$i<=$dayInfo['day'];$i++){
			$list[$i]['date']=date('Y-m-d',time()-86400*$i);
			$list[$i]['regNum']=$userService->regSum($list[$i]['date'],$Tsource);
			$list[$i]['qudaoNum']=$userService->thirdSourceSum($list[$i]['date'],$Tsource);
			$zhifubaoPay=$userService->TJpaySum($list[$i]['date'],$Tsource,1);
			$weixinPay=$userService->TJpaySum($list[$i]['date'],$Tsource,2);
			$Pay=$userService->TJpaySum($list[$i]['date'],$Tsource);
			if(empty($third_source)){
				$list[$i]['third_source']='全部';
			}else{
				$list[$i]['third_source']=$third_source;
			}
			$list[$i]['patText']='微信（￥ '.$weixinPay.'）+支付宝（￥ '.$zhifubaoPay.'）=￥ '.$Pay;
		}*/

		//$this->wxRedis->set('count_pay_sum',$list,86400);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('list',$list);
		$this->view->set_tpl("main/countWord");
		$this->view->display();
	}
	public function userLook(){
		$book_id=$this->controller->get_get("book_id");
		$userWhere['start_time']=$this->controller->get_get("start_time");
		$userWhere['end_time']=$this->controller->get_get("end_time");
		$userService = InitPHP::getService("user");
		$booksService = InitPHP::getService("books");
		$pageNum=25;
		$p=1;
		$list=$userService->userLookPage($userWhere['start_time'],$userWhere['end_time'],$book_id,$p,$pageNum);
		foreach ($list as $k=>$v) {
			foreach ($v['bookList'] as $bk=>$bv) {
				$info=$booksService->getChapter($bv['cid']);
				$bookList[$bk]="书号".$bv['book_id']." 从 ".$info['sort']."章 进入充值<br/>";
			}
			$list[$k]['bookList']=$bookList;
			$payLookInfo=$userService->userPaySumLook($v['user_id'],$userWhere['start_time'],$userWhere['end_time']);
			$list[$k]['userPaySum']=$payLookInfo['moneySum'];
			if(empty($payLookInfo['create_time'])){
				$list[$k]['orderText']="无";
			}else{
				$orderInfo=$userService->lookOrderPay($v['user_id'],$payLookInfo['create_time']);
				$info=$booksService->getChapter($orderInfo['chapter']);
				$list[$k]['orderText']="听书号".$orderInfo['book_id']." 第".$info['sort']."章 <br/>";
			}
		}
		$this->view->assign('book_id',$book_id);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('list',$list);
		$this->view->set_tpl("main/userLook");
		$this->view->display();
	}

	public function cread(){
		if(!$this->admin_id){
			header("Location:".$this->doMain."index.php?c=login&a=index");die;
		}
		$bookId=$this->controller->get_get("bookId");
		$userWhere['third_source']=$this->controller->get_get("third_source");
		$userService = InitPHP::getService("user");
		$thirdList=$userService->thirdSourceList();
		if(!empty($bookId)){
			$booksService = InitPHP::getService("books");
			$chapterList=$booksService->getChapterList($bookId);
			$sourceuserNum=$userService->getsourceUserAll($userWhere['third_source']);
			foreach ($chapterList as $k=>$v) {
				$userNum=$userService->historyBook($v['id'],$userWhere['third_source']);
				$chapterList[$k]['userNum']=$userNum;
				$chapterList[$k]['scale']=sprintf("%.2f",$userNum/$sourceuserNum*100);
			}
		}
		$this->view->assign('bookId',$bookId);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('chapterList',$chapterList);
		$this->view->set_tpl("main/cread");
		$this->view->display();
	}
	public function source(){
		$sourceService = InitPHP::getService("source");
		$list=$sourceService->getAllSource();
		foreach($list as $k=>$v){
			$listUrl=$sourceService->getAllSourceUrl($v['sourceKey']);
			$list[$k]['url']=$listUrl;
		}
		$this->view->assign('list',$list);
		$this->view->set_tpl("main/source");
		$this->view->display();
	}
	public function sourceAdd(){
		$this->view->assign('list',$data);
		$this->view->set_tpl("main/sourceAdd");
		$this->view->display();
	}
	public function sourceEdit(){
		$id=$this->controller->get_get("id");
		$sourceService = InitPHP::getService("source");
		$info=$sourceService->getSource($id);
		$this->view->assign('info',$info);
		$this->view->set_tpl("main/sourceEdit");
		$this->view->display();
	}
	public function sourceUrl(){
		$sourceService = InitPHP::getService("source");
		$list=$sourceService->getAllSource();
		$this->view->assign('list',$list);
		$this->view->set_tpl("main/sourceUrl");
		$this->view->display();
	}
	public function sourceUrlEdit(){
		$id=$this->controller->get_get("id");
		$sourceService = InitPHP::getService("source");
		$list=$sourceService->getAllSource();
		$this->view->assign('list',$list);
		$info=$sourceService->getSourceUrl($id);
		$this->view->assign('list',$list);
		$this->view->assign('info',$info);
		$this->view->set_tpl("main/sourceUrlEdit");
		$this->view->display();
	}
	public  function error(){
	    $this->view->set_tpl("main/error");
	    $this->view->display();
	}
	public function timediff($beginTime,$endTime){
		if(empty($beginTime)){
			$beginTime=date("Y-m-d",time());
		}
		if(empty($endTime)){
			$endTime=date("Y-m-d",time());
		}
		  $begin_time=strtotime($beginTime);
		  $end_time=strtotime($endTime);
		  if($begin_time < $end_time ) {
			$starttime = $begin_time;
			$endtime = $end_time;
		  }else{
			$starttime = $end_time;
			$endtime = $begin_time;
		  }
		  $timediff = $endtime - $starttime;
		  $days = intval( $timediff / 86400 );
		  $remain = $timediff % 86400;
		  $hours = intval( $remain / 3600 );
		  $remain = $remain % 3600;
		  $mins = intval( $remain / 60 );
		  $secs = $remain % 60;
		  $res = array("day" =>$days,"hour" =>$hours,"min" =>$mins,"sec" =>$secs );
		  return $res;
	}
	public function getUrl($c,$a){
		return $this->doMain.'index.php?c='.$c.'&a='.$a;
	}
	public function stripTags($content) {
		$content=strip_tags($content,'<p>');
		$content=preg_replace("/\s*style=('|\")[^\"]*('|\")/",'',$content);
		$content=preg_replace("/(\s|\&nbsp\;)*/","",$content);
		$content=preg_replace("/<p>\s(?=\s)/",'<p>',$content);
		$content=preg_replace('/<p>　*/','<p>',$content);
		return $content;
	}

}
