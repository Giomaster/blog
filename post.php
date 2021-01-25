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
session_start();
$auth = apache_request_headers();
$json = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  #tratando as informações obtidas
  if (!$auth["Authorization"]) {
    $response = ["message" => "Token não encontrado"];
    http_response_code(401);
  } elseif ($auth["Authorization"] != $_SESSION["token"]) {
    $response = ["message" => "Token expirado ou inválido"];
    http_response_code(401);
  } elseif (!$json["title"]) {
    $response = ["message" => "\"title\" is required"];
    http_response_code(400);
  } elseif (!$json["content"]) {
    $response = ["message" => "\"content\" is required"];
    http_response_code(400);
  } else {
    #inserindo post à database
    $id = random_int(9999999999999, 999999999999999999);
    $con->query("INSERT INTO Posts (id, title, content, userId) VALUES ('$id', '$json[title]', '$json[content]', '$_SESSION[id]')");
    $response = ["title" => $json["title"], "content" => $json["content"], "userId" => $_SESSION["id"]];
    http_response_code(201);
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $response = [];
  if (preg_match("/search\?q/i", $id)) {
    $id = str_replace("%20", " ", explode("search?q=", $id)[1]);
    if ($auth["Authorization"] == $_SESSION["token"]) {
      $getPosts = $con->query("SELECT * FROM Posts");
      for ($i = 0; $fetchPosts = $getPosts->fetch(PDO::FETCH_OBJ) ; $i++) {
        $getUsers = $con->query("SELECT * FROM Users WHERE id='$fetchPosts->userId'");
        $fetchUser = $getUsers->fetch(PDO::FETCH_OBJ);
        $postItems = [
          "id" => $fetchPosts->id,
          "title" => $fetchPosts->title,
          "content" => $fetchPosts->content,
          "published" => $fetchPosts->published,
          "updated" => $fetchPosts->updated,
          "user" => [
            "id" => $fetchUser->id,
            "displayName" => $fetchUser->displayName,
            "email" => $fetchUser->email,
            "image" => $fetchUser->image
          ]
        ];
        if (preg_match("/{$id}/i", $fetchPosts->title) or preg_match("/{$id}/i", $fetchPosts->content) or empty($id)) { $response.array_push($response, $postItems); }
      }
    } else if (!$auth["Authorization"]) {
      $response = ["message" => "Token não encontrado"];
      http_response_code(401);
    } else {
      $response = ["message" => "Token expirado ou inválido"];
      http_response_code(401);
    }
  } else {
    if ($auth["Authorization"] == $_SESSION["token"]) {
      $getPosts = empty($id) ? $con->query("SELECT * FROM Posts") : $con->query("SELECT * FROM Posts WHERE id='$id'");
      for ($i = 0; $fetchPosts = $getPosts->fetch(PDO::FETCH_OBJ) ; $i++) {
        $getUsers = $con->query("SELECT * FROM Users WHERE id='$fetchPosts->userId'");
        $fetchUser = $getUsers->fetch(PDO::FETCH_OBJ);
        $postItems = [
          "id" => $fetchPosts->id,
          "title" => $fetchPosts->title,
          "content" => $fetchPosts->content,
          "published" => $fetchPosts->published,
          "updated" => $fetchPosts->updated,
          "user" => [
            "id" => $fetchUser->id,
            "displayName" => $fetchUser->displayName,
            "email" => $fetchUser->email,
            "image" => $fetchUser->image
          ]
        ];
        $response.array_push($response, $postItems);
      }
      if (count($response) < 1) {
        $response = ["message" => "Post não existe"];
        http_response_code(404);
      }
    } else if (!$auth["Authorization"]) {
      $response = ["message" => "Token não encontrado"];
      http_response_code(401);
    } else {
      $response = ["message" => "Token expirado ou inválido"];
      http_response_code(401);
    }
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $getPost = $con->query("SELECT userId FROM Posts WHERE id='$id'");
  $fetchPost = $getPost->fetch(PDO::FETCH_OBJ);

  #tratando as informações
  if (!$auth["Authorization"]) {
    $response = ["message" => "Token não encontrado"];
    http_response_code(401);
  } elseif ($auth["Authorization"] != $_SESSION["token"]) {
    $response = ["message" => "Token expirado ou inválido"];
    http_response_code(401);
  } elseif (!$fetchPost) {
    $response = ["message" => "Post não existe"];
    http_response_code(404);
  } elseif ($fetchPost->userId != $_SESSION["id"]) {
    $response = ["message" => "Usuário não autorizado"];
    http_response_code(401);
  } else {
    #inserindo post à database
    $con->query("DELETE FROM Posts WHERE id = '$id'");
    http_response_code(204);
    $response = "";
  }
} else if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
  $getPost = $con->query("SELECT userId FROM Posts WHERE id='$id'");
  $updatePost = $getPost->fetch(PDO::FETCH_OBJ);

  #tratando as informações
  if (!$auth["Authorization"]) {
    $response = ["message" => "Token não encontrado"];
    http_response_code(401);
  } elseif ($auth["Authorization"] != $_SESSION["token"]) {
    $response = ["message" => "Token expirado ou inválido"];
    http_response_code(401);
  } elseif ($updatePost->userId != $_SESSION["id"]) {
    $response = ["message" => "Usuário não autorizado"];
    http_response_code(401);
  } elseif (!$json["title"]) {
    $response = ["message" => "\"title\" is required"];
    http_response_code(400);
  } elseif (!$json["content"]) {
    $response = ["message" => "\"content\" is required"];
    http_response_code(400);
  } else {

    #atualizando post à database
    $con->query("UPDATE Posts SET title = '$json[title]', content = '$json[content]' WHERE id = '$id'");
    $response = ["title" => $json["title"], "content" => $json["content"], "userId" => $_SESSION["id"]];
  }
}
echo json_encode($response);

?>
