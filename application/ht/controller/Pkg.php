<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;

/**
 * 上传
 */
 
class Pkg extends Controller
{
   
   public function upload(){
		
		$pkg = new \helper\Pkg();
		$obj_url = $pkg->upload($_FILES);

		if(file_exists('.'.$mb_obj_url)){
			ajaxResult('100',$obj_url);
		}else{
			ajaxResult('0','上传失败');
		}
   }
  
}
