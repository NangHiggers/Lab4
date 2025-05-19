<?php
session_start();
require_once 'db.php';

$limit = 6;

$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$totalQuery = $connection->query("SELECT COUNT(*) AS total FROM Процессоры");
$totalRow = $totalQuery->fetch_assoc();
$total = (int)$totalRow['total'];

$totalPages = ceil($total / $limit);
$offset = ($page - 1) * $limit;

$query = "SELECT Модель, image_url FROM Процессоры LIMIT $limit OFFSET $offset";
$result = $connection->query($query);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Галерея процессоров</title>
</head>
<body>
<?php include 'header.php'; ?>

<h1>Галерея процессоров</h1>
<div class="gallery">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="item">
            <img src="<?= htmlspecialchars($row['image_url']) ?>" alt="<?= htmlspecialchars($row['Модель']) ?>">
            <p><?= htmlspecialchars($row['Модель']) ?></p>
        </div>
    <?php endwhile; ?>
</div>

<div class="pagination">
    <?php if ($page > 1): ?>
        <a href="?page=<?= $page - 1 ?>">&laquo; Назад</a>
    <?php endif; ?>

    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php if ($i === $page): ?>
            <span class="current"><?= $i ?></span>
        <?php else: ?>
            <a href="?page=<?= $i ?>"><?= $i ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($page < $totalPages): ?>
        <a href="?page=<?= $page + 1 ?>">Вперед &raquo;</a>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>
