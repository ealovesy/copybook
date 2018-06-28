<?php
namespace app\ht\model;
use think\Model;
use think\Db;
use think\Session;

class Admin extends Base
{
	// 自动验证
    protected $_validate=array(
        array('username','require','用户名必须',0,'',3), // 验证字段必填
    );

    // 自动完成
    protected $_auto=array(
        array('password','md5',1,'function') , // 对password字段在新增的时候使md5函数处理
        array('register_time','time',1,'function'), // 对date字段在新增的时候写入当前时间戳
    );

    /**
     * 删除数据
     * @param   array   $map    where语句数组形式
     * @return  boolean         操作是否成功
     */
    public function deleteData($map){
        die('禁止删除用户');
    }
    
    function getSaltByName($username){
    	$u = Db::name('admin')->where('username',$username)->select();
    	return empty($u[0]['salt']) ? '' : $u[0]['salt'];
    }
    
    //加密密码
    function formatPassword($password, $salt) {
        return md5(md5($password).$salt);
    }
    
    function get_uniqid() {
		do {
		   $uniq = $this->rand_uniqid(6);
		} while (Db::name('admin')->where('salt',$uniq)->select());
		return $uniq;
	}
    
    function rand_uniqid($num = '8') {
	    $in = time();
	    $passKey = rand(0, $in);
		$index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		if($passKey !== null) {
			for($n=0;$n<strlen($index);$n++) $i[] = substr($index,$n,1);
			$passhash = hash('sha256',$passKey);
			$passhash = (strlen($passhash) < strlen($index)) ? hash('sha512',$passKey) : $passhash;
	        for($n=0;$n<strlen($index);$n++) $p[] =  substr($passhash, $n ,1);
			array_multisort($p, SORT_DESC, $i);
	        $index = implode($i);
		}
		$base = strlen($index);
	    if (is_numeric($num)) {
	        $num--;
	        if ($num > 0) $in += pow($base, $num);
	    }
	    $out = "";
	    $t = floor(log($in, $base));
	    for ($t = floor(log($in, $base)); $t >= 0; $t--) {
	        $bcp = bcpow($base, $t);
	        $a   = floor($in / $bcp) % $base;
	        $out = $out . substr($index, $a, 1);
	        $in  = $in - ($a * $bcp);
	    }
	    $out = strrev($out);
		return strtolower($out);
	}
	
	

}