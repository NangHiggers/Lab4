<?php
require_once 'db.php';

// 2. Подготовка SQL-запроса
$sql = "INSERT INTO `Импортеры` (`email`, `pass`) VALUES (?, ?)";
$stmt = mysqli_prepare($connection, $sql);
if (!$stmt) {
    echo "Ошибка подготовки запроса: " . mysqli_error($connection);
    exit();
}

// 3. Пример значений
$email = 'testexample.com';
$pass = 'secretpass'; // Лучше использовать password_hash

// 4. Привязка параметров
if (!mysqli_stmt_bind_param($stmt, 'ss', $email, $pass)) {
    echo "Ошибка привязки параметров: " . mysqli_error($connection);
    mysqli_stmt_close($stmt);
    exit();
}

// 5. Выполнение запроса
if (!mysqli_stmt_execute($stmt)) {
    echo "Ошибка выполнения запроса: " . mysqli_error($connection);
    mysqli_stmt_close($stmt);
    exit();
}

// 6. Проверка результата
if (mysqli_stmt_affected_rows($stmt) > 0) {
    echo "Регистрация успешна. ID нового импортера: " . mysqli_insert_id($connection);
} else {
    echo "Регистрация не выполнена.";
}

// 7. Очистка
mysqli_stmt_close($stmt);
mysqli_close($connection);
?>
