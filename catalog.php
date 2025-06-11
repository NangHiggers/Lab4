<?php
require_once 'db.php';

$search = '';
$supplier_filter = '';
$isSearch = false;
$isFiltered = false;

// Получаем список поставщиков для фильтра
$suppliers = $connection->query("SELECT * FROM `Поставщики`");

// Обработка поиска
if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    $isSearch = true;
}

// Обработка фильтра по поставщику
if (isset($_GET['supplier']) && $_GET['supplier'] !== '') {
    $supplier_filter = (int)$_GET['supplier'];
    $isFiltered = true;
}

// Формируем SQL запрос
$sql = "SELECT p.* FROM `Процессоры` p";
$where = [];
$params = [];
$types = '';

if ($isSearch) {
    $where[] = "p.`Модель` LIKE ?";
    $params[] = "%$search%";
    $types .= 's';
}

if ($isFiltered) {
    $where[] = "p.`ID-Поставщика` = ?";
    $params[] = $supplier_filter;
    $types .= 'i';
}

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$stmt = $connection->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'header.php'; ?>

<h2>Каталог процессоров</h2>

<div class="search-filter-container">
    <form method="get" action="catalog.php" class="search-form">
        <input type="text" name="search" placeholder="Поиск по модели..." value="<?= htmlspecialchars($search) ?>">
        <select name="supplier" class="supplier-filter">
            <option value="">Все поставщики</option>
            <?php while ($supplier = $suppliers->fetch_assoc()): ?>
                <option value="<?= $supplier['ID-Поставщика'] ?>" 
                    <?= $supplier_filter == $supplier['ID-Поставщика'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($supplier['Название']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="Применить">
        <?php if ($isSearch || $isFiltered): ?>
            <a href="catalog.php" class="reset-btn">Сбросить</a>
        <?php endif; ?>
    </form>
</div>

<form id="compareForm" method="get" action="compare.php"></form>
<div class="catalog-container <?= $isSearch || $isFiltered ? 'search-results' : '' ?>">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="product-card">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="Фото процессора">
            <h3><?= htmlspecialchars($row['Модель']) ?></h3>
            <p><strong>Характеристики:</strong> <?= htmlspecialchars($row['Характеристики']) ?></p>
            <p><strong>Цена:</strong> <?= number_format($row['Цена'], 0, ',', ' ') ?> ₽</p>
            <p><strong>Дата выпуска:</strong> <?= htmlspecialchars($row['Дата Выпуска']) ?></p>

            <?php if (isset($_SESSION['user'])): ?>
                <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="id" value="<?= $row['ID-Процессора'] ?>">
                    <label for="quantity_<?= $row['ID-Процессора'] ?>">Количество:</label>
                    <input type="number" name="quantity" id="quantity_<?= $row['ID-Процессора'] ?>" min="1" value="1" style="width:60px;">
                    <button type="submit">В корзину</button>
                </form>
            <?php endif; ?>

            <label>
                <input type="checkbox" name="compare[]" value="<?= $row['ID-Процессора'] ?>" form="compareForm">
                Сравнить
            </label>
        </div>
    <?php endwhile; ?>
</div>

<div class="compare-button-wrapper">
    <button type="submit" form="compareForm">Сравнить выбранные</button>
</div>

<?php include 'footer.php'; ?>