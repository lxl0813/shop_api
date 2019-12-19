<?php

namespace App\Http\Controllers\Api;


use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;



class SendMsgController extends Controller
{
    public function sendMsg(Request $request){
        //接值
        $user_account=$request->input("user_account","");
//        //正则表达式
        $reg_phone='/^1[3456789]\d{9}$/';
        $reg_email='/^\w+@[a-z0-9]+\.[a-z]{2,4}$/';
        if(!preg_match($reg_phone,$user_account) && !preg_match($reg_email,$user_account)){
            return response(["status"=>40000,"message"=>"输入的账号不符合要求"],200);
        }
        //随机数作为验证码
        $code=rand(1000,9999);
        //文件缓存（将验证码放入文件缓存中，用到取出来对比）
        Cache::put('code',$code,1);
        //判断是否手机号码或者邮箱
        if(preg_match($reg_phone,$user_account)){
            //将验证码发送到手机
            $param = urlencode("web=致洁商城&code=".$code);
            $url = "http://api.k780.com/?app=sms.send&tempid=51769&param=".$param."&phone=".$user_account .
                "&appkey=45344&sign=1de9c0c90e4dc4ec7975db7b93044795&format=json";
            $data=json_decode(file_get_contents($url), true);
            if(isset($data["success"])){
                if($data["success"]==1){
                    return response(["status" => 20000, "message" => "短信发送成功"],200);
                }else{
                    return response(["status" => 40001, "message" => "短信发送失败"],200);
                }
            }

        }

        //发送到邮箱
        if(preg_match($reg_email,$user_account)){
            //将验证码发送到邮箱上
            $data=$this->sendMsgToEmail($user_account,$code);
            if($data==1){
                return response(["status" => 20000, "message" => "短信发送成功"],200);
            }else{
                return response(["status" => 40001, "message" => "短信发送失败"],200);
            }
        }
    }


    public function sendMsgToEmail($email,$code){
        require_once("mailer/class.phpmailer.php");
        $mail=new \PHPMailer();
        /*服务器相关信息*/
        $mail->IsSMTP();                        //设置使用SMTP服务器发送
        $mail->SMTPAuth   = true;               //开启SMTP认证
        $mail->Host       = 'smtp.163.com';   	    //设置 SMTP 服务器,自己注册邮箱服务器地址

        $mail->Username   = 'lxl2427396682@163.com';  		//注册的网易账号
        $mail->Password   = 'lxlysy0816';          //授权码

        /*内容信息*/
        $mail->IsHTML(true); 			         //指定邮件格式为：html/text
        $mail->CharSet    = "UTF-8";			     //编码
        $mail->From       = 'lxl2427396682@163.com';	 		 //发件人完整的邮箱名称
        $mail->FromName   = '李小龙';			 //发信人署名
        $mail->Subject    = "请查收您的注册验证码";  			 //信的标题
        $mail->MsgHTML("尊敬的用户：您注册的验证码是:".$code);  				 //发信主体内容
        /*发送邮件*/
        $mail->AddAddress($email);  			 //收件人地址
        //使用send函数进行发送
        if($mail->Send()) {
            return 1;
        } else {
            return 0;//如果发送失败，则返回错误提示
        }
    }
}
