<?php
require_once 'db.php';
$success = '';
$error = '';

$importersList = [];
$resultImporters = $connection->query("SELECT `ID-Точки импорта`, `Местоположение` FROM `Импортеры`");
if ($resultImporters) {
    while ($row = $resultImporters->fetch_assoc()) {
        $importersList[] = $row;
    }
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $message = htmlspecialchars(trim($_POST['message']));
    $importer_id = isset($_POST['importer_id']) ? (int)$_POST['importer_id'] : 0;

    $importerLocation = '';
    if ($importer_id > 0) {
        $stmtImporter = $connection->prepare("SELECT `Местоположение` FROM `Импортеры` WHERE `ID-Точки импорта` = ?");
        $stmtImporter->bind_param("i", $importer_id);
        $stmtImporter->execute();
        $resImporter = $stmtImporter->get_result();
        if ($rowImp = $resImporter->fetch_assoc()) {
            $importerLocation = $rowImp['Местоположение'];
        }
        $stmtImporter->close();
    }

    $to = 'admin@example.com'; 
    $subject = "Сообщение с сайта от $name (Точка импорта: $importerLocation)";
    $headers = "From: $email\r\nReply-To: $email\r\nContent-type: text/plain; charset=utf-8";
    $fullMessage = "Имя: $name\nEmail: $email\nТочка импорта: $importerLocation\n\nСообщение:\n$message";

    if (mail($to, $subject, $fullMessage, $headers)) {
        $success = "Сообщение успешно отправлено!";
        $stmt = $connection->prepare("INSERT INTO `Контактные_сообщения` (`Имя`, `Email`, `Сообщение`, `ID_Импортера`) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $name, $email, $message, $importer_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $error = "Не удалось отправить сообщение.";
    }
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 1;
$offset = $page * $limit;

$result = $connection->query("SELECT * FROM `Импортеры` LIMIT $limit OFFSET $offset");
$importer = $result->fetch_assoc();

$totalResult = $connection->query("SELECT COUNT(*) as total FROM `Импортеры`");
$totalRow = $totalResult->fetch_assoc();
$totalImporters = $totalRow['total'];
?>

<?php include 'header.php'; ?>
<div class="contact-wrapper centered-container">

    <div style="flex: 1;">
        <h2>Контакты</h2>
        <p>Свяжитесь с нами, используя форму ниже:</p>

        <?php if ($success): ?>
            <p class="success"><?= $success ?></p>
        <?php elseif ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="post" class="contact-form">
            <label>Имя:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email" required>

            <label>Точка импорта:</label>
            <select name="importer_id" required>
                <option value="" disabled selected>Выберите точку импорта</option>
                <?php foreach ($importersList as $imp): ?>
                    <option value="<?= (int)$imp['ID-Точки импорта'] ?>">
                        <?= htmlspecialchars($imp['Местоположение']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Сообщение:</label>
            <textarea name="message" rows="5" required></textarea>

            <button type="submit">Отправить</button>
        </form>
    </div>

    <div class="importer-info">
    <h3>Информация о точке импорта</h3>
    <?php if ($importer): ?>
        <p><strong>Местоположение:</strong> <?= htmlspecialchars($importer['Местоположение']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($importer['email']) ?></p>
        <?php if (!empty($importer['image_url'])): ?>
            <img src="<?= htmlspecialchars($importer['image_url']) ?>" alt="Фото точки">
        <?php else: ?>
            <p><em>Фото не загружено.</em></p>
        <?php endif; ?>
        <div class="importer-nav">
            <?php if ($page > 0): ?>
                <a href="?page=<?= $page - 1 ?>">← Назад</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <?php if ($page < $totalImporters - 1): ?>
                <a href="?page=<?= $page + 1 ?>">Далее →</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <p>Информация о точке не найдена.</p>
    <?php endif; ?>
</div>
</div>
<?php include 'footer.php'; ?>