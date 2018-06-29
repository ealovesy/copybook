<?php
namespace helper;

class Image{
	
	//文字水印
	public function waterMark($imgPath, $outDir, $post){
    	$wimg = imagecreatefromstring(file_get_contents($imgPath));
		$font = 'static/index/fonts/MicroSoftYaHei.ttc';//字体
	    $black = imagecolorallocate($wimg, 255, 255, 255);//字体颜色 RGB
	    $fontSize = 19;   //字体大小
	    $circleSize = 0; //旋转角度
	    
	    //计算文字右下角位置
	    $arr = getimagesize($imgPath);
		$img_width = $arr[0];
		$img_height = $arr[1];
	    
	    //识别码字边距
	    $address = '';
	    $fontarea = imagettfbbox($fontSize, 0, $font, $address);
	    $u_font_width = $fontarea[2] - $fontarea[0];
	    $uniq_left = ($img_width - $u_font_width) - 20;
	    $uniq_top = $img_height - 120;
	    //日期字边距
	    $time_word = date('Y年m月d日 H点i分');
	    $fontarea = imagettfbbox($fontSize, 0, $font, $time_word);
	    $t_font_width = $fontarea[2] - $fontarea[0];
	    $time_left = ($img_width - $t_font_width) - 20;
	    $time_top = $uniq_top + 30;

	    $addr_word = '地址：'.$post['address'];
	    $fontarea = imagettfbbox($fontSize, 0, $font, $addr_word);
	    $a_font_width = $fontarea[2] - $fontarea[0];
	    $addr_left = ($img_width - $a_font_width) - 20;
	    $addr_top = $time_top + 30;
	    //经纬度字边距
	    /*$gps_word = '经度：'.$post['lng'].'  纬度：'.$post['lat'];
	    $fontarea = imagettfbbox($fontSize, 0, $font, $gps_word);
	    $g_font_width = $fontarea[2] - $fontarea[0];
	    $gps_left = ($img_width - $g_font_width) - 20;
	    $gps_top = $addr_top + 30;*/
	    
	    //水印图像########################
		$src_im = imagecreatefromjpeg('static/index/images/black_bg.jpg');
		//水印透明度
		$alpha = 50;
		//合并水印图片
		imagecopymerge($wimg,$src_im,$uniq_left,$uniq_top-26,0,0,$u_font_width, 30, $alpha);
		imagecopymerge($wimg,$src_im,$time_left,$time_top-26,0,0,$t_font_width, 30, $alpha);
		imagecopymerge($wimg,$src_im,$addr_left,$addr_top-26,0,0,$a_font_width, 30, $alpha);
		//imagecopymerge($wimg,$src_im,$gps_left,$gps_top-26,0,0,$g_font_width, 30, $alpha);
		
	    //识别码
	    imagefttext($wimg, $fontSize, $circleSize, $uniq_left, $uniq_top, $black, $font, $uniq_word);
	    //日期
	    imagefttext($wimg, $fontSize, $circleSize, $time_left, $time_top, $black, $font, $time_word);
	    //违法路段
	    imagefttext($wimg, $fontSize, $circleSize, $addr_left, $addr_top, $black, $font, $addr_word);
	    //经纬度
	    //imagefttext($wimg, $fontSize, $circleSize, $gps_left, $gps_top, $black, $font, $gps_word);
	    
		imagejpeg ( $wimg, $outDir, 95);
		imagedestroy($wimg);
	}
	
}