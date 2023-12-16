<?php 
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");


include 'DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

// Получение данных из POST-запроса
$user = json_decode( file_get_contents('php://input') );

// Поиск пользователя в базе данных
$stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
$stmt->bindParam(':email', $user->email);
$stmt->bindParam(':password', $user->password);
$stmt->execute();

// Если пользователь найден, генерируем токен и возвращаем его вместе с email в виде JSON
if ($stmt->rowCount() > 0) {
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  $token = bin2hex(random_bytes(16)); // Генерация случайного токена
  
  $stmt->execute();
  $response = ['status' => 'success', 'token' => $token];
  echo json_encode($response);
} else {
  // Если пользователь не найден, возвращаем соответствующий статус и сообщение об ошибке в виде JSON
  http_response_code(403);
  echo json_encode(array('status' => 'error', 'message' => 'Invalid email or password'));
}

?>