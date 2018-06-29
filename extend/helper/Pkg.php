<?php
namespace helper;

class Pkg{
		
	function upload($files, $name='file') {
		
		//生成目录名称
		$dir_temp = "/pkg/uploads/";
		$dir_time = date("Ymd")."/";
		$upload_dir = $_SERVER['DOCUMENT_ROOT'].'/'.$dir_temp.'/'.$dir_time;
		
		//创建目录
		$upload_dir = iconv("UTF-8", "GBK", $upload_dir);
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                return false;
            }
            @chmod($upload_dir, 0777);
        }
		
		$file_name = iconv("UTF-8", "GBK", $_FILES[$name]["name"]);   //先转换名字为 GBK 编码
        move_uploaded_file($_FILES[$name]["tmp_name"], $upload_dir . $file_name);
		
        return $dir_temp.$dir_time . $_FILES[$name]["name"];
	}
	
	function audio($files, $name='file', $path, $audio_name) {
		//生成目录名称
		$dir_temp = "/pkg/audio/";
		$upload_dir = iconv("UTF-8", "gbk", $_SERVER['DOCUMENT_ROOT'].$dir_temp.$path);
		
		//创建目录
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                return false;
            }
            @chmod($upload_dir, 0777);
        }
        move_uploaded_file($files[$name]["tmp_name"], $upload_dir .'/'. iconv("UTF-8", "gb2312", $audio_name));
        return $dir_temp.$path.'/'.$audio_name;
	}
	
	function excel($files, $name='file') {
		//生成目录名称
		$dir_temp = "/pkg/excel/".date("Ymd")."/";
		$upload_dir = $_SERVER['DOCUMENT_ROOT'].$dir_temp;
		
		//创建目录
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                return false;
            }
            @chmod($upload_dir, 0777);
        }
        move_uploaded_file($files[$name]["tmp_name"],$upload_dir . iconv("UTF-8", "gb2312", $_FILES[$name]["name"]));
        return $dir_temp.$files[$name]["name"];
	}
	
	
}