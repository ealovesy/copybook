<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use helper\Page;

/**
 * 机构管理
 */
 
class Mechanism extends uu
{
	function index(){
		
		$where = array();
		
		//查询总数total
		$total = Db::name('mechanism')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 机构AJAX列表
	 */
	public function getPage(){
		$post = request()->post();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where = array();
		
		if(isset($post['name'])){
			$url.= '?name='.$post['name'];
			$where['name'] = array('like', "%".$post['name']."%");
		}
		
		$mechanism = Db::name('mechanism')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('mechanism')->where($where)->count();
    	
    	tableJson($mechanism, $total);
	}
	
  	/*
	*添加机构
	*/
	public function edit(){
		$get = request()->get();
		
		if(!empty($get['mechanism_id'])){
			$mechanism = Db::name('mechanism')->find($get['mechanism_id']);
			$data['mechanism'] = $mechanism;
			$data['banner'] = Db::name('mechanism_banner')->where('mechanism_id', $get['mechanism_id'])->select();
		}

		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*编辑机构
	*/
	public function do_edit(){
		$post = request()->post();

		$data = array(
			'name' => $post['name'],
			'content' => $post['content'],
			'create_at' => time()
		);
		if(!empty($post['mechanism_id'])){
			model('mechanism')->where('id', $post['mechanism_id'])->update($data);
			//保存bannner
			if($post['banner']){
				model('mechanism_banner')->where('mechanism_id', $post['mechanism_id'])->delete();
				foreach((array)$post['banner'] as $img_url){
					$res = model('mechanism_banner')->addData(array('mechanism_id'=>$post['mechanism_id'], 'img_url'=>$img_url));
				}
			}
			
			ajaxResult('100','编辑成功');
		}else{
			$data['create_at'] = time();
			$mechanism_id = model('mechanism')->addData($data);
			if($mechanism_id>1){
				//保存bannner
				if($post['banner']){
					foreach((array)$post['banner'] as $img_url){
						model('mechanism_banner')->addData(array('mechanism_id'=>$mechanism_id, 'img_url'=>$img_url));
					}
				}
				ajaxResult('100','添加成功');
			}else{
				ajaxResult('-1','添加失败');
			}
		}
	}
	
	/*
	*删除机构
	*/
	public function del_mechanism(){
		$post = request()->post();
		if(!empty($post['mechanism_id'])){
			Db::name('mechanism')->delete($post['mechanism_id']);
			ajaxResult('100');
		}else{
			ajaxResult('-1','丢失参数');
		}
	}
	
	
}
