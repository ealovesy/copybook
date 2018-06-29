<?php
namespace helper;
use think\Config;

class JSSDK{

	private $appId;
	private $appSecret;
	private $access_token;
	private $QcodeTicket;

	public function __construct($appId='', $appSecret='') {
		$cnf = Config::get('wechat');
		$this->appId = $cnf['appId'];
		$this->appSecret = $cnf['appSecret'];
	}

	 public function getSignPackage() {
		
		$jsapiTicket = $this->getJsApiTicket();
		$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();

		// ���������˳��Ҫ���� key ֵ ASCII ����������
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);
		$signPackage = array(
			"appId"     => $this->appId,
			"nonceStr"  => $nonceStr,
			"timestamp" => $timestamp,
			"url"       => $url,
			"signature" => $signature,
			"rawString" => $string
		);
		return $signPackage; 
	}

	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	private function getJsApiTicket() {
		// jsapi_ticket Ӧ��ȫ�ִ洢����£����´�����д�뵽�ļ�����ʾ��
		$fdir = ROOT_PATH.'public/data/';
		$fdir_all = $fdir.'jsapi_ticket.json';
		if(!file_exists($fdir)){
			$this->createDir($fdir);
		}
		if(!file_exists($fdir_all)){
			$ticket = $this->createTicket($fdir_all);
		}
		
		$data = json_decode(file_get_contents($fdir_all));
		if ($data->expire_time < time()) {
			$ticket = $this->createTicket($fdir_all);
		} else {
			$ticket = $data->jsapi_ticket;
		}
		return $ticket;
	}

	public function getAccessToken() {
		// access_token Ӧ��ȫ�ִ洢����£����´�����д�뵽�ļ�����ʾ��
		$fdir = ROOT_PATH.'public/data/';
		$fdir_all = $fdir.'access_token.json';
		if(!file_exists($fdir)){
			$this->createDir($fdir);
		}
		if(!file_exists($fdir_all)){
			$access_token = $this->createToken($fdir_all);
		}
		
		$data = json_decode(file_get_contents($fdir_all));
		if ($data->expire_time < time()) {
			$access_token = $this->createToken($fdir_all);
		} else {
			$access_token = $data->access_token;
		}
		return $access_token;
	}
	
	function createTicket($fdir_all){
		$accessToken = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";
		$res = json_decode($this->httpGet($url));
		$ticket = $res->ticket;
		if ($ticket) {
			$data['expire_time'] = time() + 7000;
			$data['jsapi_ticket'] = $ticket;
			$fp = fopen($fdir_all, "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		}
		return $ticket;
	}
	
	function createToken($fdir_all){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$this->appId&secret=$this->appSecret";
		$res = json_decode($this->httpGet($url));
		$access_token = $res->access_token;
		if ($access_token) {
			$data['expire_time'] = time() + 7000;
			$data['access_token'] = $access_token;
			$fp = fopen($fdir_all, "w");
			fwrite($fp, json_encode($data));
			fclose($fp);
		}
		return $access_token;
	}
	
	//�Զ���˵�
	function createMenu($data){
		$access_token = $this->getAccessToken();
		$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$access_token;
		$res = $this->postSimple($url, $data);
		return $res;
	}
	
	/**
	 * ��ȡ��������ά�� scene_id
	 */
	public function getQcodeTicket($scene_id){ //����ֵ
		if(null != $this->QcodeTicket) return $this->QcodeTicket;
		$access_token = $this->getAccessToken();
		file_put_contents('tt.txt', $scene_id.PHP_EOL, FILE_APPEND);
		$url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=".$access_token;
		
		//��ʱ��ά��
		/*$data = [
			'expire_seconds' => 2592000,//30�죬��ʱ��ά�����Чʱ��
			'action_name'    => 'QR_SCENE',//��ʱ�Ͷ�ά��
			'action_info'    => [
				'scene' => [
					'scene_id' => $scene_id
				]
			]
		];*/
		
		//���ö�ά��
		$data = [
			'action_name'    => 'QR_LIMIT_SCENE',
			'action_info'    => [
				'scene' => [
					'scene_id' => $scene_id
				]
			]
		];
		
		$obj = $this->postSimple($url, $data);
		$this->QcodeTicket = $obj['ticket'];
		return $this->QcodeTicket;
	}
	
	/**
	 * ����ticket���ɶ�ά������
	 * @param  string $ticket
	 * @return string
	 */
	public function getQcodeUrl($scene_id){
		if(null == $this->QcodeTicket){
			$this->getQcodeTicket($scene_id);
		}

		return 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urlencode($this->QcodeTicket);
	}
	
	
	private function httpGet($url) {
		$urls = parse_url($url);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		if($urls['scheme']=='https') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	private function httpPost($url, $param='') {
    	$urls = parse_url($url);
		if (is_array($param)) {
			$aPOST = array();
			foreach($param as $key=>$val){
				$aPOST[] = $key."=".urlencode($val);
			}
			$params =  join("&", $aPOST);
		} else $params = $param;
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_HEADER, 0);
	    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
	    curl_setopt($ch, CURLOPT_POST, 1);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
		if($urls['scheme']=='https') {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		}
	    $output = curl_exec($ch);
	    curl_close($ch);
		return $output;
    }
    
    /**
	 * ����https��POST����
	 * @param  string $url
	 * @param  mix    $data json | array
	 * @return obj
	 */
	public static function postSimple($url, $data = null){
		if(is_array($data))
			$data = json_encode($data);
		
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
		if(null != $data){
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
		}
		$output = curl_exec($curl);
		curl_close($curl);
		return json_decode($output, true);
	}
	
	public function createDir($fdir){
		if (!is_dir($fdir)) {
            if (!mkdir($fdir, 0777, true)) {
                return false;
            }
            @chmod($fdir, 0777);
        }
	}
}