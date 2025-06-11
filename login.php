<?php
require_once 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $pass = $_POST['pass'];

    $sql = "SELECT * FROM `Импортеры` WHERE `email` = ? AND `pass` = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['user'] = $result->fetch_assoc();
        header("Location: cabinet.php");
        exit();
    } else {
        $error = "Неверный email или пароль.";
    }
}
?>

<?php include 'header.php'; ?>
<div class="auth-form">
    <h2>Вход</h2>
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" required>
        <label>Пароль:</label>
        <input type="password" name="pass" required>
        <?php if ($error): ?>
            <div style="color: red; margin: 10px 0;"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <input type="submit" value="Войти">
    </form>
    <p>Нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
</div>
<?php include 'footer.php'; ?>
