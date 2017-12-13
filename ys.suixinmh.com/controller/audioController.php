<?PHP

class audioController extends BaseUserController
{
    public $initphp_list = array('one');
    public $publicFunction;
    public $sessionDo;
    public $configDo;
    public $doMain;
    public function __construct()
    {
        parent::__construct();
        $this->configDo = InitPHP::getConfig();
        $this->publicFunction = $this->getLibrary('function');
        $this->sessionDo = $this->getUtil('session');
        $this->doMain = $this->configDo['url'];
        $this->authorMain = $this->configDo['author_url'];
        $this->view->assign('doMain', $this->doMain);

    }
    public function index(){
    }
    //正常播放模块
	public function one()
    {
		$user_id=parent::before();
		$id = $this->controller->get_get('id');
		$song = $this->controller->get_get('song');
		$p = $this->controller->get_get('p');
		$dc = $this->controller->get_get('dc');
		$booksService = InitPHP::getService("books");
		$info=$booksService->getOneChapter($id);
		$url="&song={$song}&p={$p}&dc={$dc}";
		$booksService->insertHistory(array("book_id"=>$info['book_id'],"chapter_id"=>$info['id'],"user_id"=>$user_id));
		if(!empty($info)){
			if($info['is_vip']==1 && empty($user_id)){
				echo '{"res":false,"code":"001","msg":"需要登录","url":"'.$url.'"}';
			}else{
				$authorUserService = InitPHP::getService("authorUser");
				$whereArray['user_id']=$user_id;
				$userInfo=$authorUserService->getUserInfo($whereArray);
				$orderData['book_id']=$info['book_id'];
				$orderData['chapter']=$info['id'];
				$orderData['user_id']=$user_id;

				$userOrder=$authorUserService->getUserOrderOne($orderData);//是否消费过
				if(empty($userOrder) && $info['price']>0){
					if($info['price']>$userInfo['moneyCoin']){
						echo '{"res":false,"code":"002","msg":"余额不足","url":"'.$url.'","book_id":'.$info['book_id'].',"cid":'.$id.'}';
					}else{
						//开始扣费
						//消费记录参数
						$orderData['price']=$info['price'];
						$orderId=$authorUserService->userPayOrder($orderData,$userInfo['moneyCoin']);
						if(!empty($orderId)){
							echo '{"res":true,code:"000","msg":"准备播放","url":"'.$this->authorMain.'upFile/'.$info['audio_url'].'"}';
						}
					}
				}else{
                    $source = InitPHP::getService("source");
                    $where['book_id']=$info['book_id'];
                    $where['chapter_id']=$info['sort'];
                    $follow = $source->getConcern($where);
                    if (!empty($follow) and $follow['disable']='1' and $userInfo['subscribe'] != '1'){
                        echo '{"res":true,code:"003","msg":"强制跳转","url":"'.$url.'","book_id":'.$info['book_id'].',"cid":'.$info['sort'].'}';
                    }else{
					   echo '{"res":true,code:"000","msg":"准备播放","url":"'.$this->authorMain.'upFile/'.$info['audio_url'].'"}';
                    }
				}
			}
		}
	}
	//用于强制关注模块
	public function two()
	{
	    $user_id=parent::before();
	    $id = $this->controller->get_get('id');
	    $song = $this->controller->get_get('song');
	    $p = $this->controller->get_get('p');
	    $dc = $this->controller->get_get('dc');
	    $booksService = InitPHP::getService("books");
	    $info=$booksService->getOneChapter($id);
	    $url="&song={$song}&p={$p}&dc={$dc}";
	    $booksService->insertHistory(array("book_id"=>$info['book_id'],"chapter_id"=>$info['id'],"user_id"=>$user_id));
	    if(!empty($info)){
	        if($info['is_vip']==1 && empty($user_id)){
	            echo '{"res":false,"code":"001","msg":"需要登录","url":"'.$url.'"}';
	        }else{
	            $authorUserService = InitPHP::getService("authorUser");
	            $whereArray['user_id']=$user_id;
	            $userInfo=$authorUserService->getUserInfo($whereArray);
	            $orderData['book_id']=$info['book_id'];
	            $orderData['chapter']=$info['id'];
	            $orderData['user_id']=$user_id;

	            $userOrder=$authorUserService->getUserOrderOne($orderData);//是否消费过
	            if(empty($userOrder) && $info['price']>0){
	                if($info['price']>$userInfo['moneyCoin']){
	                    echo '{"res":false,"code":"002","msg":"余额不足","url":"'.$url.'","book_id":'.$follow['book_id'].',"cid":'.$id.'}';
	                }else{
	                    //开始扣费
	                    //消费记录参数
	                    $orderData['price']=$info['price'];
	                    $orderId=$authorUserService->userPayOrder($orderData,$userInfo['moneyCoin']);
	                    if(!empty($orderId)){
	                        echo '{"res":true,code:"000","msg":"准备播放","url":"'.$this->authorMain.'upFile/'.$info['audio_url'].'"}';
	                    }
	                }
	            }else{
	                echo '{"res":true,code:"000","msg":"准备播放","url":"'.$this->authorMain.'upFile/'.$info['audio_url'].'"}';
	            }
	        }
	    }
	}
}
