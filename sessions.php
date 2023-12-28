<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

include 'DbConnect.php';
$objDb = new DbConnect();
$conn = $objDb->connect();

// Получение данных из тела запроса
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Если метод запроса GET, получаем все записи из таблицы "sessions"
    $selectSql = 'SELECT * FROM sessions';
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->execute();
    $records = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Выводим результат в формате JSON
    echo json_encode($records);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если метод запроса POST, удаляем все существующие записи и вставляем новые записи

    // Удаляем все существующие записи из таблицы "sessions"
    $deleteSql = 'DELETE FROM sessions';
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->execute();

    // Вставляем новые записи
    $insertSql = 'INSERT INTO sessions (hall, hall_id, film_title, film_id, time, session_start) 
              VALUES (:hall, :hall_id, :film_title, :film_id, :time, :session_start)';
    $insertStmt = $conn->prepare($insertSql);

    // Обрабатываем каждую запись из входных данных
    foreach ($data as $record) {
        foreach ($record as $item) {
            if (isset($item['hall_name']) && isset($item['hall_id']) && isset($item['film_title']) && isset($item['film_id']) && isset($item['film_time']) && isset($item['start_Session'])) {
                $insertStmt->bindValue(':hall', $item['hall_name']);
                $insertStmt->bindValue(':hall_id', $item['hall_id']);
                $insertStmt->bindValue(':film_title', $item['film_title']);
                $insertStmt->bindValue(':film_id', $item['film_id']);
                $insertStmt->bindValue(':time', (int) $item['film_time']);
                $insertStmt->bindValue(':session_start', $item['start_Session']);
                $insertStmt->execute();
            }
        }
    }

    // Отправляем сообщение об успешном выполнении операции
    echo json_encode(['success' => true, 'message' => 'Ваши данные успешно добавлены в базу']);
}
?>

