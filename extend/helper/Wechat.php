<?php
namespace helper;

/**
 * 微信接口
*/
class Wechat{
	public   $xml;
	public   $token;
	private  $wechat;
	private  $_msg;
	private  $flag = false;
	public   $debug = false;
	
	//获取微信服务器发来的信息
	function getObj() {
		$postStr = file_get_contents("php://input");
		//$postStr = '<xml><ToUserName><![CDATA[gh_9dd32b70c06e]]></ToUserName> <FromUserName><![CDATA[oUIUEj4ggxBSgWycvAzH1kO565bo]]></FromUserName> <CreateTime>1375064367</CreateTime> <MsgType><![CDATA[text]]></MsgType> <Content><![CDATA[关注回复]]></Content> <MsgId>5893718947236151326</MsgId> </xml>';
		//$postStr = '<xml><ToUserName><![CDATA[gh_9dd32b70c06e]]></ToUserName> <FromUserName><![CDATA[ouWyKjgxeuNKA9dD8fkPE52aeV78]]></FromUserName> <CreateTime>1372382967</CreateTime> <MsgType><![CDATA[event]]></MsgType> <Event><![CDATA[subscribe]]></Event> <EventKey>5893718947236151326</EventKey> </xml>';//
		//$postStr = '<xml><ToUserName><![CDATA[gh_6e965df15a7e]]></ToUserName><FromUserName><![CDATA[oQ0YOt2qAZvPXi83cRCSRyP4T4Xg]]></FromUserName><CreateTime>1399542490</CreateTime><MsgType><![CDATA[event]]></MsgType><Event><![CDATA[CLICK]]></Event><EventKey><![CDATA[bind]]></EventKey></xml>';
		$this->xml = $postStr;
		if (!empty($postStr)){
			$this->log('wechat:'.$postStr);
			$postObj = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			$wechat = array();
			$proArrs = self::_Pros($postObj['MsgType']);
			$wechat['wxid'] = $postObj['FromUserName'];
			$wechat['toname'] = $postObj['ToUserName'];
			$wechat['type'] = $postObj['MsgType'];
			$wechat['time'] = $postObj['CreateTime'];
			foreach($proArrs as $pro) {
				$wechat['content'][$pro] = $postObj[$pro];
			}
			$this->wechat = $wechat;
			return $wechat;
		}
		return false;
	}
	
	function _Pros($type) {
		$msgtype = array(
				'text' => array('Content'),
				'image' => array('PicUrl'),
				'location' => array('Location_X', 'Location_Y', 'Scale', 'Label'),
				'link' => array('Title', 'Description', 'Url'),
				'event' => array('Event', 'EventKey'),
			);
		return $msgtype["$type"];
	}
	
	public function valid($return=false) {
		$echoStr = isset($_GET["echostr"]) ? $_GET["echostr"] : '';
        if ($return) {
    		if ($echoStr) {
    			if ($this->checkSignature())
    				return $echoStr;
    			else
    				return false;
    		} else 
    			return $this->checkSignature();
        } else {
        	if ($echoStr) {
        		if ($this->checkSignature())
        			die($echoStr);
        		else 
        			die('no access');
        	}  else {
        		if ($this->checkSignature())
        			return true;
        		else
        			die('no access');
        	}
        }
        return false;
	}
	
	private function checkSignature() {
        $signature = request()->get('signature');
        $timestamp = request()->get('timestamp');
        $nonce = request()->get('nonce');
		$tmpArr = array($this->token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}
	
    private function log($log){
		if($this->debug) {
			if (is_array($log)) $log = print_r($log, true);
			$log_dir = ROOT.'data/weixin/';
	    	if(!is_dir($log_dir)) {
	        	mkdir($log_dir, 0777);
	    	}
			@file_put_contents($log_dir.'put_log', $log."\n", FILE_APPEND);
		}
		return;
    }
    
	public static function xmlSafeStr($str)	{   
		return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
	} 

	//数据XML编码
	public static function data_to_xml($data) {
	    $xml = '';
	    foreach ($data as $key => $val) {
	        is_numeric($key) && $key = "item id=\"$key\"";
	        $xml    .=  "<$key>";
	        $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
	        list($key, ) = explode(' ', $key);
	        $xml    .=  "</$key>";
	    }
	    return $xml;
	}	

	//XML编码
	public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
	    if(is_array($attr)){
	        $_attr = array();
	        foreach ($attr as $key => $value) {
	            $_attr[] = "{$key}=\"{$value}\"";
	        }
	        $attr = implode(' ', $_attr);
	    }
	    $attr   = trim($attr);
	    $attr   = empty($attr) ? '' : " {$attr}";
	    $xml   = "<{$root}{$attr}>";
	    $xml   .= self::data_to_xml($data, $item, $id);
	    $xml   .= "</{$root}>";
	    return $xml;
	}

	//设置回复消息
	public function text($text='') {
		$FuncFlag = $this->flag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->wechat['wxid'],
			'FromUserName'=>$this->wechat['toname'],
			'MsgType'=>'text',
			'Content'=>$text,
			'CreateTime'=>time(),
			'FuncFlag'=>$FuncFlag
		);
		$this->reply($msg);
		return;
	}

	//设置回复音乐
	public function music($title, $desc, $musicurl, $hgmusicurl='') {
		$FuncFlag = $this->flag ? 1 : 0;
		$msg = array(
			'ToUserName' => $this->wechat['wxid'],
			'FromUserName'=>$this->wechat['toname'],
			'CreateTime'=>time(),
			'MsgType'=>'music',
			'Music'=>array(
				'Title'=>$title,
				'Description'=>$desc,
				'MusicUrl'=>$musicurl,
				'HQMusicUrl'=>$hgmusicurl
			),
			'FuncFlag'=>$FuncFlag
		);
		$this->reply($msg);
		return;
	}

	//设置回复图文
	public function news($newsData=array()) {
		$FuncFlag = $this->flag ? 1 : 0;
		$count = count($newsData);
		$msg = array(
			'ToUserName' => $this->wechat['wxid'],
			'FromUserName'=>$this->wechat['toname'],
			'MsgType'=>'news',
			'CreateTime'=>time(),
			'ArticleCount'=>$count,
			'Articles'=>$newsData,
			'FuncFlag'=>$FuncFlag
		);
		$this->reply($msg);
		return;
	}

	//回复微信服务器
	public function reply($msg=array(), $return = false) {
		if (empty($msg)) return;
		$xmldata=  $this->xml_encode($msg);
		$this->log('reply:'.$xmldata."\n");
		if ($return)
			return $xmldata;
		else
			echo $xmldata;
	}
}
