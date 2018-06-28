<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use think\Session;
use helper\Page;
use app\ht\model\Admin as admin_model;

class Admins extends uu
{
    public function index()
    {
    	$url = "admins/index";
		$get = request()->get();
		$page = isset($get['page']) ? $get['page'] : 1;
		$num = 15;
		$start = ($page - 1) * $num;
		
		$where = array();

		if(isset($get['username'])){
			$url.= '?username='.$get['username'];
			$where['username'] = array('like', "%".$get['username']."%");
		}
		
    	$admins = Db::name('admin')->where($where)->limit($start, $num)->select();
		//查询总数total
		$total = Db::name('admin')->where($where)->count();
		
		$pager = Page::set($total, $num, $page, $url, 1, 3);
		
    	$data = array(
    		'admins' => $admins,
    		'pager' => $pager,
    		'username' => isset($get['username']) ? $get['username'] : '',
    	);
    	$this->assign($data);
        return $this->fetch("admins/index");
    }
    
	function add(){
		if(request()->isPost()){
			$post = request()->post();
			if(!$post['username']) $this->error('请填写用户名');
			if(!$post['password']) $this->error('请填写密码');
			
			$admin_model = new admin_model;
			$salt = $admin_model->get_uniqid();
			$password = $admin_model->formatPassword($post['password'], $salt);
			
            $admin_model->data([
                'username' => input('username'),
		 		'password' => $password,
		 		'salt' => $salt,
            ]);
            $db= $admin_model->save();
            if($db){
                $this->redirect("/ht/admins/index");
            }else{
                return $this->error('添加管理员失败！');
            }
		}else{
			return $this->fetch("add");
		}
	}
	
	function del(){
		if(is_numeric(request()->get('uid'))){
			$result = Db::name('admin')->where('uid',request()->get('uid'))->delete();
	        if ($result) {
	        	$this->redirect("/ht/admins/index");
	        }else{
	            return $this->error('删除失败');
	        }
		}else{
			$this->error("缺少必要参数");
		}
	}
	
	function edit(){
		$get = request()->get();
		if(is_numeric(request()->post('uid'))){
			$post = request()->post();
			if(!$post['username']) $this->error('请填写用户名');
			if(!$post['password']) $this->error('请填写密码');
			
			$admin_model= new admin_model;
			$admin = Db::name('admin')->find($post['uid']);
			$password = $admin_model->formatPassword($post['password'], $admin['salt']);
			
			$result = Db::name('admin')->where('uid', request()->post('uid'))->update(['username'=>$post['username'], 'password'=>$password ]);  
			$this->redirect("/ht/admins/index");
		}else if(is_numeric($get['uid'])){
			$admin = Db::name('admin')->find($get['uid']);
			$data=array(
				'admin' => $admin,
			);
			$this->assign($data);
			return $this->fetch("admins/edit");
		}else{
			$this->error("缺少必要参数");
		}
	}
	
	//管理员登出
    function onLogout() {
        Session::set('admin', '');
        $this->redirect('/ht/login');
        die;
    }
}
