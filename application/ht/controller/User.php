<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use helper\Page;

/**
 * 用户管理
 */
 
class User extends uu
{
	function index(){
		
		$where = array();
		
		//查询总数total
		$total = Db::name('user')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 用户AJAX列表
	 */
	public function getPage(){
		$post = request()->post();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where = array();
		
		if(isset($post['name'])){
			$url.= '?name='.$post['name'];
			$where['nickname'] = array('like', "%".$post['name']."%");
		}
		
		$users = Db::name('user')
				->where($where)
				->order('uid desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('user')->where($where)->count();
    	
    	tableJson($users, $total);
	}

  
}
