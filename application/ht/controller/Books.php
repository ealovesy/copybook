<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;
use helper\Page;
use PHPExcel_IOFactory;

/**
 * 图书管理
 */
 
class Books extends uu
{
	function index(){
		
		$where = array();
		
		//查询总数total
		$total = Db::name('books')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 图书AJAX列表
	 */
	public function getPage(){
		$post = request()->post();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where = array();
		
		if(isset($post['title'])){
			$url.= '?title='.$post['title'];
			$where['title'] = array('like', "%".$post['title']."%");
		}
		
		$books = Db::name('books')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('books')->where($where)->count();
    	
    	tableJson($books, $total);
	}
	
	/*
	*年级列表
	*/
	function grade(){
		
		$where = array();
		
		//查询总数total
		$total = Db::name('grade')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
	/**
	 * 年级AJAX列表
	 */
	public function getGrade(){
		$post = request()->post();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where = array();
		
		if(isset($post['name'])){
			$url.= '?name='.$post['name'];
			$where['name'] = array('like', "%".$post['name']."%");
		}
		
		$books = Db::name('grade')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('grade')->where($where)->count();
    	
    	tableJson($books, $total);
	}
	
	/*
	*添加/编辑年级
	*/
	function edit_grade(){
		if(request()->isPost()){
			$grade_id = request()->post('grade_id');
			if(!empty($grade_id)){
				$grade = Db::name('grade')->where('id', $grade_id)->find();
				$this->assign('grade', $grade);
			}
			echo $this->fetch();
		}
	}
	
	/*
	*添加/编辑年级表单提交
	*/
	function submit_grade(){
		if(request()->isPost()){
			
			if(empty(request()->post('name'))) ajaxResult('0', '请输入年级');
			
			if(!empty(request()->post('grade_id'))){
				Db::name('grade')->where('id', request()->post('grade_id'))->update(array('name'=>request()->post('name')));
				ajaxResult('100');
			}elseif(!empty(request()->post('name'))){
				Db::name('grade')->insert(array('name'=>request()->post('name')));
				ajaxResult('100');
			}
		}
	}
	
	/*
	*删除年级
	*/
	function del_grade(){
		if(request()->isPost()){
			
			if(empty(request()->post('grade_id'))) ajaxResult('0', '丢失参数');
			
			Db::name('grade')->delete(request()->post('grade_id'));
			ajaxResult('100');
		}
	}

  	/*
	*添加图书
	*/
	public function edit(){
		$get = request()->get();
		
		//查询图书分类
		$category = Db::name('category')->select();
		//查询年级
		$grade = Db::name('grade')->select();
		
		if(!empty($get['book_id'])){
			$book = Db::name('books')->where('id', $get['book_id'])->find();
			$data['book'] = $book;
		}
		
		$data['category'] = $category;
		$data['grade'] = $grade;
		
		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*编辑图书
	*/
	public function do_edit(){
		$post = request()->post();
		$data = array(
			'category_id' => $post['category_id'],
			'grade_id' => $post['grade_id'],
			'title' => $post['title'],
		    'description' => $post['description'],
		    'author' => $post['author'],
		    'publisher' => $post['publisher'],
		    'isbn' => $post['isbn'],
		    'defineCode' => $post['defineCode'],
		    'barCode' => $post['barCode'],
		    'price' => $post['price'],
		    'buyUrl' => $post['buyUrl'],
		    'img' => $post['img'],
		);
		if(!empty($post['book_id'])){
			model('books')->where('id', $post['book_id'])->update($data);
			ajaxResult('100','编辑成功');
		}else{
			$data['create_at'] = time();
			$res = model('books')->addData($data);
			if($res>1){
				ajaxResult('100','添加成功');
			}else{
				ajaxResult('-1','添加失败');
			}
		}
	}
	
	/*
	*删除图书
	*/
	public function del_book(){
		$post = request()->post();
		if(!empty($post['book_id'])){
			Db::name('books')->delete($post['book_id']);
			ajaxResult('100');
		}else{
			ajaxResult('-1','丢失参数');
		}
	}
	
	/*
	*上架图书
	*/
	public function online_book(){
		$post = request()->post();
		if(!empty($post['book_id'])){
			Db::name('books')->where('id', $post['book_id'])->update(array('status'=>1));
			ajaxResult('100');
		}else{
			ajaxResult('-1','丢失参数');
		}
	}
	
	/*
	*下架图书
	*/
	public function offline_book(){
		$post = request()->post();
		if(!empty($post['book_id'])){
			Db::name('books')->where('id', $post['book_id'])->update(array('status'=>0));
			ajaxResult('100');
		}else{
			ajaxResult('-1','丢失参数');
		}
	}
	
	/*
	*单元列表
	*/
	function unit(){
		$where['book_id'] = request()->get('book_id');
		
		//查询总数total
		$total = Db::name('unit')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    		'book_id' => $where['book_id']
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 单元AJAX列表
	 */
	public function getUnit(){
		$post = request()->post();$get = request()->get();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where['book_id'] = $post['book_id'];
		
		if(isset($post['title'])){
			$url.= '?title='.$post['title'];
			$where['title'] = array('like', "%".$post['title']."%");
		}
		
		$books = Db::name('unit')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('unit')->where($where)->count();
    	
    	tableJson($books, $total);
	}
	
	/*
	*添加/编辑单元
	*/
	function edit_unit(){
		if(request()->isPost()){
			$unit_id = request()->post('unit_id');
			$book_id = request()->post('book_id');
			if(!empty($unit_id)){
				$unit = Db::name('unit')->where('id', $unit_id)->find();
				$this->assign('unit', $unit);
			}
			if(!empty($book_id)){
				$this->assign('book_id', $book_id);
			}
			echo $this->fetch();
		}
	}
	
	/*
	*添加/编辑单元表单提交
	*/
	function submit_unit(){
		if(request()->isPost()){
			
			if(empty(request()->post('unit_num'))) ajaxResult('0', '请输入单元');
			if(empty(request()->post('title'))) ajaxResult('0', '请输入单元标题');
			if(empty(request()->post('book_id'))) ajaxResult('0', '丢失参数');
			
			if(!empty(request()->post('unit_id'))){
				Db::name('unit')->where('id', request()->post('unit_id'))->update(array('title'=>request()->post('title'), 'unit_num'=>request()->post('unit_num')));
				ajaxResult('100');
			}elseif(!empty(request()->post('title'))){
				Db::name('unit')->insert(array('book_id'=>request()->post('book_id'),'title'=>request()->post('title'), 'unit_num'=>request()->post('unit_num')));
				ajaxResult('100');
			}
		}
	}
	
	/*
	*删除单元
	*/
	function del_unit(){
		if(request()->isPost()){
			
			if(empty(request()->post('unit_id'))) ajaxResult('0', '丢失参数');
			
			Db::name('unit')->delete(request()->post('unit_id'));
			ajaxResult('100');
		}
	}
	
	/*
	*单元页码列表
	*/
	function unit_page(){
		$where['unit_id'] = request()->get('unit_id');
		
		//查询总数total
		$total = Db::name('unit_page')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    		'unit_id' => $where['unit_id'],
    		'book_id' => request()->get('book_id')
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 单元页码AJAX列表
	 */
	public function getUnitPage(){
		$post = request()->post();$get = request()->get();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where['unit_id'] = $post['unit_id'];
		
		if(isset($post['title'])){
			$url.= '?title='.$post['title'];
			$where['title'] = array('like', "%".$post['title']."%");
		}
		
		$books = Db::name('unit_page')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('unit_page')->where($where)->count();
    	
    	tableJson($books, $total);
	}
	
	/*
	*添加/编辑单元页码
	*/
	function edit_unit_page(){
		if(request()->isPost()){
			$unit_page_id = request()->post('unit_page');
			$unit_id = request()->post('unit_id');
			if(!empty($unit_page_id)){
				$unit_page = Db::name('unit_page')->where('id', $unit_page_id)->find();
				$this->assign('unit_page', $unit_page);
			}
			if(!empty($unit_id)){
				$this->assign('unit_id', $unit_id);
			}
			echo $this->fetch();
		}
	}
	
	/*
	*添加/编辑单元页码表单提交
	*/
	function submit_unit_page(){
		if(request()->isPost()){
			
			if(empty(request()->post('title'))) ajaxResult('0', '请输入单元页码');
			if(empty(request()->post('unit_id'))) ajaxResult('0', '丢失参数');
			
			if(!empty(request()->post('unit_page_id'))){
				Db::name('unit_page')->where('id', request()->post('unit_page_id'))->update(array('title'=>request()->post('title')));
				ajaxResult('100');
			}elseif(!empty(request()->post('unit_id'))){
				Db::name('unit_page')->insert(array('unit_id'=>request()->post('unit_id'),'title'=>request()->post('title')));
				ajaxResult('100');
			}
		}
	}
	
	/*
	*删除单元页码单元页码
	*/
	function del_unit_page(){
		if(request()->isPost()){
			
			if(empty(request()->post('unit_page_id'))) ajaxResult('0', '丢失参数');
			
			Db::name('unit_page')->delete(request()->post('unit_page_id'));
			ajaxResult('100');
		}
	}
	
	/*
	*词汇内容列表
	*/
	function unit_data(){
		$where['page_id'] = request()->get('page_id');
		
		//查询总数total
		$total = Db::name('datas')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    		'unit_id' => request()->get('unit_id'),
    		'page_id' => request()->get('page_id'),
    		'book_id' => request()->get('book_id')
    	);
    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 词汇AJAX列表
	 */
	public function getUnitData(){
		$post = request()->post();$get = request()->get();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where['page_id'] = $post['page_id'];
		
		if(isset($post['title'])){
			$url.= '?title='.$post['title'];
			$where['title'] = array('like', "%".$post['title']."%");
		}
		
		$books = Db::name('datas')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();
		
		$total = Db::name('datas')->where($where)->count();
    	
    	tableJson($books, $total);
	}
	
	/*
	*编辑单元内容
	*/
	public function edit_unit_data(){
		$get = request()->get();
		$unit_id = request()->get('unit_id');
		$book_id = request()->get('book_id');
		$page_id = request()->get('page_id');
		
		$datas = Db::name('datas')->where('id', $get['datas_id'])->find();
		//查询当前图书类型以及页码
		$book = Db::name('books')->where('id', $book_id)->find();
		$unit = Db::name('unit')->where('id', $unit_id)->find();
		$category = Db::name('category')->where('id', $book['category_id'])->find();
		$grade = Db::name('grade')->where('id', $book['grade_id'])->find();
		$unit_page = Db::name('unit_page')->where('id', $page_id)->find();
		
		$data = array(
    		'unit_id' => $unit_id,
    		'page_id' => $page_id,
    		'book_id' => $book_id,
    		'unit_page' => $unit_page,
    		'category' => $category,
    		'unit' => $unit,
    		'book' => $book,
    		'grade' => $grade,
    		'datas' => $datas
		);
		
		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*添加/编辑单元内容表单提交
	*/
	function submit_unit_data(){
		if(request()->isPost()){
			$post = request()->post();
			
			if(empty($post['type'])) ajaxResult('0', '请选择类型');
			if(empty($post['title'])) ajaxResult('0', '请输入内容');
			if(empty($post['shiyi'])) ajaxResult('0', '请输入释义');
			if(empty($post['book_id'])) ajaxResult('0', '丢失参数');
			if(empty($post['unit_id'])) ajaxResult('0', '丢失参数');
			
			$data = array(
				'type' => $post['type'],
				'book_id' => $post['book_id'],
				'unit_id' => $post['unit_id'],
				'title' => $post['title'],
				'shiyi' => $post['shiyi'],
			    'yinbiao' => $post['yinbiao'],
			    'yinbiao_audio' => $post['yinbiao_audio'],
			    'example' => $post['example'],
			    'translate' => $post['translate'],
			    'example_audio' => $post['example_audio'],
			    'page_id' => $post['page_id'],
			);
			if(!empty($post['datas_id'])){
				model('datas')->where('id', $post['datas_id'])->update($data);
				ajaxResult('100','编辑成功');
			}else{
				$res = model('datas')->addData($data);
				if($res>1){
					ajaxResult('100','添加成功');
				}else{
					ajaxResult('-1','添加失败');
				}
			}
		}
	}
	
	/*
	*批量导入图书
	*/
	public function book_import(){
		$book_id = request()->get('book_id');
		$this->assign('book_id', $book_id);
		return $this->fetch();
	}
	
	public function excel_import(){
		$post = request()->post();
		
		if(empty($post['book_id'])) ajaxResult('-1', '丢失book_id');
		
		vendor("PHPExcel.PHPExcel");//导入PHPExcal类库
        //不加\会提示notfound 原因为引入命名空间
        $objPHPExcel = new \PHPExcel();//生成PHPExcel类实例
        
        $suffix = substr(strrchr($_FILES['units']['name'], '.'), 1);
		if($suffix!='xls' && $suffix!='xlsx') ajaxResult('-1', '文件格式不允许');
        
		$pkg = new \helper\Pkg();
		$excel_tmp = $pkg->excel($_FILES, 'units');
		
		
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
        	
        	//单元去重
        	foreach((array)$data as $key=>$dd){
        		$uniq_unit[$dd[0]]['unit_num'] = trim($dd[0]);
        		$uniq_unit[$dd[0]]['title'] = $dd[1];
        		$uniq_unit[$dd[0]]['unit_page'] = $dd[2];
        		$data[$key]['unit_num'] = trim($dd[0]);
        		$data[$key]['unit_page'] = trim($dd[2]);
        	}
			
        	//获取当前excel里的所有单元id
        	foreach($uniq_unit as $key=>$unit){
        		$this_unit = Db::name('unit')->where(array('book_id'=>$post['book_id'], 'unit_num'=>$unit['unit_num']))->find();
        		if(!empty($this_unit)){
        			$uniq_unit[$key]['unit_id'] = $this_unit['id'];
        		}else{
        			$uniq_unit[$key]['unit_id'] = model('unit')->addData(array('book_id'=>$post['book_id'], 'unit_num'=>$unit['unit_num'], 'title'=>$unit['title']));
        		}
        	}
			
        	//获取当前excel里的所有单元页码
        	foreach($uniq_unit as $key=>$unit){
        		$this_page = Db::name('unit_page')->where(array('unit_id'=>$unit['unit_id'], 'title'=>$unit['unit_page']))->find();
        		if(!empty($this_page)){
        			$uniq_unit[$key]['unit_page_id'] = $this_page['id'];
        		}else{
        			$uniq_unit[$key]['unit_page_id'] = model('unit_page')->addData(array('unit_id'=>$unit['unit_id'], 'title'=>$unit['unit_page']));
        		}
        	}
			
			//计算 单元id 和 页码id
        	foreach($data as $key=>$dd){
        		foreach($uniq_unit as $unit){
        			if(trim($dd[0]) == trim($unit['unit_num'])){
        				$data[$key]['unit_id'] = $unit['unit_id'];
        				$data[$key]['unit_page_id'] = $unit['unit_page_id'];
        			}
        		}
        	}
        	
			foreach($data as $key=>$dd){
				$this_page = Db::name('datas')->where(array('book_id'=>$post['book_id'], 'unit_id'=>$dd['unit_id'], 'page_id'=>$dd['unit_page_id'], 'title'=>trim($dd[4])))->find();
        		if(empty($this_page)){
        			$arr = array(
	        			'book_id' => $post['book_id'],
	        			'unit_id' => $dd['unit_id'],
	        			'type' => $dd[3],
	        			'title' => $dd[4],
	        			'shiyi' => $dd[5],
	        			'yinbiao' => $dd[6],
	        			'example' => $dd[7],
	        			'translate' => $dd[8],
	        			'page_id' => $dd['unit_page_id'],
	        		);
	        		model('datas')->addData($arr);
        		}
			}
			ajaxResult('100');
        }
	}
	
	public function file_import(){
		
		if(!empty($_FILES['file']['name'])){
			$name = $_FILES['file']['name'];
			//计算该音频的路径 图书/单元/页码/(1/2)名字
			$name_arr = explode('-', $name);
			if(count($name_arr) == 4){
				
				$post = request()->post();
				$book_id = request()->get('book_id');
				
				//检查该音频文件的对应单元是否存在
				$unit = Db::name('unit')->where(array('book_id'=>$book_id, 'unit_num'=>$name_arr[0]))->find();
				if(!empty($unit)){
					//检查该音频文件的对应页码是否存在
					$unit_page = Db::name('unit_page')->where(array('unit_id'=>$unit['id'], 'title'=>$name_arr[1]))->find();
					if(!empty($unit_page)){
						$datas = Db::name('datas')->where(array('book_id'=>$book_id, 'unit_id'=>$unit['id'], 'page_id'=>$unit_page['id']))->find();
						if(!empty($datas)){
							
							//开始上传音频
							$book = Db::name('books')->where('id', $book_id)->find();
							$pkg = new \helper\Pkg();
							$obj_url = $pkg->audio($_FILES, 'file', $book['title'].'/'.$unit['unit_num'].'/'.$unit_page['title'], $name_arr[2].'-'.$name_arr[3]);
							if(file_exists('.'.$mb_obj_url)){
								//1：标题 2：例句
								if($name_arr[2]==1){
									Db::name('datas')->where('id', $datas['id'])->update(array('yinbiao_audio'=>$obj_url));//单词
								}elseif($name_arr[2]==2){
									Db::name('datas')->where('id', $datas['id'])->update(array('example_audio'=>$obj_url));//例句
								}
								ajaxResult('100',$obj_url);
							}else{
								ajaxResult('-1','上传失败');
							}
						}else{
							ajaxResult('-1', '词汇内容不存在');
						}
					}else{
						ajaxResult('-1', '页码不存在');
					}
				}else{
					ajaxResult('-1', '单元不存在');
				}
			}else{
				ajaxResult('-1', '文件名称格式有误, 图书-单元-页码-(1/2)音频');
			}
		}else{
			ajaxResult('-1', '没有获取到上传文件');
		}
		
	}
	
	/*
	*单元题库列表
	*/
	function unit_exam(){
		
		$where['unit_id'] = request()->get('unit_id');
		
		//查询总数total
		$total = Db::name('unit_exam')->where($where)->count();
		
    	$data = array(
    		'total' => $total,
    		'unit_id' => request()->get('unit_id'),
    		'book_id' => request()->get('book_id')
    	);

    	$this->assign($data);
		return $this->fetch();
	}
	
    /**
	 * 题库AJAX列表
	 */
	public function getUnitExam(){
		$post = request()->post();$get = request()->get();
		$page = isset($post['page']) ? $post['page'] : 1;
		$limit = isset($post['limit']) ? $post['limit'] : 1;
		$start = ($page - 1) * $limit;
		
		$where['unit_id'] = $post['unit_id'];
		
		if(isset($post['name'])){
			$url.= '?name='.$post['name'];
			$where['name'] = array('like', "%".$post['name']."%");
		}
		
		$unit_exam = Db::name('unit_exam')
				->where($where)
				->order('id desc')
				->limit($start, $limit)
				->select();

		$total = Db::name('unit_exam')->where($where)->count();
    	
    	tableJson($unit_exam, $total);
	}
	
	/*
	*编辑单元题库内容
	*/
	public function edit_unit_exam(){

		$get = request()->get();
		$unit_id = request()->get('unit_id');
		$book_id = request()->get('book_id');
		$unit_exam_id = request()->get('unit_exam_id');
		
		$unit_exam = Db::name('unit_exam')->where('id', $unit_exam_id)->find();
		$unit_exam_answer = Db::name('unit_exam_answer')->where('exam_id', $unit_exam_id)->order('id asc')->select();
		$answer_ids = explode(',', $unit_exam['answer_id']);
		
		$data = array(
    		'unit_id' => $unit_id,
    		'unit_exam_id' => $unit_exam_id,
    		'book_id' => $book_id,
    		'unit_exam' => $unit_exam,
    		'unit_exam_answer' => $unit_exam_answer,
    		'answer_ids' => $answer_ids
		);
		
		$this->assign($data);
		return $this->fetch();
	}
	
	/*
	*添加/编辑单元题库表单提交
	*/
	function submit_unit_exam(){
		if(request()->isPost()){
			$post = request()->post();
			
			if(empty($post['type'])) ajaxResult('0', '请选择类型');
			if(empty($post['unit_id'])) ajaxResult('0', '丢失参数');
			
			$audio_url = '';
			if($post['type']==3){
				if(empty($post['audio_url'])) ajaxResult('0', '请上传音频文件');
				$audio_url = $post['audio_url'];
			}
			
			$data = array(
				'type' => $post['type'],
				'unit_id' => $post['unit_id'],
				'type' => $post['type'],
				'name' => $post['name'],
			    'audio_url' => $audio_url,
			    'answer_id' => $answer_id
			);
			
			if(!empty($post['unit_exam_id'])){
				model('unit_exam')->where('id', $post['unit_exam_id'])->update($data);
				
				foreach((array)$post['answer'] as $key=>$ans){
					$answers[$key]['content'] = $ans;
					foreach((array)$post['answer_true'] as $key2=>$anstrue){
						if($key==$key2){
							$answers[$key]['answer'] = $anstrue;
						}
					}
				}
				Db::name('unit_exam_answer')->where('exam_id', $post['unit_exam_id'])->delete();
				foreach((array)$answers as $an){
					$res_id = model('unit_exam_answer')->addData(array('exam_id'=>$post['unit_exam_id'], 'name'=>$an['content']));
					if(!empty($an['answer'])){
						$answer_ids[] = $res_id;
					}
				}
				$answer_ids = implode(',', $answer_ids);
				model('unit_exam')->where('id', $post['unit_exam_id'])->update(array('answer_id'=>$answer_ids));
				
				ajaxResult('100','编辑成功');
			}else{
				$exam_id = model('unit_exam')->addData($data);
				if($exam_id>1){
					
					foreach((array)$post['answer'] as $key=>$ans){
						$answers[$key]['content'] = $ans;
						foreach((array)$post['answer_true'] as $key2=>$anstrue){
							if($key==$key2){
								$answers[$key]['answer'] = $anstrue;
							}
						}
					}
					foreach((array)$answers as $an){
						$res_id = model('unit_exam_answer')->addData(array('exam_id'=>$exam_id, 'name'=>$an['content']));
						if(!empty($an['answer'])){
							$answer_ids[] = $res_id;
						}
					}
					$answer_ids = implode(',', $answer_ids);
					model('unit_exam')->where('id', $exam_id)->update(array('answer_id'=>$answer_ids));
					
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
	public function exam_import(){
		$book_id = request()->get('book_id');
		$this->assign('book_id', $book_id);
		return $this->fetch();
	}
	
	/*
	*处理导入的题库
	*/
	public function exam_import_submit(){
		$post = request()->post();
		if(empty($post['book_id'])) ajaxResult('-1', '丢失book_id');
		
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
					
					//判断是否有该题目，避免重复提交
        			$has_this_exam = Db::name('unit_exam')->where(array('unit_id'=>$dd[0], 'type'=>$dd[1], 'name'=>trim($dd[2])))->count();
        			if($has_this_exam>0) continue;
        			
	    			$arr = array(
	        			'unit_id' => $dd[0],
	        			'type' => $dd[1],
	        			'name' => trim($dd[2])
	        		);
	        		$exam_id = model('unit_exam')->addData($arr);
		        	if($exam_id>0){
		        		//拆分答案
						$answer_arr = explode('|', $dd[3]);
						
		        		//导入答案
		        		foreach((array)$answer_arr as $ans){
		        			
		        			//判断是否有该题目的答案，避免重复提交
		        			$has_this_answer = Db::name('unit_exam_answer')->where(array('exam_id'=>$exam_id, 'name'=>trim($ans)))->count();
		        			if($has_this_answer>0) continue;
		        			
		        			$arr = array(
								'exam_id' => $exam_id,
			        			'name' => trim($ans)
			        		);
			        		$answer_id = model('unit_exam_answer')->addData($arr);
			        		if(trim($dd[4]) == trim($ans)){
			        			$answer_id_arr[] = $answer_id;
			        		}
		        		}
		        		if(count($answer_id_arr) > 0){
		        			//给题目附正确值
		        			Db::name('unit_exam')->where('id', $exam_id)->update(array('answer_id'=>implode(',', $answer_id_arr)));
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
	
	function delEmpty($arr){
		foreach($arr as $k=>$v){
			if (!empty($v[1])) {
				$new[$k] = $v;  
			}
		}
		//重新排序
		return $new;
	}
	
}
