<?php
session_start();
require('vendor/autoload.php'); // 載入 composer
require('route.php');   // 路由: 決定要去哪一頁，讀取該頁面需要的資料組合介面
echo date("Y-m-d H:i:s");
echo date("Y-m-d H:i:s",strtotime("+1 day")); // 會印出明天的現在時間
// strtotime("+1 day") 會給出明天的 timestamp 再由 date 轉成指定格式
?>