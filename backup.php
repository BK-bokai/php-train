<?php
require 'vendor/autoload.php';
$host="localhost";
$username="root";
$password="2841p4204";
$db="game";

$DAO=new DatabaseAccessObject($host,$username,$password,$db);
// // 要新增資料就：
// $table = "hero"; // 設定你想新增資料的資料表
// $data_array['hero_name'] = "凡恩";
// $data_array['hero_hp'] = 100;
// $data_array['hero_mp'] = 80;
// $DAO->insert($table, $data_array);
// $hero_id = $DAO->getLastId(); // 可以拿到他自動建立的 id
// // 這樣就完成新增動作了

// // 想要查詢的話
// $table = "hero"; // 設定你想查詢資料的資料表
// $condition = "hero_name = '凡恩'";
// $hero = $DAO->select($table, $condition, $order_by = "1", $fields = "*", $limit = "");
// // 這樣寫等同於下面直接呼叫的語法：
// $hero = $DAO->execute("SELECT * FROM hero WHERE hero_name = '凡恩'");
// print_r($hero); // 可以印出來看看

// // 那想修改資料呢？
// $table = "hero";
// $data_array['hero_name'] = "凡恩ATM"; // 想改他的名字
// $key_column = "id"; //
// $id = $DAO->getHeroId($table,"凡恩ATM"); // 根據我們剛剛上面拿到的 hero ID
// $DAO->update($table, $data_array, $key_column, $id);
// echo $DAO->getLastSql(); // 想知道會轉換成什麼語法 可以印出來看看

// // 最後的刪除也不難，告訴他條件就可以了
// $table = "hero";
// $key_column = "id";
// $id = $DAO->getHeroId($table,"凡恩ATM"); // 我們假設要刪除 hero_id = 1 的英雄
// $DAO->delete($table, $key_column, $id);
?>