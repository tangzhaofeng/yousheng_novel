<?PHP
class payController extends BaseUserController{
	public $initphp_list = array('ali','aliApyDo','notifyAli','notifyH5');
	public $publicFunction;
	public $configDo;
	public $sessionDo;
	public $doMain;
	public $thisUrl;
	public $aliPay;
	public $user_id;
	public $papPay;
	public $h5Pay;
	public function __construct(){
		parent::__construct();
		$this->user_id=parent::before();
		$this->configDo = InitPHP::getConfig();
		$this->sessionDo = $this->getUtil('session');
		$this->publicFunction = $this->getLibrary('function');
		$this->doMain=$this->configDo['url'];
		$this->thisUrl=$this->publicFunction->get_url();
		$this->aliPay=$this->getLibrary('aliPay');
		$this->h5Pay=$this->getLibrary('h5Pay');
	}
	public function notifyH5(){
		$xml =file_get_contents("php://input");//$GLOBALS['HTTP_RAW_POST_DATA'];
		$result = $this->h5Pay->FromXml($xml);
		if($result['return_code']=='SUCCESS'){
			$giveConf=$this->configDo['give_conf'];
			$scale=$this->configDo['money_coin_scale'];//兑换比例
			$data['source']=2;//来源微信支付
			$data['out_trade_no']=$result['out_trade_no'];//用户订单
			$data['user_id']=$result['device_info'];//用户ID
			$data['trade_no']=$result['transaction_id'];//微信订单号
			$data['money']=(int)($result['total_fee']/100);//充值金额
			$data['scale']=$scale;
			$data['moneyCoin']=$data['money']*$scale;//充值阅读币
			$data['give']=$data['money']*$giveConf['give_scale_'.$data['money']]*$scale;//充值阅读币
			$data['create_time']=time();

			$authorUserService = InitPHP::getService("authorUser");
			$PayRecordInfo=$authorUserService->getOnePayRecord(array("out_trade_no"=>$data['out_trade_no']));
			if(empty($PayRecordInfo)){
				if($authorUserService->userPayRecord($data)){
					$xml='<xml>'; 
					$xml.='<return_code><![CDATA[SUCCESS]]></return_code>';
					$xml.='<return_msg><![CDATA[OK]]></return_msg>';
					$xml.='</xml>';
					echo $xml;
				}
			}
		}
		$content=json_encode($result);
		$time=time();
        $log_str="$time   \n$content\n------------------\n";
        $file_n="notifyH5__".date("Ymd").".txt";
        $file=fopen("./data/$file_n","a+");
        fwrite($file, $log_str);
        fclose($file);
	}
	public function aliApyDo(){
		if($this->user_id){
			$toUrl=$this->controller->get_get('toUrl');
			$this->sessionDo->set("toUrl",$toUrl);
			$giveConf=$this->configDo['give_conf'];
			$scale=$this->configDo['money_coin_scale'];//兑换比例
			//商户订单号，商户网站订单系统中唯一订单号，必填
			$out_trade_no =$this->publicFunction->trade_no();//$_POST['WIDout_trade_no'];
			//付款金额，必填
			$total_fee =$this->controller->get_get('money');
			//订单名称，必填
			$subject ="No_".$this->user_id."_充值阅读币".($total_fee+$total_fee*$giveConf['give_scale_'.$total_fee])*$scale."枚";//$_POST['WIDsubject'];
			//收银台页面上，商品展示的超链接，必填
			$show_url =$this->configDo['url']."index.php?c=user&a=Cmoney";//$_POST['WIDshow_url'];
	
			//商品描述，可空
			$body ="充值阅读币";
			//构造要请求的参数数组，无需改动
			$alipay_config=$this->configDo['aliPayConfig'];
			$parameter = array(
					"service"       => $alipay_config['service'],
					"partner"       => $alipay_config['partner'],
					"seller_id"  => $alipay_config['seller_id'],
					"payment_type"	=> $alipay_config['payment_type'],
					"notify_url"	=> $alipay_config['notify_url'],
					"return_url"	=> $alipay_config['return_url'],
					"_input_charset"	=> trim(strtolower($alipay_config['input_charset'])),
					"out_trade_no"	=> $out_trade_no,
					"subject"	=> $subject,
					"total_fee"	=> $total_fee,
					"show_url"	=> $show_url,
					"body"	=> $body,
			);
			//建立请求
			$this->aliPay->AlipaySubmit($alipay_config);
			$html_text = $this->aliPay->buildRequestForm($parameter,"get","确认");
			echo $html_text;
		}
	}
	public function notifyAli(){
		$alipay_config=$this->configDo['aliPayConfig'];
		$giveConf=$this->configDo['give_conf'];
		//计算得出通知验证结果
		$this->aliPay->AlipaySubmit($alipay_config);
		$verify_result = $this->aliPay->verifyNotify($_POST);
		if($verify_result) {//验证成功
			//商户订单号
			$out_trade_no = $_POST['out_trade_no'];
			//支付宝交易号
			$trade_no = $_POST['trade_no'];
			$subject  =$_POST['subject'];
			//交易状态
			$trade_status = $_POST['trade_status'];
			if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
				//如果做过处理返回自己想要的页面
				$userInfo=explode("_",$subject);
				$scale=$this->configDo['money_coin_scale'];//兑换比例
				$data['source']=1;//来源支付宝
				$data['out_trade_no']=$out_trade_no;//用户订单
				$data['user_id']=$userInfo[1];//用户ID
				$data['trade_no']=$trade_no;//支付宝交易号
				$data['money']=(int)$_POST['total_fee'];//充值金额
				$data['scale']=$scale;
				$data['moneyCoin']=$data['money']*$scale;//充值阅读币
				$data['give']=$data['money']*$giveConf['give_scale_'.$data['money']]*$scale;//充值阅读币
				$data['create_time']=time();
				$this->aliPay->logResult(json_encode($data));
				$authorUserService = InitPHP::getService("authorUser");
				$PayRecordInfo=$authorUserService->getOnePayRecord(array("out_trade_no"=>$out_trade_no));
				if(empty($PayRecordInfo)){
					if($authorUserService->userPayRecord($data)){
						echo "success";
					}
				}else{
					echo "success";
				}
				//print_r($data);
			}
		}else {
			//验证失败
			echo "fail";
		}
	}
}
