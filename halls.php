<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");


    include 'DbConnect.php';

    // Получение списка кинозалов
function getHalls() {
    // Запрос к базе данных для получения списка кинозалов
    $objDb = new DbConnect;
    $conn = $objDb->connect();
    $sql = "SELECT * FROM halls";
    $result = $conn->query($sql);

    // Проверка наличия данных
    if ($result->rowCount() > 0) {
      $halls = array();

      // Преобразование данных в ассоциативные массивы
      while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        $halls[] = $row;
      }

      // Закрытие соединения с базой данных
      $conn = null;

      // Возвращение списка кинозалов в формате JSON
      header('Content-Type: application/json');
      echo json_encode($halls);
    } else {
      echo "Нет доступных кинозалов";
    }
}

// Удаление кинозала по идентификатору
function deleteHall($hallId) {
     $objDb = new DbConnect;
        $conn = $objDb->connect();

        // Запрос к базе данных для удаления кинозала
        $sql = "DELETE FROM halls WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$hallId]);

        echo "Кинозал успешно удален";

        // Закрытие соединения с базой данных
        $conn = null;
}

// Добавление нового кинозала в базу данных
function createHall() {
    $objDb = new DbConnect;
    $conn = $objDb->connect();

    // Запрос к базе данных для добавления нового кинозала
    $hall = json_decode( file_get_contents('php://input') );
    $sql = "INSERT INTO halls (name) VALUES (:name)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':name', $hall->name);

    if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Кинозал успешно добавлен'];
    } else {
            $response = ['status' => 0, 'message' => 'Кинозал не добавлен'];
    }
    echo json_encode($response);

    echo "Кинозал успешно добавлен";

    // Закрытие соединения с базой данных
    $conn = null;
}

// Обработка запросов API
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  getHalls();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
  $hallId = $_GET['id'];
  if (isset($hallId)) {
    deleteHall($hallId);
  } else {
    echo "Не указан идентификатор кинозала для удаления";
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
  //createHall($_POST['name']);
  createHall();
} else {
  echo "Неправильный метод запроса";
}

    


?>