<?php
namespace helper;

class Util{
	
	public function toIdArray($arr, $id='id'){
		if(count($arr) > 0){
			$re = array();
			foreach($arr as $key=>$row){
				if ($id && isset($row[$id]) && $row[$id])
					$re[$row[$id]] = $row;
				else
					$re[] = $row;
			}
			return $re;
		}
	}
	
	//下载指定文件的方法  
    public function download($file_name, $file_path){  
        header("Content-type:text/html;charset=utf-8");
        
        $file_path = iconv('UTF-8','GBK',$file_path);
        
        if(!file_exists($file_path)){  
            echo "没有该文件文件.";   
            return ;   
        }  
        if(!file_exists($file_path)){  
            echo "没有该文件文件";   
            return ;   
        }  
        $fp=fopen($file_path,"r");  
        $file_size=filesize($file_path);
        //下载文件需要用到的头  
        Header("Content-type: application/octet-stream");  
        Header("Accept-Ranges: bytes");   
        Header("Accept-Length:".$file_size);   
        Header("Content-Disposition: attachment; filename=".$file_name);   
        $buffer=1024;   
        $file_count=0;   
        //向浏览器返回数据   
        while(!feof($fp) && $file_count<$file_size){   
            $file_con=fread($fp,$buffer);   
            $file_count+=$buffer;   
            echo $file_con;   
        }  
        fclose($fp);  
    }
	

}