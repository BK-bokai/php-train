<?php

//mail.simenvi.com.tw
//gmail.com.tw
//cloudmail.taipower.com.tw
require_once "phpmailer/class.phpmailer.php";
$mail = new PHPMailer();
$mail->SMTPSecure = "ssl";
$mail->Host = "cloudsmtp.taipower.com.tw";
$mail->Port = 465;
$mail->CharSet = "utf-8";    //信件編碼
$mail->Username = "d0610201@taipower.com.tw";   //帳號，例:example@gmail.com
$mail->Password = "";        //密碼
$mail->IsSMTP();
$mail->SMTPAuth = true;
$mail->SMTPDebug  = 1;
$mail->Encoding = "base64";
$mail->IsHTML(true);     //內容HTML格式
$mail->From = "d0610201@taipower.com.tw";        //寄件者信箱
$mail->FromName = "d0610201@taipower.com.tw";    //寄信者姓名
$mail->Subject = "test";     //信件主旨
$mail->Body = "body";        //信件內容
$mail->AddAddress("bokai830124@simenvi.com.tw");   //收件者信箱
if($mail->Send()){
    echo "寄信成功";
}else{
    echo "寄信失敗";
    #echo "Mailer Error: " . $mail->ErrorInfo;
}
?>
