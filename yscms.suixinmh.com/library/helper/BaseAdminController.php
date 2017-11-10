<?PHP
/*
	before方法会在run或者get方法调用之前调用。一般可以做权限操作。
	after方法会在run或者get方法调用之后调用。一般可以做日志等后置的操作。
*/
class BaseAdminController extends Controller{
	public function getService($serviceName) {  
        $Service = InitPHP::getService($serviceName);
		return $Service;
    }  
	public function before() {  
		$session = $this->getUtil('session');
		$admin_id=$session->get("admin_id");
		if(empty($admin_id)){
			return false;
			/*$url=$this->getUrl("index","login");
			$this->controller->redirect($url,0);
			die;*/
		}else{
			return $admin_id;
		}
    }  
      
    public function after() {  
    }
	/**
	 * 后台公用URL组装函数
	 * @param string $c
	 * @param string $a
	 */
	public function getUrl($c,$a) {
		return $this->doMain.'index.php?c='.$c.'&a='.$a;
	}
	/**
	 * 分页类
	 * @param int $count
	 */
	public function page($count, $str = '') {
		$pager= $this->getLibrary('pager'); //分页加载
		$c   = $this->controller->get_gp('c');
		$a   = $this->controller->get_gp('a');
		$url = $this->getUrl($c, $a) . $str;
		$page_html = $pager->pager($count, $this->perpage, $url);
		if ($count == 0) $page_html = '';
		$this->view->assign('page', $page_html);
	}
	/**
	 * 后台图片上传公用类
	 * @param string $fileName 表单名称 例如：img
	 * @param string 上传文件的路劲 例如：data/attachment/ad
	 */
	public function upload($fileName, $path) {
		$upload = $this->getLibrary('upload'); //文件上传类加载
		$allow  = array('maxSize'=>2048,'allowFileType'=>array('gif', 'jpg', 'png', 'jpeg'));
		$path   = rtrim($path, '/') . '/' . date("Y") . '/' . date("m") . '/' . date("d");
		$function = $this->getLibrary('function');
        $newFileName = $function->trade_no(); 
        $uploadResult = $upload->upload($fileName, $newFileName, $path, $allow);
		return $uploadResult;
	}
	/**
	 * 后台图片压缩 对InitPHP框架图片类进行封装
	 * @param string $source 原图片地址
	 * @param array  $param  参数
	 * 如果多张压缩图，这个数组为多维：
	 * array(
	 * 	 0=>array('_small', 50, 50)  
	 * )
	 * _small：最后图片名称 原名称：2011213902103.jpg,压缩图：2011213902103_small.jpg
	 * 如果第一个参数 _small 设置为空，则会覆盖原图
	 * 50, 50分别为：宽和高
	 */
	public function imageThumb($source, $param = array(0=>array('_small', 50, 50))) {
		$image   = $this->getLibrary('image'); //图片类加载
		$newName = str_replace(strstr($source, '.'), '', $source);
		foreach ($param as $val) {
       		$image->make_thumb($source, $newName . $val[0], $val[1], $val[2], true); //缩略图
		}
		return true;
	}
	/**
     * 日期处理
     */
	public function _getDateTime($dates) {
		if ($dates == '') return 0;
		$dtime[0] = substr($dates, 0, 10);
		$dtime[1] = substr($dates, -8, 8);
		$date     = explode('-', $dtime[0]);
		$time     = explode(':', $dtime[1]);
		return mktime($time[0], $time[1], $time[2], $date[1], $date[2], $date[0]);	
	}
}