<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];
$userId = $user['ID-Точки импорта'];

$warehouse = null;
$stmt = $connection->prepare("SELECT * FROM `Склады` WHERE `ID-Точки импорта` = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$warehouse = $result->fetch_assoc();
?>

<?php include 'header.php'; ?>

<div class="container-fluid custom-cabinet">
    
    
    <h2>Личный кабинет</h2>

    <div class="profile-info">
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Местоположение:</strong> <?= htmlspecialchars($user['Местоположение'] ?? 'Не указано') ?></p>

        <?php if ($warehouse): ?>
            <p><strong>Склад:</strong> <?= htmlspecialchars($warehouse['Местоположение']) ?> (<?= (int)$warehouse['Площадь'] ?> м²)</p>
        <?php else: ?>
            <p><strong>Склад:</strong> отсутствует</p>
        <?php endif; ?>
    </div>

    <a href="edit_profile.php"><button class="btn btn-primary">Редактировать аккаунт</button></a>

    <h3>Ваши заказы</h3>
    <?php
    $stmt = $connection->prepare("
        SELECT z.`ID-Заказа`, z.`Дата`
        FROM `Заказы на импорт` zi
        JOIN `Заказы` z ON zi.`ID-Заказа` = z.`ID-Заказа`
        WHERE zi.`ID-Точки импорта` = ?
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $orders = $stmt->get_result();
    ?>

    <?php if ($orders->num_rows > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID Заказа</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <?php $orderId = $o['ID-Заказа']; ?>
            <tr>
                <td><?= $orderId ?></td>
                <td><button class="btn btn-info" onclick="toggleDetails(<?= $orderId ?>)">Показать детали</button></td>
            </tr>
            <tr id="order-details-<?= $orderId ?>" class="order-details" style="display: none;">
                <td colspan="3">
                    <h5>Подробности заказа #<?= $orderId ?> от <?= date('d.m.Y', strtotime($o['Дата'])) ?></h5>
                    <ul>
                    <?php
                    $dStmt = $connection->prepare("
                        SELECT p.`Модель`, zp.`Количество`, p.`Цена`
                        FROM `Заказ_Процессор_Склад` zp
                        JOIN `Процессоры` p ON zp.`ID-Процессора` = p.`ID-Процессора`
                        WHERE zp.`ID-Заказа` = ?
                    ");
                    $dStmt->bind_param("i", $orderId);
                    $dStmt->execute();
                    $details = $dStmt->get_result();

                    $total = 0;
                    while ($item = $details->fetch_assoc()):
                        $lineSum = $item['Количество'] * $item['Цена'];
                        $total += $lineSum;
                    ?>
                        <li>
                            <?= htmlspecialchars($item['Модель']) ?> —
                            <?= $item['Количество'] ?> шт. × <?= number_format($item['Цена'],0,',',' ') ?> ₽
                            = <?= number_format($lineSum,0,',',' ') ?> ₽
                        </li>
                    <?php endwhile; ?>
                    </ul>
                    <p><strong>Общая стоимость заказа:</strong>
                        <?= number_format($total, 0, ',', ' ') ?> ₽
                    </p>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>У вас пока нет заказов.</p>
    <?php endif; ?>
    
    <br>
    <a href="logout.php" class="btn btn-danger">Выйти</a>
</div>

<?php include 'footer.php'; ?>

<script>
function toggleDetails(orderId) {
    var detailsRow = document.getElementById("order-details-" + orderId);
    if (detailsRow.style.display === "none" || detailsRow.style.display === "") {
        detailsRow.style.display = "table-row";
    } else {
        detailsRow.style.display = "none";
    }
}
</script>