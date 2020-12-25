<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// require_once "/var/www/live.proofix.ru/vendor/autoload.php";
require 'vendor/autoload.php';

// use Sendpulse\RestApi\ApiClient;
// use Sendpulse\RestApi\Storage\FileStorage;

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate'); 
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');


// Устанавливаем соединение с БД
$mysqli = new mysqli("live.proofix.tv", "root", "HQHm&GW4\sdw[Ya&4};p", "1711");
$mysqli->set_charset("utf8");

if ($mysqli->connect_errno)
    die( "Не удалось подключиться к MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error);


// Имя таблицы
$table_name = "users_table";

// Переменная в пост сообщении
$post_name = "Email";
$post_name2 = "Phone";

// Запрос на создание таблицы, проверяем что еще нет
$null_table = "CREATE TABLE IF NOT EXISTS `".$table_name."` (
		`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
		`email` VARCHAR(255) default NULL,
		`phone` VARCHAR(255) default NULL,
		`name` VARCHAR(255) default NULL,
		`name2` VARCHAR(255) default NULL,
		`name3` VARCHAR(255) default NULL,
		`prof` VARCHAR(255) default NULL,
		`session` VARCHAR(255) default NULL,
		`ip` CHAR(30) default NULL,
		`city` VARCHAR(255) default NULL,
        `whereq` VARCHAR(255) default NULL,
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
$name2 = '';
$name3 = '';
$prof = '';
$email = '';
$whereq = '';
$phone = '';
$city = '';
$param = '';
$param2 = '';
// Если есть запрос, записываем в переменную param и экранируем спец.символы
if(isset($_POST["auth"]))
{
    $auth = quotemeta($_POST["auth"]);
}
if(isset($_POST["whereq"]))
{
    $whereq = quotemeta($_POST["whereq"]);
}
if(isset($_POST["name"]))
{
    $name = quotemeta($_POST["name"]);
}
if(isset($_POST["name2"]))
{
    $name2 = quotemeta($_POST["name2"]);
}
if(isset($_POST["name3"]))
{
    $name3 = quotemeta($_POST["name3"]);
}
if(isset($_POST["prof"]))
{
    $prof = quotemeta($_POST["prof"]);
}
if(isset($_POST["city"]))
{
    $city = quotemeta($_POST["city"]);
}
if(isset($_POST[$post_name]))
{
    $param = quotemeta($_POST[$post_name]);
    $param = str_replace("\.", ".", $param);
}
if(isset($_POST[$post_name2]))
{
    $param2 = quotemeta($_POST[$post_name2]);
    $param2 = str_replace("\.", ".", $param2);
}
// var_dump($param, $param2,$auth);
if($auth == "login") {
    checkLogin($table_name, $param2);
}else if($auth == "reg"){
    addNewUser($table_name, $name, $name2, $name3, $param2, $city, $param, $prof, $whereq, getIp());
    
}

function checkLogin($table_name, $phone)
{
    global $mysqli, $param1;
    $result = $mysqli->query("SELECT * FROM ".$table_name." WHERE phone='".$phone."'");
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

function addNewUser($table_name, $name, $name2, $name3, $phone, $city, $email, $prof, $whereq, $ip)
{
    // var_dump($table_name, $name, $name2, $name3, $phone, $city, $email, $prof, $where, $ip);
    global $mysqli;
    $mysqli->query("INSERT INTO ".$table_name." (email, phone, name, name2, name3, prof, ip, city, whereq) VALUES ('".$email."', '".$phone."', '".$name."', '".$name2."', '".$name3."', '".$prof."', '".$ip."', '".$city."', '".$whereq."');");
  /*  echo "INSERT INTO ".$table_name." (email, phone, name, name2, name3, prof, ip, city, whereq ) VALUES ('".$email."', '".$phone."', '".$name."', '".$name2."', '".$name3."', '".$prof."', '".$ip."', '".$city."', '".$whereq."') ".PHP_EOL.$mysqli->errno . " " .PHP_EOL. $mysqli->error;*/
    $res_id = mysqli_insert_id($mysqli);
    addRowToGoogleSheet($res_id, $name, $name2, $name3, $phone, $city, $email, $prof, $whereq, $ip);
    //addStrToFile("REG;".$res_id.";".$email.";".$phone.";".getIP().";".date("d.m.y H:i:s").";".$_SERVER["HTTP_USER_AGENT"]);
    
    echo 'Благодарим за успешную регистрацию! Через несколько секунд вы будете перенаправлены на страницу трансляции.';
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

function addStrToFile($str)
{
    $fileopen=fopen("user-log.txt", "a+");
    $write=$str."\r\n";
    fwrite($fileopen,$write);
    fclose($fileopen);
}
function addRowToGoogleSheet($id, $name, $name2, $name3, $phone, $city, $email, $prof, $whereq, $ip) {
    $client = new Google_Client();
    $client->setApplicationName("DoctorSchool");
    $client->setScopes(Google_Service_Sheets::SPREADSHEETS);
    $client->setAuthConfig("MyProject-098ec42a6a12.json");
    $client->setAccessToken("098ec42a6a1281836f35611ccdd30f85948dafe6");

    $service = new Google_Service_Sheets($client);

    $options = array('valueInputOption' => 'RAW');

    $values = [[$id, date("d.m.y H:i:s"),$name, $name2, $name3, $phone, $city, $email, $prof, $whereq, $ip]];
    // print_r($values);
    $body = new Google_Service_Sheets_ValueRange(['values' => $values]);
    $result = $service->spreadsheets_values->append("1-l30a61YaVokzG00cjhWFPKmOn-R9x_DMK7k9MafTI0", 'Users!A1:K1', $body, $options);
}

// function sendEmail($name, $email, $phone, $city, $prof)
// {
// // API credentials from https://login.sendpulse.com/settings/#api
// define('API_USER_ID', '78a906cded497e9ad5f54782aeaa87e9');
// define('API_SECRET', '3f1ee11499f7fbcde1b7adcffa5e7e84');
// define('PATH_TO_ATTACH_FILE', __FILE__);

// $SPApiClient = new ApiClient(API_USER_ID, API_SECRET, new FileStorage());

// $bookID = 1040332;
//  $emails = array(
//     array(
//         'email' => $email,
//         'variables' => array(
//             'phone' => $phone,
//             'name' => $name,    
//             'city' => $city,
//             'prof' => $prof
//         )
//     )
// );

// $SPApiClient->addEmails($bookID, $emails);

// }


?>
