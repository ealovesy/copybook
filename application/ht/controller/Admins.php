<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use helper\Page;

/**
 * 管理员列表
 */
 
class Admins extends uu
{
	
	function index(){
		
		$where = array();
		
		//查询总数total
		$total = Db::name('admin')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 管理员AJAX列表
	 */
	public function getPage(){
		$post = request()->post();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where = array();
		
		if(isset($post['username'])){
			$url.= '?username='.$post['username'];
			$where['username'] = array('like', "%".$post['username']."%");
		}
		
		$admin = Db::name('admin')
				->where($where)
				->order('uid desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('admin')->where($where)->count();
    	
    	tableJson($admin, $total);
	}
	
  	/*
	*添加管理员
	*/
	public function edit(){
		$get = request()->get();
		
		if(!empty($get['uid'])){
			$admin = Db::name('admin')->find($get['uid']);
			$data['admins'] = $admin;
		}
		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*编辑管理员
	*/
	public function do_edit(){
		$post = request()->post();
		
		$salt = model('admin')->get_uniqid();
		$password = model('admin')->formatPassword($post['password'], $salt);
		
		$data = array(
			'password' => $password,
			'salt' => $salt
		);
		if(!empty($post['uid'])){
			model('admin')->where('uid', $post['uid'])->update($data);
			ajaxResult('100','编辑成功');
		}else{
			$data['username'] = $post['username'];
			$data['create_at'] = time();
			$uid = model('admin')->addData($data);
			if($uid>1){
				ajaxResult('100','添加成功');
			}else{
				ajaxResult('-1','添加失败');
			}
		}
	}
	
	/*
	*删除管理员
	*/
	public function del_admin(){
		$post = request()->post();
		if(!empty($post['uid'])){
			Db::name('admin')->delete($post['uid']);
			ajaxResult('100');
		}else{
			ajaxResult('-1','丢失参数');
		}
	}
	
	
}
