<?php
/**
 * Created by PhpStorm.
 * User: luowei
 * Date: 2017/9/19
 * Time: 10:54
 */
namespace helper;
class WebAPIHelperActiveMode
{
    private $secretKey = "SECUSOFT1702";
    private $appid = '0';
    public $urlprfix = 'http://192.168.10.1/PrintAPI/';//"http://47.92.89.117/PrintAPI/";//
    public $res_info = ""; //response info array,include http_code...

    function __construct($server_id,$openid)
    {
        //return $this->urlprfix = "http://$server_id/PrintDBSQL.WebAPI/";
        if(!$server_id)
        {
            $server_id = DBHelper::GetServerID($openid);
        }
        $server_ip = DBHelper::GetServerIP($server_id);
        if($server_id)
        {
            $this->urlprfix = "http://$server_ip/PrintAPI/";
        }
    }

    private function GetHeaders()
    {
        date_default_timezone_set('PRC');
        $timestamp = date('Y-m-d H:i:s');
        $nonce = rand();
        $plaintext = strtoupper($this->appid . $timestamp . $nonce);
        $signature = hash_hmac('sha256', $plaintext, 'SECUSOFT1702');
        $headers = array('appid:' . $this->appid,
            'timestamp:' . $timestamp,
            'nonce:' . $nonce,
            'signature:' . $signature);
        return $headers;
    }

    private function GET($api, $queryParam = array())
    {
        $c = curl_init($this->urlprfix . $api);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_HTTPGET, true);
        curl_setopt($c, CURLOPT_HTTPHEADER, $this->GetHeaders());
        $fact = curl_exec($c);
        $this->res_info = curl_getinfo($c);
        return $fact;
    }

    private function POST($api, $form_data,$content_type="json")
    {
        $c = curl_init($this->urlprfix . $api);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_POST, true);
        $headers = $this->GetHeaders();
        $content_data = $form_data;
        if($content_type == "json")
        {
            $content_data = json_encode($form_data);
            $headers[] = 'Content-Type:application/json';
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_POSTFIELDS, $content_data);
        $fact = curl_exec($c);
        $this->res_info = curl_getinfo($c);
        return $fact;
    }
    private function PUT($api,$form_data,$content_type="json")
    {
        $c = curl_init($this->urlprfix . $api);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, "PUT"); //定义请求类型，当然那个提交类型那一句就不需要了
        $headers = $this->GetHeaders();
        $content_data = $form_data;
        if($content_type == "json")
        {
            $content_data = json_encode($form_data);
            $headers[] = 'Content-Type:application/json';
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_POSTFIELDS, $content_data);
        $fact = curl_exec($c);
        $this->res_info = curl_getinfo($c);
        return $fact;

    }

    private function DELETE($api,$form_data,$content_type="json")
    {
        $c = curl_init($this->urlprfix . $api);
        curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($c, CURLOPT_CUSTOMREQUEST, "DELETE"); //定义请求类型，当然那个提交类型那一句就不需要了
        $headers = $this->GetHeaders();
        $content_data = $form_data;
        if($content_type == "json")
        {
            $content_data = json_encode($form_data);
            $headers[] = 'Content-Type:application/json';
        }
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_POSTFIELDS, $content_data);
        $fact = curl_exec($c);
        $this->res_info = curl_getinfo($c);
        return $fact;

    }
    public function Login($account, $password)
    {
        $form_data = array('MobilePhone'=>'',
            'Email'=>'',
            'UserName'=>$account,
            'Password'=>md5($password));
        $res =  $this->POST('api/Account/Login',$form_data);
        $status = $this->res_info['http_code'];
        $result = array(
            'status'=>$status,
            'message'=>'',
            'user_info'=>array()
        );
        if ($status == 200)
        {
            $user_info=json_decode($res);
            $my_stuff = array(
                'account'=>$user_info->{'UserName'},
                'user_id'=>$user_info->{'UserID'}
            );
            $result['user_info'] = $my_stuff;
            $result['message'] = '绑定成功';
        }
        else
        {
            $result['message'] = $res;
        }
        return $result;
    }

    public function GetUserInfo($user_id)
    {
        $res = $this->GET('api/Account/UserInfo/'.$user_id);
        $user_info=json_decode($res);
		$my_stuff = array(
				'account'=>null,
				'user_id'=>null
		);
		if($user_info)
		{
			$my_stuff = array(
				'account'=>$user_info->{'UserName'},
				'user_id'=>$user_info->{'UserID'}
				);
		}
        return $my_stuff;
    }

    public function Logout($user_id)
    {

    }

    public function GetUserPrintTask($user_id,$refresh_cloud=false)
    {
        $result =  $this->GET('api/UserPrintTask/'.$user_id);
        $obj_tasks = json_decode($result,true);
        return $obj_tasks;
    }
    public function GetUserPrintTaskWithTaskId($user_id,$task_id_list)
    {
        $result =  $this->GET('api/UserPrintTask/?UserID='.$user_id.'&TaskId='.$task_id_list);
        $obj_tasks = json_decode($result,true);
        return $obj_tasks;
    }
    public function AddUserPrintTask($user_id,$task_info,$filename)
    {
        $upload_file_GBK = iconv("UTF-8", "GBK", $filename);

        $api = "api/userprinttask/".$user_id;
        $form_data = $task_info;
        $form_data[] = new \CURLFile($upload_file_GBK);
        $res = $this->POST($api,$form_data,"multipart");
        return $res;
    }

    public function CancelUserPrintTask($task_id_list)
    {
        $res = $this->DELETE("api/UserPrintTask",$task_id_list);
        return $res;
    }

    public function SendPrintTaskToPrinter($task_id_list,$printer_name)
    {
        $form_data = array(
            'TaskId'=>$task_id_list,
            'PrinterName'=>$printer_name
        );
        $res = $this->PUT("api/UserPrintTask",$form_data);
        return $res;
    }
}