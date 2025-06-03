<?php
session_start();
require 'db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $result = $connection->query("SELECT * FROM Администраторы WHERE Логин = '$username' AND Пароль = '$password'");
    
    if ($result->num_rows > 0) {
        $_SESSION['admin'] = $result->fetch_assoc();
        header("Location: admin_panel.php");
        exit();
    } else {
        $error = "Неверные учетные данные!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход для администратора</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Авторизация администратора</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="post">
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Логин" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                            </div>
                            <button type="submit" name="login" class="btn btn-primary w-100">Войти</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>