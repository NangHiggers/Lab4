<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>ProcIMP</title>
    <link rel="stylesheet" href="style.css?v=2">
</head>
<body>
    <header>
        <div class="navbar">
            <div class="container">
                <div class="nav-left">
                <a href="index.php">Главная</a>
                <a href="gallery.php">Галерея</a>
                <a href="catalog.php">Каталог</a>
                <a href="contacts.php">Контакты</a>
            </div>
            <div class="nav-right">
                <?php if (isset($_SESSION['user'])): ?>
                    <a href="cart.php">Корзина</a>
                    <a href="cabinet.php">Личный кабинет</a>
                <?php else: ?>
                    <a href="login.php">Вход</a>
                <?php endif; ?>
            </div>
        </div>
    </header>
    <main>
