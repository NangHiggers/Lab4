<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    echo "Корзина пуста.";
    exit();
}

$user = $_SESSION['user'];
$userId = $user['ID-Точки импорта'];
$cart = $_SESSION['cart'];


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
    $defaultWarehouseId = 1; 

    $stmt3 = $connection->prepare("INSERT INTO `Заказ_Процессор_Склад` (`ID-Заказа`, `ID-Процессора`, `ID-Склада`, `Количество`) VALUES (?, ?, ?, ?)");
    $stmt3->bind_param("iiii", $orderId, $productId, $defaultWarehouseId, $quantity);
    $stmt3->execute();
}

unset($_SESSION['cart']);

header("Location: cabinet.php");
exit();
?>
