<?php
namespace app\ht\controller;
use think\Controller;
use think\Db;

class Index extends uu
{
	public function index(){
		return $this->fetch('index/index');
	}
	
	public function console(){
		return $this->fetch('index/console');
	}
	

}