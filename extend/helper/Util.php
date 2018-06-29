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
	
	//����ָ���ļ��ķ���  
    public function download($file_name, $file_path){  
        header("Content-type:text/html;charset=utf-8");
        
        $file_path = iconv('UTF-8','GBK',$file_path);
        
        if(!file_exists($file_path)){  
            echo "û�и��ļ��ļ�.";   
            return ;   
        }  
        if(!file_exists($file_path)){  
            echo "û�и��ļ��ļ�";   
            return ;   
        }  
        $fp=fopen($file_path,"r");  
        $file_size=filesize($file_path);
        //�����ļ���Ҫ�õ���ͷ  
        Header("Content-type: application/octet-stream");  
        Header("Accept-Ranges: bytes");   
        Header("Accept-Length:".$file_size);   
        Header("Content-Disposition: attachment; filename=".$file_name);   
        $buffer=1024;   
        $file_count=0;   
        //���������������   
        while(!feof($fp) && $file_count<$file_size){   
            $file_con=fread($fp,$buffer);   
            $file_count+=$buffer;   
            echo $file_con;   
        }  
        fclose($fp);  
    }
	

}