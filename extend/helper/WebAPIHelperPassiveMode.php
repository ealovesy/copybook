<?php
/**
 * Created by PhpStorm.
 * User: luowei
 * Date: 2017/9/19
 * Time: 10:54
 */
namespace helper;
use think\Db;

class WebAPIHelperPassiveMode
{
    public $res_info = array('http_code'=>200); //response info array,include http_code...
    private $open_id = "";
    private $server_id = "";
    function __construct($server_id,$openid)
    {
        if(!$server_id)
        {
            $server_id = DBHelper::GetServerID($openid);
        }
        //$server_ip = DBHelper::GetServerIP($server_id);
        if($server_id)
        {
            $this->open_id =$openid;
            $this->server_id = $server_id;
        }

    }

    public function Login($account, $password)
    {
        return DBHelper::Login($this->open_id,$this->server_id,$account,md5($password));
    }
    public function GetUserInfo($user_id)
    {
        return DBHelper::GetUserInfo($this->open_id);
    }


    public function Logout($user_id)
    {

    }

    public function GetUserPrintTask($user_id,$refresh_cloud=false)
    {
        $result =  DBHelper::GetUserPrintTask($this->open_id,$this->server_id,$user_id,$refresh_cloud);
        return $result;
    }
    public function GetUserPrintTaskWithTaskId($user_id,$task_id_list)
    {
        $obj_tasks = DBHelper::GetUserPrintTaskWithTaskId($this->server_id,$user_id,$task_id_list);
        return $obj_tasks;
    }
    public function AddUserPrintTask($user_id,$task_info,$filename)
    {
        return DBHelper::AddUserPrintTask($this->open_id,$user_id,$task_info,$filename);
    }

    public function CancelUserPrintTask($task_id_list)
    {
        return DBHelper::CancelUserPrintTask($this->open_id,$task_id_list);
    }

    public function SendPrintTaskToPrinter($task_id_list,$printer_name)
    {
        return DBHelper::SendPrintTaskToPrinter($this->open_id,$task_id_list,$printer_name);
    }
}