<?PHP
class weixinController extends Controller{
	public $initphp_list = array('weixin');
	public $publicFunction;
	public $doMain;
	public $sessionDo;
	public $configDo;
	public $weixinService;
	public $wxDatas;
	public $wxRedis;
	public function __construct(){
		parent::__construct();
		$this->configDo = InitPHP::getConfig();
		$this->publicFunction = $this->getLibrary('function');
		$this->wxRedis = $this->getLibrary('redis');
		$this->doMain=$this->configDo['url'];
		$this->sessionDo = $this->getUtil('session');
		$this->weixinService = InitPHP::getService("weixinLogin");
		$config=$this->configDo['redis']['default'];
		$this->wxRedis->init($config);
	} 
	public function index(){
		$gc=$this->controller->get_get('gc');
		$ga=$this->controller->get_get('ga');
		if(isset($_GET['code'])){
			
			$this->wxDatas=$this->weixinService->getOauthAccessToken($_GET['code']);
			
		}else{
			$CallUrl=$this->weixinService->getOauthRedirect('snsapi_userinfo',$this->doMain."index.php?c=weixin&a=index&gc={$gc}&ga={$ga}");
			header("location:".$CallUrl);die;
		}
		$sessonid=$this->sessionDo->get("sessonid");
		$this->wxRedis->set($sessonid.'_openid',$this->wxDatas['openid']);
		print_r($this->wxDatas);
		echo $this->wxRedis->get($sessonid.'_openid');
		die;
		if(!empty($gc) && !empty($ga)){
			$url=$this->doMain."index.php?c={$gc}&a={$ga}";
			header("refresh:2;url=$url");
			//header("location:".$url);
		}
	}
	public function weixin(){
		echo $sessonid=$this->sessionDo->get("sessonid");
		//echo $this->wxRedis->get($sessonid.'_openid');
		echo $toUrl=$this->wxRedis->get($sessonid.'_thisUrl');
	}
}