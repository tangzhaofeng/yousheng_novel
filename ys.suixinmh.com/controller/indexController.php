<?PHP

class indexController extends BaseUserController
{
    public $initphp_list = array('type','soSuo','bookInfo', 'bookList', 'chapterList','chapterShow','rank','pushBook', 'bookcase');
    public $publicFunction;
    public $sessionDo;
    public $cookieDo;
    public $configDo;
    public $doMain;
    public $authorMain;
    public $bookPicMain;
    public $thisUrl;
    public $isWeiXin;
    public $wxRedis;
    public $erweimaMain;

    public function __construct()
    {
        parent::__construct();
        $this->configDo = InitPHP::getConfig();
        $this->publicFunction = $this->getLibrary('function');
        $this->sessionDo = $this->getUtil('session');
        $this->cookieDo = $this->getUtil('cookie');
        $this->doMain = $this->configDo['url'];
        $this->staticsMain = $this->configDo['statics_url'];
        $this->authorMain = $this->configDo['author_url'];
        $this->bookPicMain = $this->authorMain . 'Uploads/books/';
        $this->erweimaMain = $this->authorMain . 'Uploads/erweima/';
        //$this->wxRedis = $this->getLibrary('redis');
        $config = $this->configDo['redis']['default'];
        //$this->wxRedis->init($config);
        $this->thisUrl = $this->publicFunction->get_url();
        $this->isWeiXin = false;
        if ($this->publicFunction->isWeiXin()) {
            if (!parent::before()) {
                $sessonid = $this->sessionDo->get("sessonid");
                //$this->wxRedis->set($sessonid . '_thisUrl', $this->thisUrl, 300);
                $this->sessionDo->set('__returnUrl', $this->thisUrl);
                //echo $this->doMain.'index.php?c=login&a=weixin';die;
                header('location:' . $this->doMain . 'index.php?c=login&a=weixin');
                die;
            } else {
                $redirectUrl = $this->sessionDo->get('__returnUrl');
                if ($redirectUrl) {
                    $this->sessionDo->set('__returnUrl', '');
                    header('location:' . $redirectUrl);
                }
            }
            $this->isWeiXin = true;
        }
        $this->view->assign('isWeiXin', $this->isWeiXin);
        $this->view->assign('doMain', $this->doMain);
        $this->view->assign('staticsMain', $this->staticsMain);
        $this->view->assign('authorMain', $this->authorMain);
        $this->view->assign('bookPicMain', $this->bookPicMain);
        $this->view->assign("erweimaMain", $this->erweimaMain);
        $this->view->assign('thisUrl', $this->thisUrl);

    }
    public function index(){
        $booksService = InitPHP::getService("books");
		$bookUpList=$booksService->getUpBook(1,6);
		$jxtjList=$booksService->getPushBook("jxtj",6,1);
		$vipList=$booksService->getPushBook("vip",6,1);
		$bookNewList=$booksService->getPushBook("news",3,1);
        $this->view->assign('bookUpList', $bookUpList);
        $this->view->assign('jxtjList', $jxtjList);
        $this->view->assign('vipList', $vipList);
        $this->view->assign('bookNewList', $bookNewList);
        $this->view->assign('title', "爱上听书");
        $this->view->set_tpl("index/m_index");//设置模板
        $this->view->display();
    }
	public function type()
    {
		$booksService = InitPHP::getService("books");
		$bookTypeService = InitPHP::getService("bookType");
		$bookType=$bookTypeService->getAll();
		foreach($bookType as $k=>$v){
			foreach($v['minType'] as $mk=>$mv){
				$bookType[$k]['minType'][$mk]['bookCount']=$booksService->getTypeBookCount($mv['id']);
			}
		}
        $this->view->assign('bookType',$bookType);
        $this->view->assign('keyWord','作品分类');
        $this->view->assign('title', "作品分类");
		$this->view->set_tpl("index/m_type");//设置模板
        $this->view->display();
	}
	public function soSuo(){
		$bookTypeService = InitPHP::getService("bookType");
		$bookType=$bookTypeService->getAll();
		$booksService = InitPHP::getService("books");
		$infoList=$booksService->getPushBook("info",3,1);
        $this->view->assign('bookType',$bookType);
        $this->view->assign('infoList',$infoList);
        $this->view->assign('keyWord','作品搜索');
        $this->view->assign('title', "作品搜索");
		$this->view->set_tpl("index/m_soSuo");//设置模板
        $this->view->display();
	}
    public function bookInfo()
	{
		$id = $this->controller->get_get('id');
		$cid = $this->controller->get_get('cid');
		$type = $this->controller->get_get('type');
		$booksService = InitPHP::getService("books");
		if(parent::before() && !empty($cid)){
			$authorUserService = InitPHP::getService("authorUser");
			$whereArray['user_id']=parent::before();
			$userInfo=$authorUserService->getUserInfo($whereArray);
			$info=$booksService->getOneChapter($id);
			if($info['price']>$userInfo['moneyCoin']){
				$scale=$this->configDo['money_coin_scale'];//兑换比例
				$giveConf=$this->configDo['give_conf'];
				$this->view->assign('scale',$scale);
				$this->view->assign('giveConf',$giveConf);
				$this->view->assign('keyWord',"充值中心");
				$this->view->assign('title',"充值中心");
				$this->view->set_tpl("user/Cmoney");
				$this->view->display();
			}
			die;
		}
		$bookInfo=$booksService->getBooks($id);
		if($type=='desc'){
			$orderType='desc';
		}else{
			$orderType='asc';
		}
		$chapterList=$booksService->getChapter($id,$orderType,6,1);
		foreach($chapterList as $k=>$v){
			$chapter[0][$k]['name']="第{$v['chapter']}章 {$v['title']}";
			$chapter[0][$k]['singer']='';//empty($v['is_vip'])?' ':"VIP";
			$chapter[0][$k]['song']=$k;
			$chapter[0][$k]['src']=$this->doMain."index.php?c=audio&a=one&id={$v['id']}";
			$chapter[0][$k]['time']='00:00';
		}
		$booksService->insertBooksClick(array("click_count"=>1,"book_id"=>$id));
		$infoList=$booksService->getPushBook("info",3,1);
		//echo json_encode($chapterList);
        $this->view->assign('bookInfo',$bookInfo);
        $this->view->assign('infoList',$infoList);
        $this->view->assign('orderType',$orderType);
        $this->view->assign('chapterList',json_encode($chapter));
        $this->view->assign('playSong',0);
        $this->view->assign('play',false);
        $this->view->assign('playSTime',0);
        $this->view->assign('user_id', $user_id);
        $this->view->assign('id', $id);
        $this->view->assign('title', "作品详情");
        $this->view->set_tpl("index/m_bookInfo");//设置模板
        $this->view->display();
    }


    public function bookList()
    {
        $p = $this->controller->get_get('p');
        $type = $this->controller->get_get('type');
        $time = $this->controller->get_get('time');
        $serial = $this->controller->get_get('serial');
        $keyWord = $this->controller->get_get('keyWord');
        $booksService = InitPHP::getService("books");
		$bookTypeService = InitPHP::getService("bookType");
        if(!empty($type)){
			$typeInfo = $bookTypeService->getType($type);
			$title=$typeInfo['typeName'];
		}else{
			$title="书库";
		}
		$p=empty($p)?1:$p;
		$pageNum=20;
		$orderType='desc';
		$bookType=$bookTypeService->getAll();
		$bookList=$booksService->getBookList($p,$pageNum,$keyWord,$orderType,$serial,$type,$time);
		$pages = ceil($bookList['count']/$pageNum);
		$url=$this->doMain."index.php?c=index&a=bookList&type={$type}&serial={$serial}&time={$time}";
        $nexturl = $url . '&p=' . ($p < $pages ? $p + 1 : $pages);
        $backurl = $url . '&p=' . ($p > 1 ? $p - 1 : $p);
        if ($p > 1 && $p < $pages) {
            $nexttext = '下一页';
            $backtext = '上一页';
        }
        if ($p >= $pages) {
            $backtext = '上一页';
            $nexttext = '选择上面分类获取更多图书';
        }
        if ($p <= 1) {
            $backtext = '首页';
            $nexttext = '下一页';
        }
        $this->view->assign('pages', $pages);
        $this->view->assign('nexturl', $nexturl);
        $this->view->assign('backurl', $backurl);
        $this->view->assign('backtext', $backtext);
        $this->view->assign('nexttext', $nexttext);
        $this->view->assign('bookType',$bookType);
        $this->view->assign('bookList',$bookList['list']);
        $this->view->assign('serial',$serial);
        $this->view->assign('type',$type);
        $this->view->assign('time',$time);
        $this->view->assign('keyWord',$title);
        $this->view->assign('title',$title);
        $this->view->set_tpl("index/m_bookList");//设置模板
        $this->view->display();
    }


    public function chapterList()
    {
        $id = $this->controller->get_get('bookid');
        $p = $this->controller->get_get('p');
        $desc = $this->controller->get_get('dc');
        $cid = $this->controller->get_get('cid');//播放章节
        $play = $this->controller->get_get('play');//是否自动播放
        $playSTime = $this->controller->get_get('time');//从什么位置开始播放
        $song = $this->controller->get_get('song');//播放第几个，如果有该参数 那么参数cid将失效
        if (empty($p)) {
            $p = 1;
        }
        if (empty($desc)) {
            $desc = 'asc';
        }
		$play=empty($play)?false:true;
		$playSTime=empty($playSTime)?0:$playSTime;
        $pageNum =20;
		$playSong=0;//第几个开始播放
		$booksService = InitPHP::getService("books");
		if(!empty($cid) && $play==true){
			$palyNum=$booksService->getPlayChapter($cid,$id);//计算播放章节前面有多少数据
			$p = ceil($palyNum/$pageNum);//计算当前第几页
			$playSong=($palyNum%$pageNum)==0?$pageNum-1:$palyNum%$pageNum-1;//计算从哪个开始播放
		}
		if($song!=''){
			$playSong=$song;//计算从哪个开始播放
		}
        $booksService = InitPHP::getService("books");
        $bookInfo=$booksService->getBooks($id);
		$chapterList=$booksService->getChapter($id,$desc,$pageNum,$p);
		$chapterCount=$booksService->getChapterCount($id);
		foreach($chapterList as $k=>$v){
			$chapter[0][$k]['name']="第{$v['chapter']}章 {$v['title']}";
			$chapter[0][$k]['singer']='';//empty($v['is_vip'])?' ':"VIP";
			$chapter[0][$k]['song']=$k;
			$chapter[0][$k]['src']=$this->doMain."index.php?c=audio&a=one&id={$v['id']}&song={$k}&p={$p}&dc={$desc}";
			$chapter[0][$k]['time']='00:00';
		}
        $pageCount = ceil($chapterCount / $pageNum);
        $url = $this->doMain . "index.php?c=index&a=chapterList&bookid={$id}";
        $nexturl = $url . '&p=' . ($p < $pageCount ? $p + 1 : $pageCount);
        $backurl = $url . '&p=' . ($p > 1 ? $p - 1 : $p);
        if ($p > 1 && $p < $pageCount) {
            $nexttext = '下一页';
            $backtext = '上一页';
        }
        if ($p >= $pageCount) {
            $backtext = '上一页';
            $nexttext = '我是有底线的~~';
        }
        if ($p <= 1) {
            $backtext = '首页';
            $nexttext = '下一页';
        }
        $this->view->assign('nexturl', $nexturl);
        $this->view->assign('backurl', $backurl);
        $this->view->assign('backtext', $backtext);
        $this->view->assign('nexttext', $nexttext);
        $this->view->assign('pageCount', $pageCount);
        $this->view->assign('bookInfo', $bookInfo);
        $this->view->assign('chapterList',json_encode($chapter));
        $this->view->assign('p', $p);
        $this->view->assign('desc', $desc);
        $this->view->assign('playSong', $playSong);
        $this->view->assign('play', $play);
        $this->view->assign('playSTime', $playSTime);
        $this->view->assign('id', $id);
        $this->view->assign('bookId', $id);
        $this->view->assign('keyWord','目录');
        $this->view->assign('title', $bookInfo['book_name']);
        $this->view->set_tpl("index/m_chapterList");//设置模板
        $this->view->display();
    }
	public function chapterShow(){
		$id = $this->controller->get_get('bookid');
        $cid = $this->controller->get_get('cid');//播放章节
        $play = $this->controller->get_get('play');//是否自动播放
        $playSTime = $this->controller->get_get('time');//从什么位置开始播放
        $song = $this->controller->get_get('song');//播放第几个，如果有该参数 那么参数cid将失效
		$play=empty($play)?false:true;
		$playSTime=empty($playSTime)?0:$playSTime;
		$playSong=(empty($song) || $song=='-')?0:$song;//第几个开始播放
        $pageNum =10000;
		$p=1;
		$booksService = InitPHP::getService("books");
		if(!empty($cid) && $play==true){
			$palyNum=$booksService->getPlayChapter($cid,$id);//计算播放章节前面有多少数据
			echo $playSong=($palyNum%$pageNum)==0?$pageNum-1:$palyNum%$pageNum-1;//计算从哪个开始播放
		}
        $bookInfo=$booksService->getBooks($id);
		$chapterList=$booksService->getChapter($id,'asc',$pageNum,$p);
		foreach($chapterList as $k=>$v){
			$chapter[0][$k]['name']="第{$v['chapter']}章 {$v['title']}";
			$chapter[0][$k]['singer']='';//empty($v['is_vip'])?' ':"VIP";
			$chapter[0][$k]['src']=$this->doMain."index.php?c=audio&a=one&id={$v['id']}&song={$k}&p={$p}&dc={$desc}";
			$chapter[0][$k]['time']='00:00';
			$chapter[0][$k]['erweima']=0;
			if ($bookInfo['erweima_url'] != 0) {
			    if ($bookInfo['visible_erweima_chapter']==0 or $v['sort']==$bookInfo['visible_erweima_chapter']) {
			        $chapter[0][$k]['erweima']=1;
			    }
			}

		}
		if ($bookInfo['erweima_url'] != 0) {
		    $erweima_url = $bookInfo['erweima_url'];
		    $this->view->assign('erweima_url', $erweima_url);
		}
		$infoList=$booksService->getPushBook("info",3,1);
        $this->view->assign('bookInfo', $bookInfo);
        $this->view->assign('infoList',$infoList);
        $this->view->assign('chapterList',json_encode($chapter));
        $this->view->assign('playSong', $playSong);
        $this->view->assign('play', $play);
        $this->view->assign('playSTime', $playSTime);
		$this->view->assign('keyWord','爱上听书');
        $this->view->assign('title',$bookInfo['book_name']);
        $this->view->set_tpl("index/m_chapterShow");//设置模板
        $this->view->display();
	}
    public function rank(){
        $bigKey = $this->controller->get_get('bigKey');
        $minKey = $this->controller->get_get('minKey');
		$title='排行榜';
		$bigKey=empty($bigKey)?'zan':$bigKey;
		if($minKey==''){
			$minKey=7;
		}
		if($minKey=='0'){
			$minKey='';
		}
		$rankKey=$bigKey.$minKey;
		$booksService = InitPHP::getService("books");
		$list=$booksService->getPushBook($rankKey,10,1);
        $this->view->assign('list',$list);
        $this->view->assign('bigKey',$bigKey);
        $this->view->assign('minKey',$minKey);
        $this->view->assign('keyWord',$title);
        $this->view->assign('title',$title);
        $this->view->set_tpl("index/m_rank");//设置模板
        $this->view->display();
    }

    public function pushBook(){
        $pushType = $this->controller->get_get('type');
        $booksService = InitPHP::getService("books");
        $bookList = $booksService->getPushBook($pushType, 20);
        $bookTypeService = InitPHP::getService("bookType");
        $bookTypeList = $bookTypeService->getAll();
        $bookType = array();
        foreach ($bookTypeList as $k => $v) {
            $bookType[$v['id']]['typeName'] = $v['typeName'];
        }
        $this->view->assign('bookType', $bookType);
        $this->view->assign('pushType', $pushType);
        $this->view->assign('bookList', $bookList);
        $this->view->assign('title', "爱上听书推荐榜");
        $this->view->set_tpl("index/new_pushBook");//设置模板
        $this->view->display();
    }

    public function bookcase()
    {
		$booksService = InitPHP::getService("books");
        $this->view->assign('title', "书架");
        $this->view->set_tpl("index/m_bookcase");//设置模板
        $this->view->display();
    }
    public function getUrl($c, $a)
    {
        return $this->doMain . 'index.php?c=' . $c . '&a=' . $a;
    }

}
