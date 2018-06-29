<?php
namespace helper;

use Payment\TransferContext;
use Payment\Common\PayException;
use Payment\Config;

/*
微信企业 支付到个人
*/
class Wxcash
{
	
	function arrayToXml($arr){ 
		$xml = "<root>"; 
		foreach ($arr as $key=>$val){ 
		if(is_array($val)){ 
		$xml.="<".$key.">".arrayToXml($val)."</".$key.">"; 
		}else{ 
		$xml.="<".$key.">".$val."</".$key.">"; 
		} 
		} 
		$xml.="</root>"; 
		return $xml; 
	}
	
	function curl_post_ssl($url, $vars, $second=30){
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
		
		curl_setopt($ch,CURLOPT_SSLCERT,"/home/wwwroot/customer/daike-xcx.maoln.com/cert/apiclient_cert.pem");
		curl_setopt($ch,CURLOPT_SSLKEY,"/home/wwwroot/customer/daike-xcx.maoln.com/cert/apiclient_key.pem");
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);
		$data = curl_exec($ch);
		if($data){
			curl_close($ch);
			return $data;
		}else{
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}

	}
	
	//企业向个人付款
	public function payToUser($openid,$amount,$desc='提现成功'){
		
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		
		$config = config('wechat');
		
		$params["mch_appid"]        = $config['appId'];   //公众账号appid
		$params["mchid"]            = $config['mch_id'];   //商户号 微信支付平台账号\
		$params["nonce_str"]        = 'qilindaike99'.mt_rand(100,999);   //随机字符串
		$params["partner_trade_no"] = mt_rand(10000000,99999999);           //商户订单号
		$params["amount"]           = $amount;          //金额
		$params["desc"]             = '企业付款';            //企业付款描述
		$params["openid"]           = $openid;          //用户openid
		$params["check_name"]       = 'NO_CHECK';       //不检验用户姓名
		$params['spbill_create_ip'] = '47.92.67.110';   //获取IP
		$params['md5_key'] = $config['md5_key'];
		
		//生成签名(签名算法后面详细介绍)
		$str = 'amount='.$params["amount"].'&check_name='.$params["check_name"].'&desc='.$params["desc"].'&mch_appid='.$params["mch_appid"].'&mchid='.$params["mchid"].'&nonce_str='.$params["nonce_str"].'&openid='.$params["openid"].'&partner_trade_no='.$params["partner_trade_no"].'&spbill_create_ip='.$params['spbill_create_ip'].'&key='.$params['md5_key'];
	

		//md5加密 转换成大写
		$sign = strtoupper(md5($str));
		$params["sign"] = $sign;//签名
		$xml = $this->arrayToXml($params);
		return $this->curl_post_ssl($url, $xml);

	}
	
	
	//  生成转款单号 便于测试
	function createPayid()
	{
	    return date('Ymdhis', time()).substr(floor(microtime()*1000),0,1).rand(0,9);
	}
	
}