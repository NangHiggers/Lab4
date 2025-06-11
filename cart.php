<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_POST['remove'])) {
    $idToRemove = $_POST['remove'];
    unset($_SESSION['cart'][$idToRemove]);
}

$editId = $_POST['edit'] ?? null;

if (isset($_POST['save_quantity'])) {
    $idToSave = $_POST['product_id'];
    $newQuantity = (int)$_POST['new_quantity'];
    if ($newQuantity > 0) {
        $_SESSION['cart'][$idToSave] = $newQuantity;
    } else {
        unset($_SESSION['cart'][$idToSave]);
    }
    $editId = null;
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

                <?php if ($editId == $item['ID-Процессора']): ?>
                    <form method="post" style="display:inline-block; margin-bottom:10px;">
                        <input type="hidden" name="product_id" value="<?= $item['ID-Процессора'] ?>">
                        <label for="new_quantity_<?= $item['ID-Процессора'] ?>">Количество:</label>
                        <input type="number" id="new_quantity_<?= $item['ID-Процессора'] ?>" name="new_quantity" value="<?= $_SESSION['cart'][$item['ID-Процессора']] ?>" min="1" style="width:60px;">
                        <button type="submit" name="save_quantity">Сохранить</button>
                        <button type="submit" name="cancel_edit" value="1">Отмена</button>
                    </form>
                <?php else: ?>
                    <p><strong>Количество:</strong> <?= $_SESSION['cart'][$item['ID-Процессора']] ?> шт.</p>
                    <form method="post" style="display:inline-block;">
                        <button type="submit" name="edit" value="<?= $item['ID-Процессора'] ?>">Редактировать</button>
                    </form>
                <?php endif; ?>

                <form method="post" style="display:inline-block;">
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
