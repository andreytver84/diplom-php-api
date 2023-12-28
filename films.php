<?php

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: *');

include 'DbConnect.php';

// Получение всех фильмов из таблицы
function getAllFilms()
{
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    $sql = 'SELECT * FROM films';
    $stmt = $conn->query($sql);
    $films = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($films);

    $conn = null;
}

// Добавление нового фильма в базу данных
function createFilm()
{
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    $film = $_POST;

    $uploadDirectory = 'posters/';
    $uploadedFile = $uploadDirectory.basename($_FILES['file-film']['name']);
    if (move_uploaded_file($_FILES['file-film']['tmp_name'], $uploadedFile)) {
        $image = $uploadedFile;

        $sql = 'INSERT INTO films (title, description, time, image) VALUES (:title, :description, :time, :image)';
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':title', $film['title-film']);
        $stmt->bindParam(':description', $film['desc-film']);
        $stmt->bindParam(':time', $film['time-film']);
        $stmt->bindParam(':image', $image);

        if ($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Фильм успешно добавлен'];
            echo json_encode($response);
        } else {
            $response = ['status' => 0, 'message' => 'Фильм не добавлен'];
            echo json_encode($response);
        }
    } else {
        $response = ['status' => 0, 'message' => 'Ошибка при сохранении файла'];
        echo json_encode($response);
    }

    $conn = null;
}

// Удаление фильма по идентификатору
function deleteFilm($filmId)
{
    $objDb = new DbConnect();
    $conn = $objDb->connect();

    $sql = 'DELETE FROM films WHERE id = :id';
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $filmId);

    if ($stmt->execute()) {
        echo 'Фильм успешно удален';
    } else {
        echo 'Ошибка при удалении фильма';
    }

    $conn = null;
}

// Обработка запросов API
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    createFilm();
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $filmId = $_GET['id'];
    if (isset($filmId)) {
        deleteFilm($filmId);
    } else {
        echo 'Не указан идентификатор фильма для удаления';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    getAllFilms();
} else {
    echo 'Неправильный метод запроса';
}
