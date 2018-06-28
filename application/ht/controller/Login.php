<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use think\Session;

class Login extends Controller
{
    public function index()
    {
    	$post = request()->post();
    	if(request()->isPost()){
    		if(!$post['username']) ajaxResult('0','请填写用户名');
    		if(!$post['password']) ajaxResult('0','请填写密码');
   			
    		$usr = $this->onAdmin($post['username'], $post['password']);
    		if(!$usr) {
				ajaxResult('0','不存在该帐户或者密码错误');
			}
			
			//查询是否禁用登录
			if($usr[0]['disabled']>0) ajaxResult('0','您已被禁止登录，请联系管理员');
			
			$this->setAdmin(current($usr));
			ajaxResult('100');
    	}
    	
	
        return $this->fetch("login/index");
    }
    
    function out(){
    	Session::set('admin', '');
    	$this->redirect('/ht/login');
    }

    function onAdmin($name, $pwd){
    	$pwd = $this->formatPassword($pwd, $this->getSaltByName($name));
    	return Db::name('admin')->where(array('username'=>$name, 'password'=>$pwd))->select();
    }
    
    //加密密码
    function formatPassword($password, $salt) {
        return md5(md5($password).$salt);
    }
    
    function getSaltByName($username){
    	$u = Db::name('admin')->where('username',$username)->select();
    	return empty($u[0]['salt']) ? '' : $u[0]['salt'];
    }
    
    public function setAdmin($user) {
    	$arr = array(
    		'uid' => $user['uid'],
    		'username'=> $user['username']
    	);
    	
    	Session::set('admin', $arr);
    	
    	$a = Session::get('admin');
    }
}
