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
            'active' => $activasion
          );
          Database::get()->insert("user", $data_array);
          //以下是新的代碼
          $id = Database::get()->getLastId(); // 取得最後新增的 member ID

          $subject = "Registration Confirmation";
          $body = "<p>Thank you for registering at demo site.</p>
<p>To activate your account, please click on this link: <a href='" . Config::BASE_URL . "activate/$id/$activasion'>" . Config::BASE_URL . "activate/$id/$activasion</a></p>
<p>Regards Site Admin</p>"; // 這邊用網址上的 GET 參數讓他回來網站時夾帶驗證碼

          $mail = new Mail(Config::MAIL_USER_NAME, Config::MAIL_USER_PASSWROD);
          $mail->setFrom(Config::MAIL_FROM, Config::MAIL_FROM_NAME);
          $mail->addAddress($email);
          $mail->subject($subject);
          $mail->body($body);
          $mail->send(); // 透過 GMAIL 送出

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
    unset($_SESSION['userid']);
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
        $userVeridator->loginVerification($username, $password);
        $error = $userVeridator->getErrorArray();

        if (!isset($error) || count($error) == 0) {
          $condition = "username = :username";
          $order_by = "1";
          $fields = "*";
          $limit = "LIMIT 1";
          $data_array = array(":username" => $username);
          $result = Database::get()->select("user", $condition, $order_by, $fields, $limit, $data_array);
          $_SESSION['userid'] = $result[0]['id'];
          $_SESSION['username'] = $username;
          header('Location: home');
        }
      }
    }
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/login.php');     // 載入登入用的頁面
    include('view/footer/default.php'); // 載入共用的頁尾
    break;

    case "activate";
        $data_array = array();
        $data_array['id'] = $route->getParameter(2);    
        $data_array['active'] = $route->getParameter(3);    

        $gump = new GUMP();
        $data_array = $gump->sanitize($data_array); 
        $validation_rules_array = array(
          'id'    => 'required|integer',
          'active'    => 'required|exact_len,32'
        );
        $gump->validation_rules($validation_rules_array);

        $filter_rules_array = array(
          'id' => 'trim|sanitize_string',
          'active' => 'trim',
        );
        $gump->filter_rules($filter_rules_array);
        $validated_data = $gump->run($data_array);

        if($validated_data === false) {
          //$error = $gump->get_readable_errors(false);
          echo "驗證錯誤，請聯絡客服";
          exit;
        } else {
          foreach($validation_rules_array as $key => $val) {
            ${$key} = $data_array[$key];
          }
          $userVeridator = new UserVeridator();
          if($userVeridator->isReady2Active($id, $active)){
            $data_array['active'] = "Yes";
            Database::get()->update("user", array("active"=>"Yes"), "id", $data_array['id']); 
            header('Location: ' . Config::BASE_URL . 'login?active=active');

            // header('Location: login?action=active');
            exit;
          }else{
            echo "Your account could not be activated."; 
            exit;
          }
        }
    break;

    case "reset":
      // 檢查是否有帶 Token 
      $verify_array['resetToken'] = $route->getParameter(2);
      $gump = new GUMP();
      $verify_array = $gump->sanitize($verify_array); 
      $validation_rules_array = array(
        'resetToken'    => 'required'
      );
      $gump->validation_rules($validation_rules_array);
      $filter_rules_array = array(
        'resetToken' => 'trim'
      );
      $gump->filter_rules($filter_rules_array);
      $validated_data = $gump->run($verify_array);
      if($validated_data === false) {
        // 沒有帶 Token 回來，直接踢回 login
        header("Location: login");
        exit;
      } else {
        foreach($validation_rules_array as $key => $val) {
          ${$key} = $verify_array[$key];
        }
        // 有帶 Token 回來的話，確認是否存在
        $table = 'members';
        $condition = 'resetToken = :resetToken';
        $order_by = '1'; 
        $fields = 'resetToken, resetComplete';
        $limit = '1';
        $data_array[':resetToken'] = $resetToken;
        $result = Database::get()->query($table, $condition, $order_by, $fields, $limit, $data_array);
        if(!isset($result[0]['resetToken']) OR empty($result[0]['resetToken'])){
          $stop = 'Invalid token provided, please use the link provided in the reset email.';
        }else if(isset($result[0]['resetComplete']) AND $result[0]['resetComplete'] == 'Yes'){
          $stop = 'Your password has already been changed!';
        }
      }
      
      //if form has been submitted process it
      if(isset($_POST['submit']))
      {
      
        $gump = new GUMP();
        $_POST = $gump->sanitize($_POST); 

        $validation_rules_array = array(
          'password'    => 'required|max_len,20|min_len,3',
          'passwordConfirm' => 'required'
        );
        $gump->validation_rules($validation_rules_array);

        $filter_rules_array = array(
          'password' => 'trim',
          'passwordConfirm' => 'trim'
        );
        $gump->filter_rules($filter_rules_array);

        $validated_data = $gump->run($_POST);

        if($validated_data === false) {
          $error = $gump->get_readable_errors(false);
        } else {
          // validation successful
          foreach($validation_rules_array as $key => $val) {
            ${$key} = $_POST[$key];
          }
          $userVeridator = new UserVeridator();
          $userVeridator->isPasswordMatch($password, $passwordConfirm);
          $error = $userVeridator->getErrorArray();
        } 
        //if no errors have been created carry on
        if(count($error) == 0)
        {
          //hash the password
          $passwordObject = new Password();
          $hashedpassword = $passwordObject->password_hash($password, PASSWORD_BCRYPT);
      
          try {
            $data_array = array();
            $table = 'members';
            $data_array['password'] = $hashedpassword;
            $data_array['resetComplete'] = 'Yes';
            $key = "resetToken";
            $id = $resetToken;
            Database::get()->update($table, $data_array, $key, $id);
            
            //redirect to index page
            header('Location: '.Config::BASE_URL.'login?action=resetAccount');
            exit;
      
          //else catch the exception and show the error.
          } catch(PDOException $e) {
              $error[] = $e->getMessage();
          }
        }
      }
      include('view/header/default.php'); // 載入共用的頁首
      include('view/body/reset.php');     // 載入忘記密碼的頁面
      include('view/footer/default.php'); // 載入共用的頁尾
    break;
    case "forget":
      //if logged in redirect to members page
      if(UserVeridator::isLogin(isset($_SESSION['username'])?$_SESSION['username']:'')){
        header('Location: home'); 
        exit();
      }
      
      //if form has been submitted process it
      if(isset($_POST['submit'])){
        $gump = new GUMP();
        $_POST = $gump->sanitize($_POST); 
        $validation_rules_array = array(
          'email'    => 'required|valid_email'
        );
        $gump->validation_rules($validation_rules_array);

        $filter_rules_array = array(
          'email' => 'trim|sanitize_email'
        );
        $gump->filter_rules($filter_rules_array);
        $validated_data = $gump->run($_POST);

        if($validated_data === false) {
          $error = $gump->get_readable_errors(false);
        } else {
          //email validation
          foreach($validation_rules_array as $key => $val) {
            ${$key} = $_POST[$key];
          }
          $table = 'members';
          $condition = 'email = :email';
          $order_by = '1'; 
          $fields = 'email, memberID'; 
          $limit = '1';
          $data_array[':email'] = $email;
          $result = Database::get()->query($table, $condition, $order_by, $fields, $limit, $data_array);
          if(!isset($result[0]['memberID']) OR empty($result[0]['memberID'])){
            $error[] = 'Email provided is not recognised.';
          }else{
            $memberID = $result[0]['memberID'];
          }
        }

        //if no errors have been created carry on
        if(!isset($error)){

          //create the activation code
          try {
            $data_array = array();
            $data_array['resetComplete'] = 'No';
            $data_array['resetToken'] = md5(rand().time());
            $resetToken = $data_array['resetToken'];
            $key = "memberID";
            $id = $memberID;
            Database::get()->update('members', $data_array, $key, $id);
            
            //send email
            $to = $email;
            $subject = "Password Reset";
            $body = "<p>Someone requested that the password be reset.</p>
            <p>If this was a mistake, just ignore this email and nothing will happen.</p>
            <p>To reset your password, visit the following address: <a href='".Config::BASE_URL."reset/$resetToken'>".Config::BASE_URL."reset/$resetToken</a></p>";

            $mail = new Mail(Config::MAIL_USER_NAME, Config::MAIL_USER_PASSWROD);
            $mail->setFrom(Config::MAIL_FROM, Config::MAIL_FROM_NAME);
            $mail->addAddress($to);
            $mail->subject($subject);
            $mail->body($body);
            $mail->send();

            //redirect to index page
            header('Location: login?action=reset');
            exit;

          //else catch the exception and show the error.
          } catch(PDOException $e) {
              $error[] = $e->getMessage();
          }
        }
      }

      //define page title
      $title = 'Reset Account';
      include('view/header/default.php'); // 載入共用的頁首
      include('view/body/forget.php');    // 載入忘記密碼的頁面
      include('view/footer/default.php'); // 載入共用的頁尾
      break;

  default:
    include('view/header/default.php'); // 載入共用的頁首
    include('view/body/main.php');
    include('view/footer/default.php'); // 載入共用的頁尾
    break;
}
