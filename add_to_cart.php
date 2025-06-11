<?php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $quantity = intval($_POST['quantity']);

    if (!isset($_SESSION['user'])) {
        header("Location: login.php");
        exit();
    }

    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$id])) {
        $_SESSION['cart'][$id] += $quantity;
    } else {
        $_SESSION['cart'][$id] = $quantity;
    }

    header("Location: catalog.php");
    exit();
}
?>