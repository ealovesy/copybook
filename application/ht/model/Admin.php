<?php
namespace app\ht\model;
use think\Model;
use think\Db;
use think\Session;

class Admin extends Base
{
	// �Զ���֤
    protected $_validate=array(
        array('username','require','�û�������',0,'',3), // ��֤�ֶα���
    );

    // �Զ����
    protected $_auto=array(
        array('password','md5',1,'function') , // ��password�ֶ���������ʱ��ʹmd5��������
        array('register_time','time',1,'function'), // ��date�ֶ���������ʱ��д�뵱ǰʱ���
    );

    /**
     * ɾ������
     * @param   array   $map    where���������ʽ
     * @return  boolean         �����Ƿ�ɹ�
     */
    public function deleteData($map){
        die('��ֹɾ���û�');
    }
    
    function getSaltByName($username){
    	$u = Db::name('admin')->where('username',$username)->select();
    	return empty($u[0]['salt']) ? '' : $u[0]['salt'];
    }
    
    //��������
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