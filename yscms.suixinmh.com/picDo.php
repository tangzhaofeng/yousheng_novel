<?PHP
class pic{
	static function upImg($sgin,$book_id,$book_pic,$mulu="books"){
		if($sgin=='a7ed7a10dfb05e4d5c0622136d227534'){
			if(preg_match('/^(data:\s*image\/(\w+);base64,)/',$book_pic)){ 
				$path="./Uploads/{$mulu}/";
				$book_pic=pic::base64ToPic($path,$book_pic,$book_id);
				return $book_pic;
			}
		}
	}
	static function base64ToPic($path,$base64Str,$bookId=0){
		pic::createFolder($path); //创建目录
		if (preg_match('/^(data:\s*image\/(\w+);base64,)/',$base64Str,$result)){
			$type=$result[2];
			$imgName=date('ymdHis').uniqid().str_pad($bookId,10,"0",STR_PAD_LEFT);
			$imgNameStr=$imgName.".{$type}";
			$new_file=$path.$imgNameStr;
			if (!is_dir($path)){
				$res=mkdir($path,0777,true); 
				if (!$res){
					echo "目录 $path 创建失败";
				}
			}
			if (file_put_contents($new_file,base64_decode(str_replace($result[1],'',$base64Str)))){
				return $imgNameStr;
			}
		}
	}
	static function createFolder($path) {
		if (!is_dir($path)) {
			pic::createFolder(dirname($path));
			@mkdir($path);
			@chmod($path,0777);
			@fclose(@fopen($path.'/index.html','w'));
			@chmod($path.'/index.html',0777);
		}
	}
}
/*$path="./Uploads/books/";
pic::createFolder($path); //创建目录
*/	$sgin    =$_POST['sgin'];
	$id =$_POST['id'];
	$book_pic=$_POST['bookPic'];
	$mulu=empty($_POST['mulu'])?"books":$_POST['mulu'];
	if(!preg_match("/^[0-9A-Za-z]+$/",$sgin)){
		die;
	}
	if(!preg_match("/^[0-9]+$/",$id)){
		die;
	}
	echo pic::upImg($sgin,$id,$book_pic,$mulu);
?>