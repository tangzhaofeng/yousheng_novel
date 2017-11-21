<?PHP
class userController extends BaseUserController{
	public $initphp_list = array('bookCase','Cmoney','payList','buyList','zanList','shelf','history','unLogin','getUrl','wxPay','playerMessage');
	public $publicFunction;
	public $cookieDo;
	public $doMain;
	public $user_id;
	public $thisUrl;
	public $configDo;
	public $isWeiXin;
	public $h5Pay;
	public $weixinService;
	//public $wxRedis;
	public $sessionDo;
	public function __construct(){
		parent::__construct();
		$this->user_id=parent::before();
		$this->configDo = InitPHP::getConfig();
		$this->sessionDo = $this->getUtil('session');
		$this->publicFunction = $this->getLibrary('function');
		$this->cookieDo = $this->getUtil('cookie');
		$this->doMain=$this->configDo['url'];
		$this->thisUrl=$this->publicFunction->get_url();
		$this->staticsMain=$this->configDo['statics_url'];
		$this->authorMain=$this->configDo['author_url'];
		$this->h5Pay=$this->getLibrary('h5Pay');
		$this->weixinService = InitPHP::getService("weixinLogin");
		//$this->wxRedis = $this->getLibrary('redis');
		$config=$this->configDo['redis']['default'];
		//$this->wxRedis->init($config);
		$this->isWeiXin=false;
		if($this->publicFunction->isWeiXin()){
			if(!parent::before()){
				$sessonid=$this->sessionDo->get("sessonid");
				//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
				//header('location:'.$this->doMain.'index.php?c=login&a=weixin');die;
			}
			$this->isWeiXin=true;
		}
		$this->view->assign('isWeiXin',$this->isWeiXin);
		$this->view->assign('staticsMain',$this->staticsMain);
		$this->view->assign('thisUrl',$this->thisUrl);
		$this->view->assign('authorMain', $this->authorMain);
		$this->view->assign('doMain',$this->doMain);
	}
	public function index(){
		if($this->user_id){
			$authorUserService = InitPHP::getService("authorUser");
			$user=$authorUserService->getUser($this->user_id);
			$whereArray['user_id']=$this->user_id;
			$userInfo=$authorUserService->getUserInfo($whereArray);
			$this->view->assign('user',$user);
			$this->view->assign('userInfo',$userInfo);
			$this->view->assign('title',"我的面板");
			$this->view->set_tpl("user/index");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
			$this->view->assign('title',"读者登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function Cmoney(){//用户充值
		if($this->user_id){	
			//$sessonid = $this->sessionDo->get("sessonid");
			//$thisUrl=$this->wxRedis->get('uGo_'.$sessonid);
			$bookid = $this->controller->get_get('bookId');
			$cid = $this->controller->get_get('cid');
			$thisUrl = $this->controller->get_get('thisUrl');
			if(!empty($thisUrl)){
				$this->thisUrl=$thisUrl;
			}
			$scale=$this->configDo['money_coin_scale'];//兑换比例
			$giveConf=$this->configDo['give_conf'];
			$authorUserService = InitPHP::getService("authorUser");
			$authorUserService->userLookPayPage($this->user_id,$bookId,$cid);
			$booksService=InitPHP::getService("books");
			$bookInfo=$booksService->getBooks($bookid);
			$auAudio=$bookInfo['authorAudio'];
			$this->view->assign('auAudio', $auAudio);
			$this->view->assign('bookId',$bookid);
			$this->view->assign('cid',$cid);
			$this->view->assign('scale',$scale);
			$this->view->assign('giveConf',$giveConf);
			$this->view->assign('thisUrl',$this->thisUrl);
			$this->view->assign('keyWord',"充值中心");
			$this->view->assign('title',"充值中心");
			$this->view->set_tpl("user/Cmoney");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
			$this->view->assign('title',"读者登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function wxPay(){//用户充值
		if($this->user_id){
			$money=$this->controller->get_get('money');
			//$id=$this->controller->get_post('bookid');
			$id=$this->controller->get_cookie("bookid");
			$booksService=InitPHP::getService("books");
			$bookInfo=$booksService->getBooks($id);
			if($bookInfo['authorAudio']=='') {
			    $bookid='';
			}else{
			    $bookid=$id;
			}
			$total_fee=$money*100;
			$scale=$this->configDo['money_coin_scale'];//兑换比例
			$giveConf=$this->configDo['give_conf'];
			$whereArray['user_id']=$this->user_id;
			$subject ="No_".$this->user_id."_充值阅读币".($money+$money*$giveConf['give_scale_'.$money])*$scale."枚";//$_POST['WIDsubject'];
			$this->view->assign('subject',$subject);
			$this->view->assign('money',$money);
			$this->view->assign('title',"微信充值");
			$this->view->assign('bookid', $bookid);
			$wxConfig=$this->configDo['wxConfig'];
			$this->h5Pay->config($wxConfig);
			$data["body"]='爱上听书充值';
			$data["out_trade_no"]=$this->publicFunction->trade_no();//
			$data["total_fee"]=$total_fee;
			$data["device_info"]=$this->user_id;//设备号
			if($this->isWeiXin){
				if(isset($_GET['code']) && $_COOKIE["init_manyuedu_openid"]==''){
	               			$wxDatas=$this->weixinService->getOauthAccessToken($_GET['code']);
	           		}else{
	               			$CallUrl=$this->weixinService->getOauthRedirect('snsapi_base',$this->doMain."index.php?c=user&a=wxPay&money={$money}");
	               			header("location:".$CallUrl);
	               			die;
	           		}
				setcookie("init_manyuedu_openid",$wxDatas['openid'],time()+2592000,'/',$this->doMain);
				$_COOKIE["init_manyuedu_openid"] = $wxDatas['openid'];
				$data['trade_type']='JSAPI';
				$data['openid']=$wxDatas['openid'];
				$result=$this->h5Pay->unifiedorder($data);
				$jsApiObj["appId"] = $result['appid'];
				$timeStamp = time();
				$jsApiObj["timeStamp"] = "$timeStamp";
				$jsApiObj["nonceStr"] = $result['nonce_str'];
				$jsApiObj["package"] = "prepay_id=".$result['prepay_id'];
				$jsApiObj["signType"] = "MD5";
				$jsApiObj["sign"] =$this->h5Pay->MakeSign($jsApiObj);
				$this->view->assign('openid',$wxDatas['openid']);
				$this->view->assign('wxResult',$jsApiObj);
				$this->view->set_tpl("user/wxPayJs");
			}
		}else{
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function payList(){
		if($this->user_id){
			$year=$this->controller->get_get('year');
			$month=$this->controller->get_get('month');
			$year=empty($year)?date("Y",time()):$year;
			$month=empty($month)?date("m",time()):$month;
			$days = date('t', strtotime("{$year}-{$month}-1"));
			$startTime=strtotime("{$year}-{$month}-01 00:00:00");
			$endTime=strtotime("{$year}-{$month}-{$days} 23:59:59");
			$pageNum=25;
			$p=1;
			$where="user_id={$this->user_id} and create_time>={$startTime} and create_time<={$endTime}";
			$authorUserService = InitPHP::getService("authorUser");
			$RecordData=$authorUserService->getRecordAll($pageNum,$p,$where);
			$this->view->assign('year',$year);
			$this->view->assign('month',$month);
			$this->view->assign('RecordData',$RecordData);
			$this->view->assign('keyWord',"充值记录");
			$this->view->assign('title',"充值记录");
			$this->view->set_tpl("user/m_payList");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function buyList(){
		if($this->user_id){
			$year=$this->controller->get_get('year');
			$month=$this->controller->get_get('month');
			$year=empty($year)?date("Y",time()):$year;
			$month=empty($month)?date("m",time()):$month;
			$days = date('t', strtotime("{$year}-{$month}-1"));
			$startTime=strtotime("{$year}-{$month}-01 00:00:00");
			$endTime=strtotime("{$year}-{$month}-{$days} 23:59:59");
			$pageNum=25;
			$p=1;
			$where="user_id={$this->user_id} and create_time>={$startTime} and create_time<={$endTime}";
			$authorUserService = InitPHP::getService("authorUser");
			$booksService = InitPHP::getService("books");
			$OrderData=$authorUserService->getUserOrder($pageNum,$p,$where);
			foreach($OrderData['list'] as $k=>$v){
				$bookInfo=$booksService->getBooks($v['book_id']);
				$chapterInfo=$booksService->getOneChapter($v['chapter']);
				$OrderData['list'][$k]['book_name']=$bookInfo['book_name'];
				$OrderData['list'][$k]['chapter_name']="第{$chapterInfo['chapter']}章";
			}
			$this->view->assign('year',$year);
			$this->view->assign('month',$month);
			$this->view->assign('OrderData',$OrderData);
			$this->view->assign('keyWord',"消费记录");
			$this->view->assign('title',"消费记录");
			$this->view->set_tpl("user/m_buyList");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function zanList(){
		if($this->user_id){
			$booksService = InitPHP::getService("books");
			$list=$booksService->getGiveList($this->user_id);
			$this->view->assign('list',$list);
			$this->view->assign('keyWord',"赞过的书");
			$this->view->assign('title',"赞过的书");
			$this->view->set_tpl("user/m_zanList");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			//$this->wxRedis->set($sessonid.'_thisUrl',$this->thisUrl,300);
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function shelf(){
		if($this->user_id){
			$booksService = InitPHP::getService("books");
			$list=$booksService->getCaseList($this->user_id);
			$this->view->assign('list',$list);
			$this->view->assign('keyWord',"我的书架");
			$this->view->assign('title',"我的书架");
			$this->view->set_tpl("user/m_shelf");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function playerMessage()
	{
	       $money=$this->controller->get_get('money');
	       $id=$this->controller->get_get('bookid');
	       $total_fee=$money*100;
	       $scale=$this->configDo['money_coin_scale'];//兑换比例
	       $giveConf=$this->configDo['give_conf'];
	       $whereArray['user_id']=$this->user_id;
	       $subject ="No_".$this->user_id."_充值阅读币".($money+$money*$giveConf['give_scale_'.$money])*$scale."枚";//$_POST['WIDsubject'];
	       $this->view->assign('subject',$subject);
	       $this->view->assign('money',$money);
	       $this->view->assign('title',"微信充值");
	       $this->view->assign('bookid', $id);
	       $wxConfig=$this->configDo['wxConfig'];
	       $this->h5Pay->config($wxConfig);
	       $data["body"]='爱上听书充值';
	       $data["out_trade_no"]=$this->publicFunction->trade_no();//
	       $data["total_fee"]=$total_fee;
	       $data["device_info"]=$this->user_id;//设备号
	       if($this->isWeiXin){
	           if(isset($_GET['code']) && $_COOKIE["init_manyuedu_openid"]==''){
	               $wxDatas=$this->weixinService->getOauthAccessToken($_GET['code']);
	           }else{
	               $CallUrl=$this->weixinService->getOauthRedirect('snsapi_base',$this->doMain."index.php?c=user&a=playerMessage&bookid={$id}&money={$money}");
	               header("location:".$CallUrl);
	               die;
	           }
	           setcookie("init_manyuedu_openid",$wxDatas['openid'],time()+2592000,'/',$this->doMain);
	           $_COOKIE["init_manyuedu_openid"] = $wxDatas['openid'];
	           $data['trade_type']='JSAPI';
	           $data['openid']=$wxDatas['openid'];
	           $result=$this->h5Pay->unifiedorder($data);
	           $jsApiObj["appId"] = $result['appid'];
	           $timeStamp = time();
	           $jsApiObj["timeStamp"] = "$timeStamp";
	           $jsApiObj["nonceStr"] = $result['nonce_str'];
	           $jsApiObj["package"] = "prepay_id=".$result['prepay_id'];
	           $jsApiObj["signType"] = "MD5";
	           $jsApiObj["sign"] =$this->h5Pay->MakeSign($jsApiObj);
	           $this->view->assign('openid',$wxDatas['openid']);
	           $this->view->assign('wxResult',$jsApiObj);
	       }
            $bookId = $this->controller->get_get("bookid");
            $booksService = InitPHP::getService("books");
            $bookInfo = $booksService->getBooks($bookId);
            $playerSrc = $bookInfo['authorAudio'];
            $userIcon = "1506326023.jpeg";

	        $this->view->assign('playerSrc', $playerSrc);
	        $this->view->assign("bookid", $bookId);
	        $this->view->assign('userIcon', $userIcon);
	        $this->view->assign('title', '支付结果提示');
	        $this->view->set_tpl("user/player_return");
	    $this->view->display();
	}
	public function history(){
		if($this->user_id){
			$booksService = InitPHP::getService("books");
			$list=$booksService->getHistoryList($this->user_id);
			$this->view->assign('list',$list);
			$this->view->assign('keyWord',"最近阅读");
			$this->view->assign('title',"最近阅读");
			$this->view->set_tpl("user/m_history");
		}else{
			$sessonid=$this->sessionDo->get("sessonid");
			$this->view->assign('keyWord',"用户登录");
			$this->view->assign('title',"用户登录");
			$this->view->set_tpl("index/m_login");
		}
		$this->view->display();
	}
	public function unLogin(){//退出登录
		if($this->user_id){
			$session = $this->getUtil('session');
			$session->clear();
			echo '{"res":true,"msg":"成功退出！"}';
		}
	}
}
