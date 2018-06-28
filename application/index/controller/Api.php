<?php
namespace app\index\controller;
use think\Controller;
use think\Db;

class Api extends Controller
{
	public function login(){
		//根据小程序code获取用户资料
		$post = request()->post();
		$code = $post['code'];
		
		$http = new \helper\Http();
		$res = $http->get('https://api.weixin.qq.com/sns/jscode2session?appid=wx3af7e9e7a0363a91&secret=f4fb4b6aa6d870c5954e4fb07eb91bc7&js_code='.$code.'&grant_type=authorization_code');
		
		//https://blog.csdn.net/qq_33616529/article/details/79080141
		
		//获得{"session_key":"WUVdi+CLzpxC4Og6CxG6bg==","openid":"oKRwu5ZiGkDkPa0QI-sT8oPSFbtg","unionid":"obe5P0bAcaH7XSm105c112izWUn4"}，用3rd_session作为key，
		
		ajaxResult('100', $res);
	}
	
	

}