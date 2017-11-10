<?PHP
class doingController extends Controller{
	public $initphp_list = array('book','booksList','chapter','chapterdo','neirong','booksSizeSum');
	public $publicFunction;
	public $doMain;
	public $doingDao;
	public function __construct(){
		parent::__construct();
		$this->publicFunction = $this->getLibrary('function');
		$this->doMain=$this->configDo['url'];
		$this->doingDao = InitPHP::getDao("doing");
	} 
	public function book(){
		$authorUserService = InitPHP::getService("authorUser");
		$TemplateCode='SMS_35030004';
		$code='789456';
		$mobile='15101073613';
		$json=$authorUserService->sendsms($mobile,$code,$TemplateCode);
		print_r($json);
		/*$pageNum=20;
		$p=1;
		$list=$this->doingDao->bookslist($pageNum,$p);
		foreach($list as $k =>$v){
			$datas['aid']=$v['articleid'];
			$datas['book_name']=$v['articlename'];
			$datas['is_serial']=$v['fullflag'];
			$datas['descriptions']=$v['intro'];
			$datas['size']=$v['size'];
			$datas['book_pic']='http://www.yuedufang.com/files/article/image/0/'.$v["articleid"].'/'.$v["articleid"].'s.jpg';
			$datas['create_time']=$v['lastupdate'];
			$this->doingDao->insert($datas);
		}
		print_r($datas);*/
		
	}
	public function booksList(){
		/*$booklist=$this->doingDao->books();
		foreach($booklist as $k =>$v){
			$img=file_get_contents($v['book_pic']);
			file_put_contents("./upFile/bookCover/".$v['aid']."s.jpg",$img);
			echo $v['book_pic'].'<br>';
			$data['book_pic']=$v['aid']."s.jpg";
			$this->doingDao->upbooks($v['id'],$data);
		}*/
	}
	
	public function chapter(){
		/*$booklist=$this->doingDao->books();
		foreach($booklist as $ks =>$vs){
			$info=$this->doingDao->getOneByField(array("aid"=>$vs['aid']));
			$list=$this->doingDao->booksChapter($vs['aid']);
			foreach($list as $k =>$v){
				$datas['aid']=$v['articleid'];
				$datas['cid']=$v['chapterid'];
				$datas['book_id']=$info['id'];
				$datas['title']=$v['chaptername'];
				$datas['chapter']=$v['chapterorder'];
				$datas['size']=$v['size'];
				$datas['is_vip']=$v['isvip'];
				$datas['create_time']=$v['lastupdate'];
				$this->doingDao->cinsert($datas);
			}
		}
		print_r($datas);*/
	}
	public function chapterdo(){
		/*$list=$this->doingDao->booksChapter(356,39);
		$info=$this->doingDao->getOneByField(array("aid"=>356));
		foreach($list as $k =>$v){
			$datas['aid']=$v['articleid'];
			$datas['cid']=$v['chapterid'];
			$datas['book_id']=$info['id'];
			$datas['title']=$v['chaptername'];
			$datas['chapter']=$v['chapterorder'];
			$datas['size']=round($v['size']/2,0);
			$datas['is_vip']=$v['isvip'];
			$datas['create_time']=$v['lastupdate'];
			$this->doingDao->cinsert($datas);
		}
		print_r($datas);*/
	}
	
	public function neirong() {
		/*$aid=356;//$this->controller->get_get('aid');
		$p=$this->controller->get_get('p');
		if(empty($p)){
			$p=1;
		}
		$list=$this->doingDao->chapterList($aid,$p,39);
		foreach($list as $k =>$v){
			$content=$this->doingDao->neirong($aid,$v['cid']);
			$data['book_id']=$v['book_id'];
			echo $data['chapter_id']=$v['id'];
			echo '<br>';
			$data['content']=$content;
			$this->doingDao->cneirong($data);
		}*/
	}
	public function booksSizeSum(){
		$this->doingDao->booksSizeSum();
	}
	
}
