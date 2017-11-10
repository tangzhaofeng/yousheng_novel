<?PHP
class doController extends BaseUserController{
	public $initphp_list = array('discuss','zan','house','userSafeDo','userEditDo','h5Paystate','str_check');
	public $doMain;
	public $configDo;
	public $sessionDo;
	public $wxRedis;
	public function __construct(){
		parent::__construct();
		$this->configDo = InitPHP::getConfig();
		$this->doMain=$config['url'];
		$this->sessionDo = $this->getUtil('session');
		//$this->wxRedis = $this->getLibrary('redis');
		//$config=$this->configDo['redis']['default'];
		//$this->wxRedis->init($config);
		$this->view->assign('doMain',$this->doMain);
	}
	public function cbook(){
		$booksService = InitPHP::getService("books");
		$bookList=$booksService->getBooksList(10000,'',1);
		foreach($bookList as $k =>$v){
			$dataClick['book_id']=$v['id'];
			$dataClick['click_count']=rand(1000,2000);
			$booksService->insertBooksClick($dataClick);
		}
	}
	public function zan(){
		if(parent::before()){
			$data['book_id']=$this->controller->get_post('book_id');
			$data['user_id']=parent::before();
			$booksService = InitPHP::getService("books");
			//echo $json='{"res":true,"msg":"点赞成功'.$data['book_id'].'"}';die;
			if($booksService->insertGive($data)){
				$json='{"res":true,"msg":"点赞成功"}';
			}else{
				$json='{"res":false,"msg":"点赞失败！"}';
			}
			
		}else{
			$json='{"res":false,"msg":"请先登录！"}';
		}
		echo $json;
	}
	public function house(){
		if(parent::before()){
			$data['book_id']=$this->controller->get_post('book_id');
			$data['user_id']=parent::before();
			$booksService = InitPHP::getService("books");
			//echo $json='{"res":true,"msg":"点赞成功'.$data['book_id'].'"}';die;
			if($booksService->insertCase($data)){
				$json='{"res":true,"msg":"收藏成功"}';
			}else{
				$json='{"res":false,"msg":"收藏失败！"}';
			}
			
		}else{
			$json='{"res":false,"msg":"请先登录！"}';
		}
		echo $json;
	}
	public function uGo(){
		if(parent::before()){
			$uGo_Url=$this->controller->get_post('toUrl');
			if(!empty($uGo_Url)){
				$sessonid = $this->sessionDo->get("sessonid");
				$this->wxRedis->set('uGo_'.$sessonid,$uGo_Url,3600);
			}
			$data['book_id']=$this->controller->get_post('bookId');
			$data['chapter_id']=$this->controller->get_post('chapterId');
			$data['type']=$this->controller->get_post('type');
			$data['user_id']=parent::before();
			$typeArray=array(1,2,3,4,5,6);
			if(!in_array($data['type'],$typeArray)){
				echo '{"res":false,"msg":"打赏失败！","code":"A002"}';
				die;
			}
			if($data['type']=="1"){
				$data['price']=100;
				$Ctext="送给作者一个红包：这是一个正儿八经的催更红包！";
			}
			if($data['type']=="2"){
				$data['price']=500;
				$Ctext="送给作者一杯咖啡：提神又醒脑，日更八千不嫌少！";
			}
			if($data['type']=="3"){
				$data['price']=1000;
				$Ctext="送给作者一只手表：提醒作者距下次更新还有60秒！";
			}
			if($data['type']=="4"){
				$data['price']=10000;
				$Ctext="送给作者一颗钻石：剌开作者不重视催更的玻璃心！";
			}
			if($data['type']=="5"){
				$data['price']=50000;
				$Ctext="送给作者一辆跑车：希望作者码字时速飙升一万二！";
			}
			if($data['type']=="6"){
				$data['price']=100000;
				$Ctext="送给作者一栋别墅：舒适又美好，更新动力少不了！";
			}
			$data['create_time']=time();
			$authorUserService = InitPHP::getService("authorUser");
			$userInfo=$authorUserService->getMoney($data['user_id']);
			if($userInfo['moneyCoin']<$data['price']){
				$json='{"res":false,"msg":"打赏失败,金额不足","code":"A003"}';
			}else{
				if($authorUserService->userGratuityOrder($data,$userInfo['moneyCoin'])){
					$info['c_text']='<font color="#FF0000">'.$Ctext.'</font>';
					$info['book_id']=$data['book_id'];
					$info['reply_user_id']=0;
					$info['discuss_id']=0;
					$info['user_id']=parent::before();
					$info['create_time']=time();
					$discussService = InitPHP::getService("discuss");
					$discussService->insert($info);
					$json='{"res":true,"msg":"打赏成功","code":"A001"}';
				}else{
					$json='{"res":false,"msg":"打赏失败！","code":"A002"}';
				}
			}
			
		}else{
			$json='{"res":false,"msg":"请先登录！","code":"A004"}';
		}
		echo $json;
	}
	public function str_check($str){ 
		if(!get_magic_quotes_gpc()){ 
			$str = addslashes($str); // 进行过滤 
		} 
		$str = str_replace("_", "\_", $str); 
		$str = str_replace("%", "\%", $str);
		return $str; 
	} 
	public function userSafeDo(){
		$userId=parent::before();
		if($userId){
			$data['one']=$this->controller->get_post('one');
			$data['two']=$this->controller->get_post('two');
			$data['tree']=$this->controller->get_post('tree');
			$userSafeService = InitPHP::getService("userSafe");
			if($userSafeService->insert($data,$userId)){
				$json='{"res":true,"code":1,"msg":"密保设置成功"}';
			}else{
				$json='{"res":false,"code":2,"msg":"密保设置失败！"}';
			}
			
		}else{
			$json='{"res":false,"code":0,"msg":"请先登录！"}';
		}
		echo $json;
	}
	public function userEditDo(){
		$userId=parent::before();
		if($userId){
			if($this->controller->is_post()){
				$userPicb64=$this->controller->get_post('userPicb64');
				$data['name']=$this->controller->get_post('name');
				$data['sex']=$this->controller->get_post('sex');
				if(preg_match('/^(data:\s*image\/(\w+);base64,)/',$userPicb64)){ 
					$path="./upFile/userIco/";
					$image= $this->getLibrary('image');
					$data['headPic']=$image->base64ToPic($path,$userPicb64,$userId);
				}
				$authorUserService = InitPHP::getService("authorUser");
				if($authorUserService->upUserInfo($userId,$data)){
					$json='{"res":true,"code":1,"msg":"设置成功"}';
				}else{
					$json='{"res":false,"code":2,"msg":"设置失败！"}';
				}
			}
		}else{
			$json='{"res":false,"code":0,"msg":"请先登录！"}';
		}
		echo $json;
	}
	public function h5Paystate(){
		$config = InitPHP::getConfig();
		$wxConfig=$config['wxConfig'];
		$h5Pay=$this->getLibrary('h5Pay');
		$h5Pay->config($wxConfig);
		$out_trade_no=$this->controller->get_post('tradeNo');
		$result=$h5Pay->orderquery($out_trade_no);
		echo $result['trade_state'];
	}
}