<?php

include 'headers.php';

include 'DbConnect.php';
$objDb = new DbConnect();
$conn = $objDb->connect();

// Получение данных из тела запроса
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Если метод запроса POST, удаляем все существующие записи из таблицы "sessions_afisha"
    $deleteSql = 'DELETE FROM sessions_afisha';
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->execute();

    // Вставляем новые записи на 7 дней
    $insertSql = 'INSERT INTO sessions_afisha (hall_name, hall_id, film_title, film_id, film_time, start_Session, date, conf) 
              VALUES (:hall_name, :hall_id, :film_title, :film_id, :film_time, :start_Session, :date, :conf)';
    $insertStmt = $conn->prepare($insertSql);

    // Определяем текущую дату
    $currentDate = date('Y-m-d');

    // Обрабатываем каждый день на протяжении 7 дней
    for ($i = 0; $i < 7; ++$i) {
        // Добавляем $i дней к текущей дате
        $date = date('Y-m-d', strtotime($currentDate.' + '.$i.' days'));

        // Обрабатываем каждую запись из входных данных
        foreach ($data as $record) {
            foreach ($record as $item) {
                if (isset($item['hall_name']) && isset($item['hall_id']) && isset($item['film_title']) && isset($item['film_id']) && isset($item['film_time']) && isset($item['start_Session'])) {
                    // Вставляем новую запись с указанной датой
                    $insertStmt->execute([
                        'hall_name' => $item['hall_name'],
                        'hall_id' => $item['hall_id'],
                        'film_title' => $item['film_title'],
                        'film_id' => $item['film_id'],
                        'film_time' => $item['film_time'],
                        'start_Session' => $item['start_Session'],
                        'date' => $date,
                        'conf' => isset($item['conf']) ? json_encode($item['conf']) : null,
                    ]);
                }
            }
        }
    }

    // Выводим сообщение об успешном добавлении записей
    echo json_encode(['message' => 'Records added successfully.']);
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Если метод запроса GET, извлекаем данные из таблицы "sessions_afisha"
    $selectSql = 'SELECT * FROM sessions_afisha';
    $selectStmt = $conn->prepare($selectSql);
    $selectStmt->execute();
    $records = $selectStmt->fetchAll(PDO::FETCH_ASSOC);

    // Выводим данные в формате JSON
    echo json_encode($records);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Получение данных из тела запроса
    $data = json_decode(file_get_contents('php://input'), true);

    // Проверка наличия необходимых полей в данных
    if (isset($data['session_id']) && isset($data['conf'])) {
        // Обновление записи в таблице "sessions_afisha" по session_id
        $updateSql = 'UPDATE sessions_afisha SET conf = :conf WHERE session_id = :session_id';
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->execute([
            'conf' => json_encode($data['conf']),
            'session_id' => $data['session_id'],
        ]);

        // Вывод сообщения об успешном обновлении записи
        echo json_encode(['message' => 'Record updated successfully.']);
    } else {
        // Вывод сообщения об ошибке в случае отсутствия необходимых полей
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields.']);
    }
}
