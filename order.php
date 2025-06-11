<?php
require_once 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$importer_id = $_SESSION['user']['ID-Точки импорта'];
$quantities = $_POST['quantities'] ?? [];
$sklads = $_POST['sklads'] ?? [];

if (!empty($quantities)) {
    $stmt = $connection->prepare("INSERT INTO `Заказы на импорт` (`ID-Точки импорта`) VALUES (?)");
    $stmt->bind_param("i", $importer_id);
    $stmt->execute();
    $order_id = $stmt->insert_id;

    $stmt2 = $connection->prepare("INSERT INTO `Заказ_Процессор_Склад` (`ID-Заказа`, `ID-Процессора`, `ID-Склада`, `Количество`) VALUES (?, ?, ?, ?)");
    foreach ($quantities as $proc_id => $qty) {
        $sklad_id = $sklads[$proc_id] ?? 1;
        $stmt2->bind_param("iiii", $order_id, $proc_id, $sklad_id, $qty);
        $stmt2->execute();
    }

    unset($_SESSION['cart']);

    echo "Заказ успешно оформлен!";
} else {
    echo "Корзина пуста.";
}
?>
<a href="index.php">На главную</a>
