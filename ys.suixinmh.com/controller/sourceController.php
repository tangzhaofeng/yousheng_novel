<?PHP
class sourceController extends Controller{
	public $initphp_list = array('getIPaddress','follow');
	public $publicFunction;
	public $sessionDo;
	public $cookieDo;
	public $doMain;
	public $doRedis;
	public $configDo;
	public $user_id;
	public function __construct(){
		parent::__construct();
		$this->configDo = InitPHP::getConfig();
		$this->publicFunction = $this->getLibrary('function');
		$this->sessionDo = $this->getUtil('session');
		$this->cookieDo = $this->getUtil('cookie');
		$this->doMain=$this->configDo['url'];
		$this->thisUrl = $this->publicFunction->get_url();
		$this->user_id=$this->sessionDo->get("user_id");
	}
	public function index(){
		$id=$this->controller->get_get('id');
		$sourceService = InitPHP::getService("source");
		$info=$sourceService->getSourceUrl($id);
		$coninfo=$sourceService->getConcern($id);

		if(empty($info)){
			$url=$this->doMain;
			$this->controller->redirect($url,0);
		}
		$this->sessionDo->set('third_source',$info['sourceKey']);
		$this->cookieDo->set("third_source",$info['sourceKey'],3600);
		if ($this->publicFunction->isWeiXin()) {
            if (!$this->user_id) {
				$this->sessionDo->set('__returnUrl', $this->thisUrl);
                header('location:' . $this->doMain . 'index.php?c=login&a=weixin&toUrl='.$this->thisUrl);
                die;
            }
        }
		$pid=0;
		$source=$info['sourceKey'];
		$book_id=$info['book_id'];
		$chapter_id=$info['chapter'];
		if($this->publicFunction->isMobile()){
			$data['user_terminal']=1;//移动端
		}else{
			$data['user_terminal']=2;//PC端
		}
		$data['source']='"'.$source.'"';//1,腾讯，2微博，3，微信
		$data['pid']=$pid;
		$data['book_id']=$book_id;
		$data['chapter_id']=$chapter_id;
		$data['ip']=$this->getIPaddress();
		$data['count']=1;
		$data['week']=date("W");
		$data['day']=date("j");
		$data['month']=date("n");
		$data['year']=date("Y");
		$only_key=$data['pid'].$data['book_id'].$data['chapter_id'].$data['ip'].$data['week'].$data['day'].$data['month'].$data['year'];
		$data['only_key']=md5($only_key);
		$sourceService->insert($data);
		if($book_id>0 && $chapter_id>0){
			$url=$this->doMain."index.php?c=index&a=chapterShow&bookid=".$book_id."&cid=".$chapter_id."&play=1&time=".$info['start_time'];
		}
		if($book_id>0 && empty($chapter_id)){
			$url=$this->doMain."index.php?c=index&a=chapterShow&bookId=".$book_id;
		}
		$this->controller->redirect($url,0);
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
}