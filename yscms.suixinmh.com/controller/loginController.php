<?PHP
class loginController extends BaseAdminController{
	public $initphp_list = array('getcode','loginDo','http_get');
	public $doMain;
	public $configDo;
	public $sessionDo;
	public $admin_id;
	public function __construct(){
		parent::__construct();
		$this->admin_id=parent::before();
		$this->configDo = InitPHP::getConfig();
		$this->sessionDo = $this->getUtil('session');
		$this->doMain=$this->configDo['url'];
	} 
	public function index(){
		if($this->admin_id){
			header("Location:".$this->doMain."index.php?c=index&a=main");
		}else{
			$this->view->set_tpl("login/index");//设置模板
			$this->view->display();
		}
	}
	public function loginDo(){
		$data['userName']=$this->controller->get_post('userName');
		$data['password']=$this->controller->get_post('password');
		$code=$this->controller->get_post('code');
		$codeObj = $this->getLibrary('code');
		if(!$codeObj->checkCode($code,'login')){
			$json='{"res":false,"msg":"验证码失败"}';
		}else{
			$data['ip']=$this->getIPaddress();
			$authorAdminService = InitPHP::getService("authorAdmin");
			$adminInfo=$authorAdminService->login($data);
			if(!empty($adminInfo)){
				$this->sessionDo->set("admin_id",$adminInfo['id']);
				$this->sessionDo->set("userName",$adminInfo['userName']);
				$this->sessionDo->set("login_ip",$adminInfo['ip']);
				$json='{"res":true,"msg":"登录成功"}';
			}else{
				$json='{"res":false,"msg":"登录失败"}';
			}
		}
		echo $json;
	}
	public function getcode(){
		$code = $this->getLibrary('code');
		$code->getcode('login');
	}
	public function getIPaddress(){
		$IPaddress='';
		if (isset($_SERVER)){
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){
				$IPaddress = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} else if (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$IPaddress = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$IPaddress = $_SERVER["REMOTE_ADDR"];
			}
		} else {
			if (getenv("HTTP_X_FORWARDED_FOR")){
				$IPaddress = getenv("HTTP_X_FORWARDED_FOR");
			}else if(getenv("HTTP_CLIENT_IP")){
				$IPaddress = getenv("HTTP_CLIENT_IP");
			}else{
				$IPaddress = getenv("REMOTE_ADDR");
			}
		}
		return $IPaddress;
	}
	private function http_get($url){
		$oCurl = curl_init();
		if(stripos($url,"https://")!==FALSE){
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
		}
		curl_setopt($oCurl, CURLOPT_URL, $url);
		curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
		$sContent = curl_exec($oCurl);
		$aStatus = curl_getinfo($oCurl);
		curl_close($oCurl);
		if(intval($aStatus["http_code"])==200){
			return $sContent;
		}else{
			return false;
		}
	}
}