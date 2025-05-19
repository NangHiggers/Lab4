<?php
session_start();
require 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['remove'])) {
    $idToRemove = $_POST['remove'];
    unset($_SESSION['cart'][$idToRemove]);
}

$cartItems = $_SESSION['cart'];

$items = [];
if (!empty($cartItems)) {
    $placeholders = implode(',', array_fill(0, count($cartItems), '?'));
    $stmt = $connection->prepare("SELECT * FROM `Процессоры` WHERE `ID-Процессора` IN ($placeholders)");
    $stmt->bind_param(str_repeat('i', count($cartItems)), ...array_keys($cartItems));
    $stmt->execute();
    $result = $stmt->get_result();
    $items = $result->fetch_all(MYSQLI_ASSOC);
}

$user = $_SESSION['user'];
$userId = $user['ID-Точки импорта'];
$stmt = $connection->prepare("SELECT * FROM `Склады` WHERE `ID-Точки импорта` = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$warehouses = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<?php include 'header.php'; ?>
<div class="container cart-page">
    <h2>Корзина</h2>

    <?php if (empty($items)): ?>
        <p>Ваша корзина пуста.</p>
    <?php else: ?>
    <div class="cart-items"> 
        <?php foreach ($items as $item): ?>
            <div class="product-card">
                <h3><?= htmlspecialchars($item['Модель']) ?></h3>
                <p><strong>Цена:</strong> <?= number_format($item['Цена'], 0, ',', ' ') ?> ₽</p>
                <p><strong>Количество:</strong> <?= $_SESSION['cart'][$item['ID-Процессора']] ?> шт.</p>
                <form method="post">
                    <button type="submit" name="remove" value="<?= $item['ID-Процессора'] ?>">Удалить</button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>
        <form method="post" action="checkout.php">
            <label for="warehouse">Выберите склад:</label>
            <select name="warehouse" id="warehouse" required>
                <?php foreach ($warehouses as $warehouse): ?>
                    <option value="<?= $warehouse['ID-Склада'] ?>"><?= htmlspecialchars($warehouse['Местоположение']) ?></option>
                <?php endforeach; ?>
            </select>
            <br><br>
            <button type="submit">Оформить заказ</button>
        </form>

    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>
