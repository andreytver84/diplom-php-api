<?php 
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");


    include 'DbConnect.php';
    $objDb = new DbConnect;
    $conn = $objDb->connect();
    // Получение данных из тела запроса
    $data = json_decode(file_get_contents("php://input"), true);

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Если запрос GET, получить все записи из таблицы
        $selectSql = "SELECT * FROM prices";
        $selectStmt = $conn->prepare($selectSql);
        $selectStmt->execute();
        $records = $selectStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Вывести результат в формате JSON
        echo json_encode($records);
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Если запрос POST, оставить текущий функционал добавления и редактирования записей

    // Проверка, существует ли запись с таким id_hall
    $existingSql = "SELECT * FROM prices WHERE hall_id = :hall_id";
    $existingStmt = $conn->prepare($existingSql);
    if ($data['id'] && $data['name']) {
        $existingStmt->bindValue(':hall_id', $data['id']);
        $existingStmt->execute();
        $existingRecord = $existingStmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRecord) {
            // Если запись существует, редактировать существующую запись
            $updateSql = "UPDATE prices SET standart_price = :standart_price, vip_price = :vip_price WHERE hall_id = :hall_id";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bindValue(':standart_price', $data['standart_price']);
            $updateStmt->bindValue(':vip_price', $data['vip_price']);
            $updateStmt->bindValue(':hall_id', $data['id']);
            
            if ($updateStmt->execute()) {
                // Успешное выполнение запроса
                $response = array('success' => true, 'message' => 'Data updated successfully');
                echo json_encode($response);
            } else {
                // Ошибка при выполнении запроса
                $response = array('success' => false, 'message' => 'Failed to update data');
                echo json_encode($response);
            }
        } else {
            // Если запись не существует, добавить новую запись
            $insertSql = "INSERT INTO prices (standart_price, vip_price, name, hall_id) VALUES ( :standart_price, :vip_price, :name, :hall_id)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bindValue(':standart_price', $data['standart_price']);
            $insertStmt->bindValue(':vip_price', $data['vip_price']);
            $insertStmt->bindValue(':name', $data['name']);
            $insertStmt->bindValue(':hall_id', $data['id']);            

            if ($insertStmt->execute()) {
                // Успешное выполнение запроса
                $response = array('success' => true, 'message' => 'Data inserted successfully');
                echo json_encode($response);
            } else {
                // Ошибка при выполнении запроса
                $response = array('success' => false, 'message' => 'Failed to insert data');
                echo json_encode($response);
            }
        }
    }

    }


    


?>