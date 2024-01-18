<?php

include 'headers.php';

include 'DbConnect.php';

// Получение информации о билете по уникальному коду
function getTicketByUniqueCode($uniqueCode)
{
    // Запрос к базе данных для получения информации о билете по уникальному коду
    $objDb = new DbConnect();
    $conn = $objDb->connect();
    $sql = 'SELECT * FROM cinema_tickets WHERE uniqueCode = :uniqueCode';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':uniqueCode', $uniqueCode);
    $stmt->execute();

    // Получение результата запроса
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    // Проверка наличия данных
    if ($ticket) {
        // Закрытие соединения с базой данных
        $conn = null;

        // Возвращение информации о билете в формате JSON
        header('Content-Type: application/json');
        echo json_encode($ticket);
    } else {
        echo 'Билет с указанным уникальным кодом не найден';
    }
}

// Добавление нового билета в базу данных
function createTicket()
{
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    // Запрос к базе данных для добавления нового билета
    $ticketData = json_decode(file_get_contents('php://input'), true);
    $session_id = $ticketData['session_id'];
    $start_session = $ticketData['start_session'];
    $date = $ticketData['date'];
    $film_name = $ticketData['film_name'];
    $hall_name = $ticketData['hall_name'];
    $tickets_json = json_encode($ticketData['tickets']);
    $uniqueCode = $ticketData['uniqueCode'];

    $sql = 'INSERT INTO cinema_tickets (session_id, start_session, date, film_name, hall_name, tickets_json, uniqueCode) VALUES (:session_id, :start_session, :date, :film_name, :hall_name, :tickets_json, :uniqueCode)';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':session_id', $session_id);
    $stmt->bindParam(':start_session', $start_session);
    $stmt->bindParam(':date', $date);
    $stmt->bindParam(':film_name', $film_name);
    $stmt->bindParam(':hall_name', $hall_name);
    $stmt->bindParam(':tickets_json', $tickets_json);
    $stmt->bindParam(':uniqueCode', $uniqueCode);

    if ($stmt->execute()) {
        echo 'Новый билет успешно добавлен';
    } else {
        echo 'Ошибка при добавлении нового билета';
    }

    // Закрытие соединения с базой данных
    $conn = null;
}

// Обработка POST-запроса для добавления нового билета
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    createTicket();
}

// Обработка GET-запроса для получения информации о билете по уникальному коду
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['uniqueCode'])) {
        getTicketByUniqueCode($_GET['uniqueCode']);
    } else {
        echo 'Не указан уникальный код билета';
    }
}
