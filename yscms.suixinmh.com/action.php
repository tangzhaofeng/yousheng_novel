<?php
$action = $_GET['act'];
$cp = $_POST['cp'];
if($action=='delimg'){
	$filename = $_POST['imagename'];
	if(!empty($filename)){
		unlink('upFile/'.$filename);
		echo '1';
	}else{
		echo '删除失败.';
	}
}else{
	$picname = $_FILES['mypic']['name'];
	$picsize = $_FILES['mypic']['size'];
	if ($picname != "") {
		if ($picsize > 1024000) {
			/*echo '图片大小不能超过1M';
			exit;*/
		}
		$type = strstr($picname, '.');
		if ($type != ".gif" && $type != ".jpg") {
			/*echo '图片格式不对！';
			exit;*/
		}
		$rand = rand(100, 999);
		$pic = date("YmdHis") . $rand . $type;
		$mode = 0777;
		chdir("upFile");
		//上传路径
		if ($cp == 'auAudio') {
		    if (!is_dir('authorSound')) {
		        mkdir("authorSound",$mode);
		    }
		    $pics = "authorSound".$pic;
		}else{
		    $dir_1 = date("Y");
		    $dir_2 = $dir_1 ."/". date("m");
		    $dir_3 = $dir_2 ."/". date("d");

		    if (!is_dir($dir_1)) {
		        mkdir($dir_1,$mode);
		    }
		    if (!is_dir($dir_2)) {
		        mkdir($dir_2,$mode);
		    }
		    if (!is_dir($dir_3)) {
		        mkdir($dir_3,$mode);
		    }
		    $pics = $dir_3."/". $pic;
		}
		chdir("../");
		$pic_path = "upFile/".$pics;
		move_uploaded_file($_FILES['mypic']['tmp_name'], $pic_path);
	}
	$size = round($picsize/1024,2);
	$arr = array(
		'name'=>$picname,
	    'pic'=>$pics,
		'size'=>$size,
	    'cp'=>$cp
	);
	echo json_encode($arr);
}
?>