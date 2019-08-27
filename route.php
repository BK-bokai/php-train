<?php
$route = new Router(Request::uri()); //搭配 .htaccess 排除資料夾名稱後解析 URL
$route->getParameter(1); // 從 http://127.0.0.1/game/aaa/bbb 取得 aaa 字串之意

// 用參數決定載入某頁並讀取需要的資料
switch ($route->getParameter(1)) {

  case "do_create": // 執行儲存動作後回到新增表單畫面
    $hero_name = "";
    $hero_hp = "";
    $hero_mp = "";

    // 移除跨站攻擊的不安全代碼
    $data = GUMP::xss_clean($_POST);

    // 設定驗證規則
    $is_valid = GUMP::is_valid($data, array(
      'hero_name' => 'required',
      'hero_hp'   => 'required|max_len,3|min_len,2',
      'hero_mp'   => 'required|max_len,3|min_len,2'
      // 'hero_description' => 'required|max_len,100|min_len,6'
    ));

    // if (isset($_POST['hero_name'])) $hero_name = $_POST['hero_name'];
    // if (isset($_POST['hero_hp'])) $hero_hp = $_POST['hero_hp'];
    // if (isset($_POST['hero_mp'])) $hero_mp = $_POST['hero_mp'];

    if ($is_valid === true) {
      $table = "hero";
      $data_array['hero_name'] = $hero_name;
      $data_array['hero_hp'] = $hero_hp;
      $data_array['hero_mp'] = $hero_mp;
      Database::get()->insert($table, $data_array);
    } else {
      print_r($is_valid);
      die("新增失敗");
    }
    header("Location: " . Config::BASE_URL . "success");
    exit;
    break;

  case "create": // 顯示新增表單畫面
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/create.php');    // 載入新增用的表單
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

  case "success":
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/success.php');    // 載入新增用的表單
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

  case "list":
    // 讀取全英雄列表資料
    // $DAO->query( ...略... );

    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/list.php');
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

  case "hero":
    // 讀取單一英雄資料
    // $DAO->query( ...略... );

    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/hero.php');
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

  default:
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/main.php');
    include('view/footer/default.php'); // 載入共用的頁尾
    break;
}
