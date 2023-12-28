<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

include 'DbConnect.php';
$objDb = new DbConnect();
$conn = $objDb->connect();
// Получение данных из тела запроса
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Если запрос GET, получить все записи из таблицы
    $selectSql = 'SELECT * FROM halls_configuration';
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->execute();
    $records = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Вывести результат в формате JSON
    echo json_encode($records);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если запрос POST, оставить текущий функционал добавления и редактирования записей

    // Проверка, существует ли запись с таким id_hall
    $existingSql = 'SELECT * FROM halls_configuration WHERE hall_id = :hall_id';
    $existingStmt = $conn->prepare($existingSql);
    if ($data['id'] && $data['name']) {
        $existingStmt->bindValue(':hall_id', $data['id']);
        $existingStmt->execute();
        $existingRecord = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // Если запись существует, редактировать существующую запись
            $updateSql = 'UPDATE halls_configuration SET conf = :conf, name = :name,  num_rows = :num_rows, places = :places WHERE hall_id = :hall_id';
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindValue(':conf', $data['html']);
            $updateStmt->bindValue(':name', $data['name']);
            $updateStmt->bindValue(':hall_id', $data['id']);
            $updateStmt->bindValue(':num_rows', $data['rows']);
            $updateStmt->bindValue(':places', $data['places']);

            if ($updateStmt->execute()) {
                // Успешное выполнение запроса
                $response = ['success' => true, 'message' => 'Data updated successfully'];
                echo json_encode($response);
            } else {
                // Ошибка при выполнении запроса
                $response = ['success' => false, 'message' => 'Failed to update data'];
                echo json_encode($response);
            }
        } else {
            // Если запись не существует, добавить новую запись
            $insertSql = 'INSERT INTO halls_configuration (conf, hall_id, name, num_rows, places) VALUES (:conf, :hall_id, :name, :num_rows, :places)';
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bindValue(':conf', $data['html']);
            $insertStmt->bindValue(':hall_id', $data['id']);
            $insertStmt->bindValue(':name', $data['name']);
            $insertStmt->bindValue(':num_rows', $data['rows']);
            $insertStmt->bindValue(':places', $data['places']);

            if ($insertStmt->execute()) {
                // Успешное выполнение запроса
                $response = ['success' => true, 'message' => 'Data inserted successfully'];
                echo json_encode($response);
            } else {
                // Ошибка при выполнении запроса
                $response = ['success' => false, 'message' => 'Failed to insert data'];
                echo json_encode($response);
            }
        }
    }
}
