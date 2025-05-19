<?php
session_start();
require 'db.php';

$search = '';
$isSearch = false;

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $search = trim($_GET['search']);
    $stmt = $connection->prepare("SELECT * FROM `Процессоры` WHERE `Модель` LIKE ?");
    $param = "%$search%";
    $stmt->bind_param("s", $param);
    $isSearch = true;
} else {
    $stmt = $connection->prepare("SELECT * FROM `Процессоры`");
}
$stmt->execute();
$result = $stmt->get_result();
?>

<?php include 'header.php'; ?>



<h2>Каталог процессоров</h2>

<form method="get" action="catalog.php">
  <input type="text" name="search" placeholder="Поиск по модели..." value="<?= htmlspecialchars($search) ?>">
  <input type="submit" value="Найти">
</form>

<form id="compareForm" method="get" action="compare.php"></form>
<div class="catalog-container <?= $isSearch ? 'search-results' : '' ?>">
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
