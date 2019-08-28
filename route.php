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

  case "do_mail"; // 網址就會是 http://127.0.0.1/game/do_mail
    try {
      $to = Config::MAIL_USER_NAME;;
      $subject = "sample subject";
      $body = "sample content";
      $mail = new Mail(Config::MAIL_USER_NAME, Config::MAIL_USER_PASSWROD);
      $mail->setFrom(Config::MAIL_FROM, Config::MAIL_FROM_NAME);
      $mail->addAddress($to);
      $mail->subject($subject);
      $mail->body($body);
      if ($mail->send()) {
        echo "success";
      } else {
        echo "fail";
      }
    } catch (Exception $e) {
      echo 'Caught exception: ',  $e->getMessage();
      $error[] = $e->getMessage();
    }

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

  case "register";
    if (isset($_POST['submit'])) {
      $gump = new GUMP();

      $_POST = $gump->sanitize($_POST);

      $validation_rules_array = array(
        'username'    => 'required|alpha_numeric|max_len,20|min_len,8',
        'email'       => 'required|valid_email',
        'password'    => 'required|max_len,20|min_len,8',
        'passwordConfirm' => 'required'
      );
      $gump->validation_rules($validation_rules_array);

      $filter_rules_array = array(
        'username' => 'trim|sanitize_string',
        'email'    => 'trim|sanitize_email',
        'password' => 'trim',
        'passwordConfirm' => 'trim'
      );
      $gump->filter_rules($filter_rules_array);

      $validated_data = $gump->run($_POST);

      if ($validated_data === false) {
        $error = $gump->get_readable_errors(false);
      } else {
        // validation successful
        foreach ($validation_rules_array as $key => $val) {
          ${$key} = $_POST[$key];
        }
        $userVeridator = new UserVeridator();
        $userVeridator->isPasswordMatch($password, $passwordConfirm);
        $userVeridator->isUsernameDuplicate($username);
        $userVeridator->isEmailDuplicate($email);
        $error = $userVeridator->getErrorArray();
      }
      // if no errors have been created carry on
      if (!isset($error) || count($error) == 0) {
        //hash the password
        $passwordObject = new Password();
        $hashedpassword = $passwordObject->password_hash($password, PASSWORD_BCRYPT);

        //create the random activasion code
        $activasion = md5(uniqid(rand(), true));

        try {

          // 新增到資料庫
          $data_array = array(
            'username' => $username,
            'password' => $hashedpassword,
            'email' => $email,
            // 'active' => $activasion
          );
          Database::get()->insert("user", $data_array);

          //redirect to index page
          header('Location: ' . Config::BASE_URL . 'register');

          //else catch the exception and show the error.
        } catch (PDOException $e) {
          $error[] = $e->getMessage();
        }
      }
    }
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/register.php');  // 載入註冊用的表單
    include('view/footer/default.php'); // 載入共用的頁尾
    break;
  case "logout";
    unset($_SESSION['memberID']);
    unset($_SESSION['username']);
    header('Location: login');
    break;
  case "home";
    if (UserVeridator::isLogin(isset($_SESSION['username']) ? $_SESSION['username'] : '')) {
      include('view/header/default.php'); // 載入共用的頁首
      include('view/body/home.php');     // 載入登入用的頁面
      include('view/footer/default.php'); // 載入共用的頁尾
    } else {
      header('Location: logout');
    }
    break;
  case "login";
    if (isset($_POST['submit'])) {
      $gump = new GUMP();

      $_POST = $gump->sanitize($_POST);

      $validation_rules_array = array(
        'username'    => 'required|alpha_numeric|max_len,20|min_len,3',
        'password'    => 'required|max_len,20|min_len,3'
      );
      $gump->validation_rules($validation_rules_array);

      $filter_rules_array = array(
        'username' => 'trim|sanitize_string',
        'password' => 'trim',
      );
      $gump->filter_rules($filter_rules_array);

      $validated_data = $gump->run($_POST);

      if ($validated_data === false) {
        $error = $gump->get_readable_errors(false);
      } else {
        // validation successful
        foreach ($validation_rules_array as $key => $val) {
          ${$key} = $_POST[$key];
        }

        $userVeridator = new UserVeridator();
        $userVeridator->loginVerification($username,$password);
        $error = $userVeridator->getErrorArray();

        if (!isset($error) || count($error) == 0) {
          $condition = "username = :username";
          $order_by = "1";
          $fields = "*";
          $limit = "LIMIT 1";
          $data_array = array(":username" => $username);
          $result = Database::get()->select("user", $condition, $order_by, $fields, $limit, $data_array);
          $_SESSION['memberID'] = $result[0]['id'];
          $_SESSION['username'] = $username;
          header('Location: home');
        }
      }
    }
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/login.php');     // 載入登入用的頁面
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

  default:
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/main.php');
    include('view/footer/default.php'); // 載入共用的頁尾
    break;
}
