<?php
/**
 * Created by PhpStorm.
 * User: luowei
 * Date: 2017/9/19
 * Time: 10:54
 */
namespace helper;
class WebAPIHelper
{
    private $secretKey = "SECUSOFT1702";
    private $appid = '0';
    public $urlprfix = 'http://192.168.10.1/PrintAPI/';//"http://47.92.89.117/PrintAPI/";//
    public $res_info =  array('http_code'=>200); //response info array,include http_code...
    private $open_id = "";
    private $server_id = "";
    private $helper = null;
    function __construct($server_id,$openid)
    {
        if($this->GetRequestMode($server_id) == "passive")
        {
            return $this->helper = new WebAPIHelperPassiveMode($server_id,$openid);
        }
        else
        {
            return $this->helper = new WebAPIHelperActiveMode($server_id,$openid);
        }
    }


    public function GetRequestMode($server_id)
    {
        $server_uuid = DBHelper::GetServerUUID($server_id);
        if($server_uuid < 0)
        {
            return "active";
        }
        else
        {
            return "passive";
        }
    }
    public function Login($account, $password)
    {
        return $this->helper->Login($account,$password);
    }

    public function GetUserInfo($user_id)
    {
        return $this->helper->GetUserInfo($user_id);
    }
    public function Logout($user_id)
    {

    }

    public function GetUserPrintTask($user_id,$refresh_cloud=false)
    {
        return $this->helper->GetUserPrintTask($user_id,$refresh_cloud);
    }
    public function GetUserPrintTaskWithTaskId($user_id,$task_id_list)
    {
        return $this->helper->GetUserPrintTaskWithTaskId($user_id,$task_id_list);
    }
    public function AddUserPrintTask($user_id,$task_info,$filename)
    {
        return $this->helper->AddUserPrintTask($user_id,$task_info,$filename);
    }

    public function CancelUserPrintTask($task_id_list)
    {
        return $this->helper->CancelUserPrintTask($task_id_list);
    }

    public function SendPrintTaskToPrinter($task_id_list,$printer_name)
    {
        return $this->helper->SendPrintTaskToPrinter($task_id_list,$printer_name);
    }
}