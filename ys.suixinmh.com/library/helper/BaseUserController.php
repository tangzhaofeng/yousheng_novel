<?PHP
/*
	before方法会在run或者get方法调用之前调用。一般可以做权限操作。
	after方法会在run或者get方法调用之后调用。一般可以做日志等后置的操作。
*/
class BaseUserController extends Controller{
	public function before(){
		$session = $this->getUtil('session');
		$user_id=$session->get("user_id");
		if(empty($user_id)){
			return false;
		}else{
			return $user_id;
		}
	}
	public function publicBookClick($booksService,$wxRedis,$bookId,$up=true) {
		if($up==true){
			$sessonid = $_COOKIE["PHPSESSID"];
			$bookClick=$wxRedis->get($sessonid .'_book_click_'.$bookId);
			$wxRedis->set($sessonid .'_book_click_'.$bookId,1,1800);
			if(empty($bookClick)){
				$dataClick['book_id']=$bookId;
				$dataClick['click_count']=1;
				$booksService->insertBooksClick($dataClick);
			}
		}
		$clickInfo=$booksService->getBooksClick(array("book_id"=>$bookId));
		return $clickInfo;
	}
	public function after() {
    }
	public function getUrl($c,$a) {
		return $this->doMain.'index.php?c='.$c.'&a='.$a;
	}
	public function page($count, $str = '') {
		$pager= $this->getLibrary('pager'); //分页加载
		$c   = $this->controller->get_gp('c');
		$a   = $this->controller->get_gp('a');
		$url = $this->getUrl($c, $a) . $str;
		$page_html = $pager->pager($count, $this->perpage, $url);
		if ($count == 0) $page_html = '';
		$this->view->assign('page', $page_html);
	}
	public function upload($fileName, $path) {
		$upload = $this->getLibrary('upload'); //文件上传类加载
		$allow  = array('maxSize'=>2048,'allowFileType'=>array('gif', 'jpg', 'png', 'jpeg'));
		$path   = rtrim($path, '/') . '/' . date("Y") . '/' . date("m") . '/' . date("d");
		$function = $this->getLibrary('function');
        $newFileName = $function->trade_no(); 
        $uploadResult = $upload->upload($fileName, $newFileName, $path, $allow);
		return $uploadResult;
	}
	public function imageThumb($source, $param = array(0=>array('_small', 50, 50))) {
		$image   = $this->getLibrary('image'); //图片类加载
		$newName = str_replace(strstr($source, '.'), '', $source);
		foreach ($param as $val) {
       		$image->make_thumb($source, $newName . $val[0], $val[1], $val[2], true); //缩略图
		}
		return true;
	}
	public function _getDateTime($dates) {
		if ($dates == '') return 0;
		$dtime[0] = substr($dates, 0, 10);
		$dtime[1] = substr($dates, -8, 8);
		$date     = explode('-', $dtime[0]);
		$time     = explode(':', $dtime[1]);
		return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);	
	} 
}