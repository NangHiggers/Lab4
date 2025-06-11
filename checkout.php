<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "Корзина пуста.";
    exit();
}

if (!isset($_POST['warehouse'])) {
    echo "Не выбран склад для заказа.";
    exit();
}

$user = $_SESSION['user'];
$userId = $user['ID-Точки импорта'];
$cart = $_SESSION['cart'];
$warehouseId = (int)$_POST['warehouse']; 

$stmtCheck = $connection->prepare("SELECT 1 FROM `Склады` WHERE `ID-Склада` = ? AND `ID-Точки импорта` = ?");
$stmtCheck->bind_param("ii", $warehouseId, $userId);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows === 0) {
    echo "Выбранный склад не принадлежит вашей точке импорта.";
    exit();
}

$stmt = $connection->prepare("INSERT INTO `Заказы` (`Тип`, `Дата`, `Статус`) VALUES (?, CURDATE(), ?)");
$тип = 1;      
$статус = 0;   
$stmt->bind_param("ii", $тип, $статус);
$stmt->execute();

$orderId = $stmt->insert_id;


$stmt2 = $connection->prepare("INSERT INTO `Заказы на импорт` (`ID-Заказа`, `ID-Точки импорта`) VALUES (?, ?)");
$stmt2->bind_param("ii", $orderId, $userId);
$stmt2->execute();


foreach ($cart as $productId => $quantity) {
    $stmt3 = $connection->prepare("INSERT INTO `Заказ_Процессор_Склад` (`ID-Заказа`, `ID-Процессора`, `ID-Склада`, `Количество`) VALUES (?, ?, ?, ?)");
    $stmt3->bind_param("iiii", $orderId, $productId, $warehouseId, $quantity); // Используем выбранный склад
    $stmt3->execute();
}

unset($_SESSION['cart']);

header("Location: cabinet.php");
exit();
?>