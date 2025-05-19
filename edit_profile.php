<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['ID-Точки импорта'];
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_profile'])) {
    $newLocation = trim($_POST['Местоположение']);
    $stmt = $connection->prepare("UPDATE `Импортеры` SET `Местоположение` = ? WHERE `ID-Точки импорта` = ?");
    $stmt->bind_param("si", $newLocation, $userId);

    if ($stmt->execute()) {
        $_SESSION['user']['Местоположение'] = $newLocation;
        header("Location: cabinet.php");
        exit();
    } else {
        $message = "Ошибка при обновлении профиля: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_warehouse'])) {
    $warehouseId = (int)$_POST['ID-Склада'];
    $w_location = trim($_POST['w_Местоположение']);
    $w_area = (int)$_POST['w_Площадь'];
    $w_status = (int)$_POST['w_Статус'];

    $stmt = $connection->prepare("UPDATE `Склады` SET `Местоположение` = ?, `Площадь` = ?, `Статус` = ? 
                                  WHERE `ID-Склада` = ? AND `ID-Точки импорта` = ?");
    $stmt->bind_param("siiii", $w_location, $w_area, $w_status, $warehouseId, $userId);

    if ($stmt->execute()) {
        $message = "Склад обновлён.";
    } else {
        $message = "Ошибка при обновлении склада: " . $stmt->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_warehouse'])) {
    $newLoc = trim($_POST['new_Местоположение']);
    $newArea = (int)$_POST['new_Площадь'];
    $newStatus = (int)$_POST['new_Статус'];

    $stmt = $connection->prepare("INSERT INTO `Склады` (`Площадь`, `Местоположение`, `Статус`, `ID-Точки импорта`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isii", $newArea, $newLoc, $newStatus, $userId);

    if ($stmt->execute()) {
        $message = "Склад успешно добавлен.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $message = "Ошибка при добавлении склада: " . $stmt->error;
    }
}

$stmt = $connection->prepare("SELECT * FROM `Склады` WHERE `ID-Точки импорта` = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$warehouses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>
<div class="account-container">

<h2>Редактировать аккаунт</h2>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="update_profile" value="1">
    <label>Новое местоположение:</label>
    <input type="text" name="Местоположение" value="<?= htmlspecialchars($user['Местоположение'] ?? '') ?>">
    <button type="submit">Сохранить</button>
</form>

<hr>

<h3>Редактировать склады</h3>
<?php if (empty($warehouses)): ?>
    <p>Склады не найдены.</p>
<?php else: ?>
    <?php foreach ($warehouses as $wh): ?>
        <form method="post">
            <input type="hidden" name="update_warehouse" value="1">
            <input type="hidden" name="ID-Склада" value="<?= $wh['ID-Склада'] ?>">

            <label>Местоположение склада:</label>
            <input type="text" name="w_Местоположение" value="<?= htmlspecialchars($wh['Местоположение']) ?>">

            <label>Площадь (м²):</label>
            <input type="number" name="w_Площадь" value="<?= (int)$wh['Площадь'] ?>">

            <label>Статус:</label>
            <select name="w_Статус">
                <option value="1" <?= $wh['Статус'] == 1 ? 'selected' : '' ?>>Активен</option>
                <option value="0" <?= $wh['Статус'] == 0 ? 'selected' : '' ?>>Неактивен</option>
            </select>

            <button type="submit">Сохранить изменения</button>
        </form>
    <?php endforeach; ?>
<?php endif; ?>

<hr>
<h3>Добавить новый склад</h3>
<form method="post">
    <input type="hidden" name="create_warehouse" value="1">

    <label>Местоположение:</label>
    <input type="text" name="new_Местоположение" required>

    <label>Площадь (м²):</label>
    <input type="number" name="new_Площадь" required>

    <label>Статус:</label>
    <select name="new_Статус">
        <option value="1">Активен</option>
        <option value="0">Неактивен</option>
    </select>

    <button type="submit">Добавить склад</button>
</form>

<a href="cabinet.php" class="btn btn-secondary">Назад в кабинет</a>
</div>
<?php include 'footer.php'; ?>