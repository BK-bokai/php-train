<?php
session_start();
require('vendor/autoload.php'); // 載入 composer
require('route.php');   // 路由: 決定要去哪一頁，讀取該頁面需要的資料組合介面
// echo date("Y-m-d H:i:s");
// echo date("Y-m-d H:i:s",strtotime("+1 day")); // 會印出明天的現在時間
// strtotime("+1 day") 會給出明天的 timestamp 再由 date 轉成指定格式
$table='user';
$db=Database::get();
//直接執行
// $sql="INSERT INTO `{$table}` (`username`,`password`,`email`) VALUES (:username, :password, :email)";
// $data_array=array(':username'=>'test',":password"=>12345678,":email"=>'test@email.com');
// $db->execute($sql,$data_array);
// 新增
// $data_array=array('username'=>'test', 'password'=>12345678, 'email'=>'bokai830124@gmail.com');
// $db->insert($table,$data_array);
// 搜尋
// $data_array=array(':id'=>1);
// print_r($db->select($table,'`id`=:id','','*','',$data_array));
// 更新
// $data_array=array('password'=>'00000','email'=>'bbbbb@email.com');
// $db->update($table,$data_array,'username','bokai');
// 刪除
// $data_array=array(':username'=>"test");
// $db->delete($table,'username','test')
?>