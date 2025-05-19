<?php
session_start();
require 'db.php';

if (!isset($_GET['compare']) || empty($_GET['compare'])) {
    header("Location: catalog.php");
    exit();
}

$compareIds = $_GET['compare']; 
$placeholders = implode(',', array_fill(0, count($compareIds), '?'));

$stmt = $connection->prepare("SELECT * FROM `Процессоры` WHERE `ID-Процессора` IN ($placeholders)");
$stmt->bind_param(str_repeat('i', count($compareIds)), ...$compareIds);
$stmt->execute();
$result = $stmt->get_result();

$processors = [];
while ($row = $result->fetch_assoc()) {
    preg_match('/(\d+)-(\d+)\s*(\d+)Hz/', $row['Характеристики'], $matches);

    $cores = isset($matches[1]) ? (int)$matches[1] : 0;    
    $threads = isset($matches[2]) ? (int)$matches[2] : 0;  
    $frequency = isset($matches[3]) ? (int)$matches[3] : 0; 

    $processors[] = [
        'ID-Процессора' => $row['ID-Процессора'],
        'Модель' => $row['Модель'],
        'Цена' => $row['Цена'],
        'Дата Выпуска' => $row['Дата Выпуска'],
        'Ядра' => $cores,
        'Потоки' => $threads,
        'Частота' => $frequency
    ];
}
?>

<?php include 'header.php'; ?>
<h2>Сравнение процессоров</h2>

<?php if (count($processors) > 1): ?>
    <table class="comparison-table" style="width: 100%; border-collapse: collapse;">
        <tr>
            <th>Параметр</th>
            <?php foreach ($processors as $processor): ?>
                <th><?= htmlspecialchars($processor['Модель']) ?></th>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <td>Ядра</td>
            <?php 
                $maxCores = max(array_column($processors, 'Ядра'));
            ?>
            <?php foreach ($processors as $processor): ?>
                <td class="<?= $processor['Ядра'] == $maxCores ? 'best' : 'worst' ?>">
                    <?= $processor['Ядра'] ?>
                </td>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <td>Потоки</td>
            <?php 
                $maxThreads = max(array_column($processors, 'Потоки'));
            ?>
            <?php foreach ($processors as $processor): ?>
                <td class="<?= $processor['Потоки'] == $maxThreads ? 'best' : 'worst' ?>">
                    <?= $processor['Потоки'] ?>
                </td>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <td>Частота (Hz)</td>
            <?php 
                $maxFreq = max(array_column($processors, 'Частота'));
            ?>
            <?php foreach ($processors as $processor): ?>
                <td class="<?= $processor['Частота'] == $maxFreq ? 'best' : 'worst' ?>">
                    <?= $processor['Частота'] ?> Hz
                </td>
            <?php endforeach; ?>
        </tr>
        
        <tr>
            <td>Цена (₽)</td>
            <?php 
                $minPrice = min(array_column($processors, 'Цена'));
            ?>
            <?php foreach ($processors as $processor): ?>
                <td class="<?= $processor['Цена'] == $minPrice ? 'best' : 'worst' ?>">
                    <?= number_format($processor['Цена'], 0, ',', ' ') ?>
                </td>
            <?php endforeach; ?>
        </tr>
        
    </table>
    

<?php else: ?>
    <p>Пожалуйста, выберите хотя бы два процессора для сравнения.</p>
<?php endif; ?>

<a href="catalog.php"><button>Вернуться в каталог</button></a>

<?php include 'footer.php'; ?>
