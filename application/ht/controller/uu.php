<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use think\Loader;
use think\Session;

class uu extends Controller
{
	public $admin = array();
	public $uid;
	public $session;
	
	public function _initialize(){
		$cUser = $this->onGetCurrentUser();
		$uid = $cUser['uid'] ? $cUser['uid'] : '';
		
		$cUser = Db::name('admin')->find($uid);
		
		if(!$uid) {
			$this->onLogout();
			die();
		} 
		$this->user = $cUser;
		$this->onSetCurrentUser($cUser);
		$this->uid = $cUser['uid'];
		$this->assign("admin", $cUser);
		
		$session = Session::get('admin');
		$this->session = $session;
		$this->assign("session", $session);
	

	}
	
	//获取登录管理员信息
    public function onGetCurrentUser() {
    	$admin = array();
		$session = Session::get('admin');
        $admin['uid'] = isset ($session['uid']) ? $session['uid'] : null;
        $admin['openid'] = isset ($session['username']) ? $session['username'] : null;
        return $admin;
    }
    
    //管理员登出
    public function onLogout() {
        Session::set('admin', '');
        $this->redirect('/ht/login');
    }
    
    //保存当前管理员信息
    public function onSetCurrentUser($admin) {
		$session = Session::get('admin');
    	$session['uid'] = $admin['uid'];
    	$session['username'] = $admin['username'];
    }
	

    
}
