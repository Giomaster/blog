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
$auth = apache_request_headers();
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  #verficando email na database
  $checkEmail = $con->query("SELECT * FROM Users WHERE email = '$json[email]'");
  $cef = $checkEmail->fetch(PDO::FETCH_OBJ);

  #tratando as informações obtidas
  if (strlen($json["displayName"]) < 8) {
    $response = ["message" => "\"displayName\" length must be at least 8 characters long"];
    http_response_code(400);
  } elseif ($cef) {
    $response = ["message" => "Usuário já existe"];
    http_response_code(409);
  } elseif (!array_key_exists("email", $json)) {
    $response = ["message" => "\"email\" is required"];
    http_response_code(400);
  } elseif (!array_key_exists("password", $json)) {
    $response = ["message" => "\"password\" is required"];
    http_response_code(400);
  } elseif (!filter_var($json["email"], FILTER_VALIDATE_EMAIL)) {
    $response = ["message" => "\"email\" must be a valid email"];
    http_response_code(400);
  } elseif (strlen($json["password"]) != 6) {
    $response = ["message" => "\"password\" length must be 6 characters long"];
    http_response_code(400);
  } else {
    #gerando token JWT
    $header = ['typ' => 'JWT', 'alg' => 'HS2556'];
    $header = base64_encode(json_encode($header));
    $payload = base64_encode(json_encode($json));
    $sign = base64_encode(hash_hmac('sha256', $header . "." . $payload, '', true));
    $token = $header . '.' . $payload . '.' . $sign;

    $response = ["token" => $token];
    http_response_code(201);

    #inserindo user à database
    $id = random_int(9999999999999, 999999999999999999);
    $con->query("INSERT INTO Users (id, displayName, email, password, image) VALUES ('$id', '$json[displayName]', '$json[email]', '$json[password]', '$json[image]')");
    $_SESSION["token"] = $token;
    $_SESSION["id"] = $id;
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  if ($auth["Authorization"] == $_SESSION["token"]) {
    $response = [];
    $getUsers = empty($id) ? $con->query("SELECT * FROM Users") : $con->query("SELECT * FROM Users WHERE id='$id'");
    for ($i = 0; $fetchUser = $getUsers->fetch(PDO::FETCH_OBJ) ; $i++) {
      $userItems = [
        "id" => $fetchUser->id,
        "displayName" => $fetchUser->displayName,
        "email" => $fetchUser->email,
        "image" => $fetchUser->image
      ];
      $response.array_push($response, $userItems);
    }
    if (count($response) < 1) {
      $response = ["message" => "Usuário não existe"];
      http_response_code(404);
    }
  } else if (!$auth["Authorization"]) {
    $response = ["message" => "Token não encontrado"];
    http_response_code(401);
  } else {
    $response = ["message" => "Token expirado ou inválido"];
    http_response_code(401);
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE' and $id == "me") {

  if ($auth["Authorization"] == $_SESSION["token"]) {
    $deleteUser = $con->query("DELETE FROM Users WHERE id='$_SESSION[id]'");
    $response = "";
    session_destroy();
    http_response_code(204);
  } else if (!$auth["Authorization"]) {
    $response = ["message" => "Token não encontrado"];
    http_response_code(401);
  } else {
    $response = ["message" => "Token expirado ou inválido"];
    http_response_code(401);
  }
}
echo json_encode($response);

?>
