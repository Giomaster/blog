<?php
#configurando a database
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db   =  'blog';

$con = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
$con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
error_reporting(E_ALL & ~E_NOTICE);
header('Content-Type: application/json');
$json = json_decode(file_get_contents('php://input'), true);
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  #verficando email na database
  $checkCredenciais = $con->query("SELECT id, email, password FROM Users WHERE email = '$json[email]' AND password = '$json[password]'");
  $cec = $checkCredenciais->fetch(PDO::FETCH_OBJ);

  #tratando as informações obtidas
  if (!array_key_exists("email", $json)) {
    $response = ["message" => "\"email\" is required"];
    http_response_code(400);
  } elseif (!array_key_exists("password", $json)) {
    $response = ["message" => "\"password\" is required"];
    http_response_code(400);
  } elseif (!$json["email"]) {
    $response = ["message" => "\"email\" is not allowed to be empty"];
    http_response_code(400);
  } elseif (!$json["password"]) {
    $response = ["message" => "\"password\" is not allowed to be empty"];
    http_response_code(400);
  } elseif (!$cec) {
    $response = ["message" => "Campos inválidos"];
    http_response_code(400);
  } else {
    #gerando token JWT
    $header = ['typ' => 'JWT', 'alg' => 'HS2556'];
    $header = base64_encode(json_encode($header));
    $payload = base64_encode(json_encode($json));
    $sign = base64_encode(hash_hmac('sha256', $header . "." . $payload, '', true));
    $token = $header . '.' . $payload . '.' . $sign;

    $response = ["token" => $token];
    $_SESSION["token"] = $token;
    $_SESSION["id"] = $cec->id;
  }
  echo json_encode($response);
}

?>
