<?PHP

class loginController extends Controller
{
    public $initphp_list = array('weixin','apiWx','getCode', 'regDo', 'loginDo','loginDoself','forgotDO', 'qq');
    public $publicFunction;
    public $doMain;
    public $sessionDo;
    public $cookieDo;
    public $configDo;
    public $weixinService;
    public $wxService;
    public $wxRedis;

    public function __construct()
    {
        parent::__construct();
        $this->configDo = InitPHP::getConfig();
        $this->publicFunction = $this->getLibrary('function');
        $this->doMain = $this->configDo['url'];
        $this->staticsMain = $this->configDo['statics_url'];
        $this->sessionDo = $this->getUtil('session');
        $this->cookieDo = $this->getUtil('cookie');
        $this->weixinService = InitPHP::getService("weixinLogin");
        $this->wxService = InitPHP::getService("wxLogin");
        $this->isWeiXin = false;
        if ($this->publicFunction->isWeiXin()) {
            $this->isWeiXin = true;
        }
        $this->view->assign('isWeiXin', $this->isWeiXin);
        $this->view->assign('staticsMain', $this->staticsMain);
    }

    public function index()
    {
        $toUrl = $_GET['toUrl'];
        if (!empty($toUrl)) {
            $thisUrl = $toUrl;
        }
        $this->view->assign('title', "读者登录");
        $this->view->assign('thisUrl', $thisUrl);
        $this->view->set_tpl("index/login");//设置模板
        $this->view->display();
    }

    public function weixin()
    {
		$toUrl = $_GET['toUrl'];
        if (isset($_GET['code'])) {
            $datas = $this->weixinService->getOauthAccessToken($_GET['code']);
            $userInfoData = $this->weixinService->getOauthUserinfo($datas['access_token'], $datas['openid']);
        } else {
            $CallUrl = $this->weixinService->getOauthRedirect();
            header("location:" . $CallUrl);
            die;
        }
		$toUrl =empty($toUrl)?$this->sessionDo->get('__returnUrl'):$toUrl;
        if (!empty($toUrl)) {
			$this->sessionDo->set('__returnUrl', '');
        }else{
            $toUrl = $this->doMain . "index.php?c=user&a=index";
        }
        if (empty($userInfoData['openid'])) {
            header("location:".$this->doMain . "index.php?c=login&a=index&toUrl=".$toUrl);
            die;
        }
        $authorUserService = InitPHP::getService("authorUser");
        $data['openid'] = $userInfoData['openid'];
		$data['source'] = 'weixin';
        if (empty($data['openid'])) {
            header("location:" . $this->doMain . "index.php?c=login&a=index");
            die;
        }
        $userInfo = $authorUserService->getOneByField($data,$userInfoData['unionid']);
        if (!empty($userInfo)) {
            $this->sessionDo->set("user_id", $userInfo['id']);
            $this->sessionDo->set("state", $userInfo['state']);
            header("location:" . $toUrl);
            die;
        } else {
			$data['unionid'] = $userInfoData['unionid'];
            $data['nickname'] = $userInfoData['nickname'];
            $data['sex'] = $userInfoData['sex'] == '' ? 1 : $userInfoData['sex'];
            $userPicb64 = $this->publicFunction->img2Base64($userInfoData['headimgurl']);
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $userPicb64)) {
                $path = "./upFile/userIco/";
                $image = $this->getLibrary('image');
                $data['headPic'] = $image->base64ToPic($path, $userPicb64, time());
            }
            $thirdSourceA = $this->sessionDo->get("third_source");
            $thirdSourceB = $this->cookieDo->get("third_source");
            $data['third_source'] = empty($thirdSourceA) ? $thirdSourceB : $thirdSourceA;
            $data['moneyCoin'] = $this->configDo['reg_user_money'];
            $data['create_time'] = time();
            $user_id = $authorUserService->insert($data);
            if ($user_id) {
                $this->sessionDo->del("third_source");
                $this->sessionDo->set("user_id", $user_id);
                $this->sessionDo->set("state", 1);
                header("location:" . $toUrl);
            }
        }
    }
	public function apiWx(){
		$qcode=$_GET['qcode'];//qcode传递过来的PC端sessonid
		$sessonid = $this->sessionDo->get("sessonid");
		if(!empty($qcode)){
			$this->weixinService->callback($this->doMain.'index.php?c=login&a=apiWx');
			$this->wxRedis->set($sessonid.'pc_wx',$qcode,500);//保存PC的唯一标识
		}
        if (isset($_GET['code'])) {
			$SessionId=$this->wxRedis->get($sessonid.'pc_wx');
			$this->sessionDo->getSessionId($SessionId);
            $datas = $this->weixinService->getOauthAccessToken($_GET['code']);
            $userInfoData = $this->weixinService->getOauthUserinfo($datas['access_token'], $datas['openid']);
		} else {
            $CallUrl = $this->weixinService->getOauthRedirect();
            header("location:" . $CallUrl);
            die;
        }
        $authorUserService = InitPHP::getService("authorUser");
        $data['openid'] = $userInfoData['openid'];
		$data['source'] = 'weixin';
        if (empty($data['openid']) || empty($SessionId)) {
			$this->view->assign('loginTxt',"登录失败，刷新网站返回重试");
			$this->view->assign('loginSTA',0);
			$this->view->assign('title', "PC扫码登录");
			$this->view->set_tpl("index/apiWx");//设置模板
			$this->view->display();
            die;
        }
        $userInfo = $authorUserService->getOneByField($data,$userInfoData['unionid']);
        if (!empty($userInfo)) {
            $this->sessionDo->set("user_id", $userInfo['id']);
			$this->sessionDo->set("author_id",$userInfo['id']);
			$this->sessionDo->set("author_state", 0);
            $this->sessionDo->set("state", $userInfo['state']);
			$this->view->assign('loginTxt',"登录成功");
        } else {
			$data['unionid'] = $userInfoData['unionid'];
            $data['nickname'] = $userInfoData['nickname'];
            $data['sex'] = $userInfoData['sex'] == '' ? 1 : $userInfoData['sex'];
            $userPicb64 = $this->publicFunction->img2Base64($userInfoData['headimgurl']);
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $userPicb64)) {
                $path = "./upFile/userIco/";
                $image = $this->getLibrary('image');
                $data['headPic'] = $image->base64ToPic($path, $userPicb64, time());
            }
            $data['third_source'] ='';
            $data['moneyCoin'] = 0;
            $data['create_time'] = time();
            $user_id = $authorUserService->insert($data);
            if ($user_id) {
                $this->sessionDo->del("third_source");
                $this->sessionDo->set("user_id", $user_id);
				$this->sessionDo->set("author_id",$user_id);
				$this->sessionDo->set("author_state", 0);
                $this->sessionDo->set("state", 1);
				$this->view->assign('loginTxt',"登录成功");
            }
        }
		$this->view->assign('loginSTA',1);
		$this->view->assign('title', "PC扫码登录");
        $this->view->set_tpl("index/apiWx");//设置模板
        $this->view->display();
    }
    public function qq(){
        $toUrl = $this->controller->get_get('toUrl');
        $this->cookieDo->set("toUrl", $toUrl, 60);
        $this->sessionDo->set("toUrl", $toUrl);
		$sessonid=$this->sessionDo->get("sessonid");
		$this->wxRedis->set($sessonid.'_thisUrl',$toUrl,300);
        $qqObj = $this->getLibrary('qq');
        $qqObj->login('get_user_info');
    }

    public function getCode()
    {
        if ($this->publicFunction->isAjax() && $this->controller->is_post()) {//是否ajax提交
            $to = $this->controller->get_post('user');
            $check = $this->controller->get_post('check');
            $authorUserService = InitPHP::getService("authorUser");
            $data['phone'] = $to;
            if ($check) {
                $userInfo = $authorUserService->getOneByField($data);
            } else {
                $userInfo = 0;
            }
            if (!empty($userInfo)) {
                echo '{"res":false,"msg":"该手机已注册！"}';
                die;
            } else {
                $vcode = $this->get_random_val();
                $authorUserService = InitPHP::getService("authorUser");
                if ($authorUserService->sendsms($to, $vcode, 'SMS_35030004')) {
                    echo '{"res":true,"msg":"验证码发送成功，请查看短信！"}';
                } else {
                    echo '{"res":false,"msg":"验证码发送失败"}';
                }
            }
        }
    }

    public function regDo()
    {
        $user = $this->controller->get_post('user');
        $code = $this->controller->get_post('code');
        $password = $this->controller->get_post('password');
        if (!$this->checkCode($code)) {
            $json = '{"res":false,"msg":"认证码错误！"}';
        } else {
            $authorUserService = InitPHP::getService("authorUser");
            if (preg_match("/^13[0-9]{9}$|17[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/", $user)) {
                $data['phone'] = $user;
                $userInfo = $authorUserService->getOneByField($data);
                if (!empty($userInfo)) {
                    echo '{"res":false,"msg":"注册的手机号已处在"}';
                    die;
                }
            }
            $thirdSourceA = $this->sessionDo->get("third_source");
            $thirdSourceB = $this->cookieDo->get("third_source");
            $data['third_source'] = empty($thirdSourceA) ? $thirdSourceB : $thirdSourceA;
            $data['moneyCoin'] = $this->configDo['reg_user_money'];
            $data['password'] = md5($password);
            $data['create_time'] = time();
            $user_id = $authorUserService->insert($data);
            if ($user_id) {
                $this->sessionDo->del("third_source");
                $this->sessionDo->set("user_id", $user_id);
                $this->sessionDo->set("state", 1);
                $this->sessionDo->set("author_id", $user_id);
                $this->sessionDo->set("author_state", 0);
                $json = '{"res":true,"msg":"注册成功"}';
            } else {
                $json = '{"res":false,"msg":"注册失败！"}';
            }
        }
        echo $json;
    }

    public function forgotDO()
    {
        $user = $this->controller->get_post('user');
        $code = $this->controller->get_post('code');
        $password = $this->controller->get_post('password');
        if (!$this->checkCode($code)) {
            $json = '{"res":false,"msg":"认证码错误！"}';
        } else {
            $authorUserService = InitPHP::getService("authorUser");
            if (preg_match("/^13[0-9]{9}$|17[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/", $user)) {
                $phone =& $user;
            }
            $data['password'] = md5($password);
            $data['create_time'] = time();
            if ($authorUserService->upPassword($phone, $data)) {
                $json = '{"res":true,"msg":"找回密码成功！"}';
            } else {
                $json = '{"res":false,"msg":"找回密码失败！"}';
            }
        }
        echo $json;
    }

    public function loginDo()
    {
        $user = $this->controller->get_post('user');
        $keep = $this->controller->get_post('keep');
        $password = $this->controller->get_post('password');
        if (preg_match("/^13[0-9]{9}$|17[0-9]{9}|15[0-9]{9}$|18[0-9]{9}$/", $user)) {
            $data['phone'] = $user;
        }
        $data['password'] = md5($password);
        $authorUserService = InitPHP::getService("authorUser");
        $userInfo = $authorUserService->getOneByField($data);
        if (empty($userInfo)) {
            echo '{"res":false,"msg":"账号或秘密错误！"}';
        } else {
            $this->sessionDo->set("user_id", $userInfo['id']);
            $this->sessionDo->set("state", $userInfo['state']);
            $this->sessionDo->set("author_id", $userInfo['id']);
            $this->sessionDo->set("author_state", 0);
            echo '{"res":true,"msg":"登录成功！"}';
        }
    }
   /* public function loginDoself()
    {
			$data['phone']= $this->controller->get_get('phone');
            $thirdSourceA = $this->sessionDo->get("third_source");
            $thirdSourceB = $this->cookieDo->get("third_source");
            $data['third_source'] = empty($thirdSourceA) ? $thirdSourceB : $thirdSourceA;
            $data['moneyCoin'] =0;
            $data['password'] = '9e7c5153caa63eb3bea4d6283f5dbdfa';
            $data['create_time'] = time();
			$authorUserService = InitPHP::getService("authorUser");
            $user_id = $authorUserService->insert($data);
            if ($user_id) {
                $this->sessionDo->del("third_source");
                $this->sessionDo->set("user_id", $user_id);
                $this->sessionDo->set("state", 1);
                $this->sessionDo->set("author_id", $user_id);
                $this->sessionDo->set("author_state", 0);
                $json = '{"res":true,"msg":"注册成功"}';
            } else {
                $json = '{"res":false,"msg":"注册失败！"}';
            }
        echo $json;
    }*/

    public function get_random_val()
    {
        srand((double)microtime() * 1000000);
        while (($authnum = rand() % 100000) < 10000) ;
        $this->sessionDo->set("v_code", $authnum);
        return $authnum;
    }

    public function checkCode($code)
    {
        $v_code = $this->sessionDo->get("v_code");
        if ($v_code == $code) return true;
        return false;
    }
}