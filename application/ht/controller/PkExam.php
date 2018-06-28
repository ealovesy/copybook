<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use helper\Page;
use PHPExcel_IOFactory;

/**
 * pk答题管理
 */
 
class PkExam extends uu
{

	
	/*
	*pk题库列表
	*/
	function index(){
		
		//查询总数total
		$total = Db::name('pk_exam')->count();
		
    	$data = array(
    		'total' => $total
    	);
		
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * pk题库AJAX列表
	 */
	public function getPkExam(){
		$post = request()->post();$get = request()->get();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		if(isset($post['name'])){
			$url.= '?name='.$post['name'];
			$where['name'] = array('like', "%".$post['name']."%");
		}
		
		$pk_exam = Db::name('pk_exam')
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('pk_exam')->where($where)->count();
    	
    	tableJson($pk_exam, $total);
	}
	
	/*
	*编辑单元题库内容
	*/
	public function edit_pk_exam(){

		$get = request()->get();
		$pk_exam_id = request()->get('pk_exam_id');
		
		$pk_exam = Db::name('pk_exam')->where('id', $pk_exam_id)->find();
		$pk_exam_answer = Db::name('pk_exam_answer')->where('pk_exam_id', $pk_exam_id)->order('id asc')->select();
		$answer_ids = explode(',', $pk_exam['answer_id']);
		
		$data = array(
    		'pk_exam_id' => $pk_exam_id,
    		'pk_exam' => $pk_exam,
    		'pk_exam_answer' => $pk_exam_answer,
    		'answer_ids' => $answer_ids
		);
		
		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*添加/编辑单元题库表单提交
	*/
	function submit_pk_exam(){
		if(request()->isPost()){
			$post = request()->post();
			
			if(empty($post['type'])) ajaxResult('0', '请选择类型');
			
			$audio_url = '';
			if($post['type']==3){
				if(empty($post['audio_url'])) ajaxResult('0', '请上传音频文件');
				$audio_url = $post['audio_url'];
			}
			
			$data = array(
				'type' => $post['type'],
				'difficult' => $post['difficult'],
				'name' => $post['name'],
			    'audio_url' => $audio_url
			);
			
			if(!empty($post['pk_exam_id'])){
				model('pk_exam')->where('id', $post['pk_exam_id'])->update($data);
				
				foreach((array)$post['answer'] as $key=>$ans){
					$answers[$key]['content'] = $ans;
					foreach((array)$post['answer_true'] as $key2=>$anstrue){
						if($key==$key2){
							$answers[$key]['answer'] = $anstrue;
						}
					}
				}
				Db::name('pk_exam_answer')->where('pk_exam_id', $post['pk_exam_id'])->delete();
				foreach((array)$answers as $an){
					$res_id = model('pk_exam_answer')->addData(array('pk_exam_id'=>$post['pk_exam_id'], 'name'=>$an['content']));
					if(!empty($an['answer'])){
						$answer_ids[] = $res_id;
					}
				}
				$answer_ids = implode(',', $answer_ids);
				model('pk_exam')->where('id', $post['pk_exam_id'])->update(array('answer_id'=>$answer_ids));
				
				ajaxResult('100','编辑成功');
			}else{
				$pk_exam_id = model('pk_exam')->addData($data);
				if($pk_exam_id>1){
					
					foreach((array)$post['answer'] as $key=>$ans){
						$answers[$key]['content'] = $ans;
						foreach((array)$post['answer_true'] as $key2=>$anstrue){
							if($key==$key2){
								$answers[$key]['answer'] = $anstrue;
							}
						}
					}
					foreach((array)$answers as $an){
						$res_id = model('pk_exam_answer')->addData(array('pk_exam_id'=>$pk_exam_id, 'name'=>$an['content']));
						if(!empty($an['answer'])){
							$answer_ids[] = $res_id;
						}
					}
					$answer_ids = implode(',', $answer_ids);
					model('pk_exam')->where('id', $pk_exam_id)->update(array('answer_id'=>$answer_ids));
					
					ajaxResult('100','添加成功');
				}else{
					ajaxResult('-1','添加失败');
				}
			}
		}
	}
	
	/*
	*批量导入题库
	*/
	public function pk_exam_import(){
		return $this->fetch();
	}
	/*
	*处理导入的题库
	*/
	public function pk_exam_import_submit(){
		$post = request()->post();
		
		vendor("PHPExcel.PHPExcel");//导入PHPExcal类库
        $objPHPExcel = new \PHPExcel();//生成PHPExcel类实例
        
		$pkg = new \helper\Pkg();
		$excel_tmp = $pkg->excel($_FILES, 'exams');
		
        // 判断文件是什么格式
	    $type = pathinfo($excel_tmp); 
	    $type = strtolower($type["extension"]);
	    $type=$type==='csv' ? $type : 'Excel5';
	    ini_set('max_execution_time', '0');
	    Vendor('PHPExcel.PHPExcel');
	    // 判断使用哪种格式
	    $objReader = PHPExcel_IOFactory::createReader($type);
	    $objPHPExcel = $objReader->load(iconv("UTF-8", "GBK", $_SERVER['DOCUMENT_ROOT'].$excel_tmp)); 
	    $sheet = $objPHPExcel->getSheet(0); 
	    // 取得总行数 
	    $highestRow = $sheet->getHighestRow();
	    // 取得总列数      
	    $highestColumn = $sheet->getHighestColumn(); 
	    //循环读取excel文件,读取一条,插入一条
	    $data=array();
	    //从第一行开始读取数据
	    for($j=1;$j<=$highestRow;$j++){
	        //从A列读取数据
	        for($k='A';$k<=$highestColumn;$k++){
	            // 读取单元格
	            $data[$j][]=$objPHPExcel->getActiveSheet()->getCell("$k$j")->getValue();
	        } 
	    }  
	    
        if(!empty($data[2])){
        	unset($data[1]);
        	
        	//清除excel中空数据
        	$data = $this->delEmpty($data);
        	
        	Db::startTrans();
        	try{
	        	//先导入题目
				foreach($data as $key=>$dd){
					//将中文难度转成数字
					$dd[1] = ($dd[1]=='简单') ? 1 : (($dd[1]=='中等') ? 2 : (($dd[1]=='困难') ? 3 : 0));
					if(!$dd[1]) continue;
					
					//判断是否有该题目，避免重复提交
        			$has_this_exam = Db::name('pk_exam')->where(array('type'=>$dd[0], 'difficult'=>$dd[1], 'name'=>trim($dd[2])))->count();
        			if($has_this_exam>0) continue;
        			
	    			$arr = array(
	        			'type' => $dd[0],
	        			'difficult' => $dd[1],
	        			'name' => trim($dd[2])
	        		);
	        		$pk_exam_id = model('pk_exam')->addData($arr);
		        	if($pk_exam_id>0){
		        		//拆分答案
						$answer_arr = explode('|', $dd[3]);
						
		        		//导入答案
		        		foreach((array)$answer_arr as $ans){
		        			
		        			//判断是否有该题目的答案，避免重复提交
		        			$has_this_answer = Db::name('pk_exam_answer')->where(array('pk_exam_id'=>$pk_exam_id, 'name'=>trim($ans)))->count();
		        			if($has_this_answer>0) continue;
		        			
		        			$arr = array(
								'pk_exam_id' => $pk_exam_id,
			        			'name' => trim($ans)
			        		);
			        		$answer_id = model('pk_exam_answer')->addData($arr);
			        		if(trim($dd[4]) == trim($ans)){
			        			$answer_id_arr[] = $answer_id;
			        		}
		        		}
		        		if(count($answer_id_arr) > 0){
		        			//给题目附正确值
		        			Db::name('pk_exam')->where('id', $pk_exam_id)->update(array('answer_id'=>implode(',', $answer_id_arr)));
		        		}
		        	}
				}
				Db::commit();
				ajaxResult('100', '导入成功');
			}catch(Exception $e){
				Db::rollback();
			}
			
        }
	}
	
	/*
	*删除题目
	*/
	public function del_pk_exam(){
		$post = request()->post();
		if($post['pk_exam_id']){
			$pk_exam = Db::name('pk_exam')->where('id', $post['pk_exam_id'])->find();
			Db::name('pk_exam_answer')->where(array('pk_exam_id'=>array('in', $pk_exam['answer_id'])))->delete();
			Db::name('pk_exam')->where('id', $post['pk_exam_id'])->delete();
			ajaxResult('100');
		}else{
			ajaxResult('-1', '丢失参数');
		}
	}
	
	function delEmpty($arr){
		foreach($arr as $k=>$v){
			if (!empty($v[1])) {
				$new[$k] = $v;  
			}
		}
		//重新排序
		return $new;
	}
	
	/*
	*下载文件
	*/
	function download(){
		$get = request()->get();
		if(empty($get['file_name']) || empty($get['file_url'])) $this->error('缺少请求参数');
		$util = new \helper\Util();
		$util->download($get['file_name'], $_SERVER['DOCUMENT_ROOT'].'/'.$get['file_url']);
	}
	
	
	
}
