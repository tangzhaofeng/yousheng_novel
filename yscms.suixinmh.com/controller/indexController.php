<?PHP
class indexController extends BaseAdminController{
	public $initphp_list = array('main','bookList','bookInfo','userList','caiwuList','bookPush','pushbook','countWord','timediff');
	public $publicFunction;
	public $sessionDo;
	public $configDo;
	public $doMain;
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
		$this->view->assign('doMain',$this->doMain);
		$this->thisUrl=$this->publicFunction->get_url();
		$this->view->assign('thisUrl',$this->thisUrl);

	}
	public function index(){
		if($this->admin_id){
			header("Location:".$this->doMain."index.php?c=main&a=index");
		}else{
			header("Location:".$this->doMain."index.php?c=login&a=index");
		}
	}
	public function main(){
		/*$bookList[0]['name']='a';
		$bookList[0]['url']='a1';
		$bookList[1]['name']='aa';
		$bookList[1]['url']='a2';
		$menu['bookMenu']=$bookList;
		$menu['userMenu']=$bookList;
		echo json_encode($menu);*/
		$this->view->set_tpl("index/main");
		$this->view->display();
	}
	public function bookList(){
		$p=$this->controller->get_get("page");
		$pageNum=20;
		if(empty($p)){
			$p=1;
		}
		$where='1=1';
		if(!empty($keyWord)){
			$url.="&keyWord=".$keyWord;
			$where.=" and b.book_name like '%".$keyWord."%'";
			$this->view->assign('keyWord',$keyWord);
			$this->view->assign('delKey','<div class="delKey"></div>');
		}
		if(!empty($typeId)){
			$url.="&typeId=".$typeId;
			$where.=" and b.type=".$typeId;
			$this->view->assign('typeId',$typeId);
		}
		if(!empty($t)){
			$url.="&t=".$t;
			$this->view->assign('t',$t);
		}
		if(!empty($pid)){
			$url.="&pid=".$pid;
			$Ptype=$pid;
			$this->view->assign('pid',$pid);
		}else{
			$Ptype='min';
		}
		if(!empty($serial)){
			$url.="&serial=".$serial;
			if($serial=='lz'){
				$where.=" and b.is_serial=0";
			}
			if($serial=='wb'){
				$where.=" and b.is_serial=1";
			}
			$this->view->assign('serial',$serial);
		}
		if($vip!=''){
			$url.="&vip=".$vip;
			if($vip=='0'){
				$where.=" and b.is_vip=0";
			}
			if($vip=='1'){
				$where.=" and b.is_vip=1";
			}
			$this->view->assign('vip',$vip);
		}
		$booksService = InitPHP::getService("books");
		$bookList=$booksService->getBooksList($pageNum,$where,$p);
		$booksCount=$booksService->getBooksCount();
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($booksCount,$pageNum,$this->thisUrl,true);
		$this->view->assign('bookList',$bookList);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("index/bookList");
		$this->view->display();
	}
	public function userList(){
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
		//print_r($userPaySum);
		$userList=$userService->userList($pageNum,$p,$field,$other);
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($userList[1],$pageNum,$this->doMain."index.php?c=index&a=userList".$pageUrl,true);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('userList',$userList[0]);
		$this->view->assign('userCount',$userList[1]);
		$this->view->assign('userMoney',$userPaySum['moneySum']);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("index/userList");
		$this->view->display();
	}
	public function caiwuList(){
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
		//print_r($userWhere);
		//print_r($recordWhere);
		$userService = InitPHP::getService("user");
		$userPayList=$userService->userPayList($userWhere,$recordWhere,$pageNum,$p);
		$userPaySum=$userService->userPaySum($userWhere,$recordWhere);
		$thirdList=$userService->thirdSourceList();
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($userPayList['count'],$pageNum,$this->doMain."index.php?c=index&a=caiwuList".$pageUrl,true);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('userPayList',$userPayList['list']);
		$this->view->assign('userPayCount',$userPayList['count']);
		$this->view->assign('userMoney',$userPaySum['moneySum']);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('recordWhere',$recordWhere);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("index/caiwuList");
		$this->view->display();
	}

	public function bookInfo(){
		$book_id=$this->controller->get_get("id");
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('list',$list);
		$this->view->set_tpl("index/bookInfo");
		$this->view->display();
	}
	public function bookPush(){
		$push['book_id']=$this->controller->get_post("book_id");
		$push['sort']=$this->controller->get_post("sort");
		$push['push_type']=$this->controller->get_post("pushType");
		$push['create_time']=time();
		$booksService = InitPHP::getService("books");
		if($booksService->insertPush($push)){
			header("Location:".$this->doMain."index.php?c=index&a=pushbook&tag=".$push['push_type']);
		}
	}
	public function pushbook(){
		$pushType=$this->controller->get_get("tag");
		if(empty($pushType)){
			return false;
		}
		$p=$this->controller->get_get("page");
		$pageNum=20;
		if(empty($p)){
			$p=1;
		}
		$booksService = InitPHP::getService("books");
		$pushList=$booksService->getPushBook($pushType,$pageNum,$p);
		$pager= $this->getLibrary('pager'); //分页加载
		$page_html = $pager->pager($pushList['count'],$pageNum,$this->doMain."index.php?c=index&a=pushbook&tag=".$pushType,true);
		$this->view->assign('pushList',$pushList['list']);
		$this->view->assign('page_html',$page_html);
		$this->view->set_tpl("index/pushbook");
		$this->view->display();
	}
	public function countWord(){
		$start_time=$this->controller->get_get("start_time");
		$end_time=$this->controller->get_get("end_time");
		$third_source=$this->controller->get_get("third_source");
		$source=$this->controller->get_get("source");
		$userService = InitPHP::getService("user");
		$userWhere['third_source']=$third_source;
		$userWhere['start_time']=$start_time;
		$userWhere['end_time']=$end_time;
		$Tsource=$third_source;
        if(empty($third_source)){
			$third_source='全部';
		}
		if(empty($start_time) && empty($end_time)){
			$dayInfo['day']=6;
		}else{
			$dayInfo=$this->timediff($start_time,$end_time);
		}
		for($i=0;$i<=$dayInfo['day'];$i++){
			$list[$i]['date']=date('Y-m-d',time()-86400*$i);
			$list[$i]['third_source']=$third_source;
			$list[$i]['regNum']=$userService->regSum($list[$i]['date'],$Tsource);
			$list[$i]['qudaoNum']=$userService->thirdSourceSum($list[$i]['date'],$Tsource);
			$zhifubaoPay=$userService->TJpaySum($list[$i]['date'],$Tsource,1);
			$weixinPay=$userService->TJpaySum($list[$i]['date'],$Tsource,2);
			$xianzaiPay=$userService->TJpaySum($list[$i]['date'],$Tsource,4);
			$Pay=$userService->TJpaySum($list[$i]['date'],$Tsource);
			$list[$i]['patText']='微信（￥ '.$weixinPay.'）+支付宝（￥ '.$zhifubaoPay.'）+现在（￥ '.$xianzaiPay.'）=￥ '.$Pay;
		}
		$thirdList=$userService->thirdSourceList();
		//$this->wxRedis->set('count_pay_sum',$list,86400);
		$this->view->assign('userWhere',$userWhere);
		$this->view->assign('thirdList',$thirdList);
		$this->view->assign('list',$list);
		$this->view->set_tpl("index/countWord");
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
