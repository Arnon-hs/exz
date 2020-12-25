<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');

// Устанавливаем соединение с БД
$mysqli = new mysqli("live.proofix.tv", "root", "HQHm&GW4\sdw[Ya&4};p", "exam");
$mysqli->set_charset("utf8");

if ($mysqli->connect_errno)
    die( "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);


// Имя таблицы
$table_name = "users";

// Переменная в пост сообщении
$post_name = "Email";

// Запрос на создание таблицы, проверяем что еще нет
$null_table = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`email` VARCHAR(255) default NULL,
		`role_id` VARCHAR(255) default NULL,
		`name` VARCHAR(255) default NULL,
		`ip` CHAR(30) default NULL,
		`update_at` TIMESTAMP default CURRENT_TIMESTAMP()
		) ENGINE = MYISAM CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;";

// Выполняем запрос на создание таблицы
if (!$mysqli->query($null_table))
{
    die("Не удалось создать таблицу: (" . $mysqli->errno . ") " . $mysqli->error);
}

// Получаем IP текущего клиента
function getIp()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

$auth = '';
$name = '';
$role_id = 1;
$email = '';
// Если есть запрос, записываем в переменную param и экранируем спец.символы
if(isset($_POST["auth"]))
{
    $auth = quotemeta($_POST["auth"]);
}
if(isset($_POST["name"]))
{
    $name = quotemeta($_POST["name"]);
}
if(isset($_POST["email"]))
{
    $email = quotemeta($_POST["email"]);
}

// var_dump($param, $param2,$auth);
if($auth == "login") {
    checkLogin($table_name, $email);
}else if($auth == "reg"){
    addNewUser($table_name, $name, $email, getIp());
    
}

function checkLogin($table_name, $email)
{
    global $mysqli, $param1;
    $result = $mysqli->query("SELECT * FROM ".$table_name." WHERE email='".$email."'");
    if($result)
    {
        $row = $result->fetch_assoc();
        if($row)
        {
            // Записывае IP и дату
            $mysqli->query("UPDATE ".$table_name." SET ip='".getIp()."', update_at=CURRENT_TIMESTAMP() WHERE id='".$row['id']."'");
            $res_id = $row['id'];
            //addStrToFile("LOGIN;".$res_id.";".$phone.";".$param1.";".getIP().";".date("d.m.y H:i:s").";".$_SERVER["HTTP_USER_AGENT"]);
            echo 'Вы успешно авторизировались! Перенаправляем Вас на главную страницу...';
            echo "<input type='hidden' name='id' value='".$res_id."'>";
            die();
        }else{
            echo "error";
        }

    }else die("Не удалось выполнить запрос: (" . $mysqli->errno . ") " . $mysqli->error);
}

function updateStatus($id_user)
{
    global $mysqli;
    global $table_name;
    $mysqli->query("UPDATE ".$table_name." SET ip='".getIp()."', update_at=CURRENT_TIMESTAMP() WHERE id=".$id_user);
}

function addNewUser($table_name, $name, $email, $ip, $role_id)
{
    // var_dump($table_name, $name, $name2, $name3, $phone, $city, $email, $prof, $where, $ip);
    global $mysqli;
    $mysqli->query("INSERT INTO ".$table_name." (email, name, ip, role_id) VALUES ('".$email."', '".$name."', '".$ip."', '".$role_id."');");
  /*  echo "INSERT INTO ".$table_name." (email, phone, name, name2, name3, prof, ip, city, whereq ) VALUES ('".$email."', '".$phone."', '".$name."', '".$name2."', '".$name3."', '".$prof."', '".$ip."', '".$city."', '".$whereq."') ".PHP_EOL.$mysqli->errno . " " .PHP_EOL. $mysqli->error;*/
    $res_id = mysqli_insert_id($mysqli);
    // addRowToGoogleSheet($res_id, $name, $name2, $name3, $phone, $city, $email, $prof, $whereq, $ip);
    //addStrToFile("REG;".$res_id.";".$email.";".$phone.";".getIP().";".date("d.m.y H:i:s").";".$_SERVER["HTTP_USER_AGENT"]);
    
    echo 'Благодарим за успешную регистрацию!<br> Через несколько секунд вы будете перенаправлены на страницу трансляции.';
    echo "<input type='hidden' name='id' value='".$res_id."'>";
    //sendEmail($name, $email, $phone, $city, $prof);
    die();
}

function applyNetMask($ip, $mask)
{
    if ( is_string($ip  ) ) $ip   = ip2long($ip  );
    if ( is_string($mask) ) $mask = ip2long($mask);

    return long2ip(sprintf('%u', $ip & $mask));
}
?>
