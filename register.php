<?php
require_once 'db.php';


$error   = '';
$success = '';

$siteKey   = '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI';
$secretKey = '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = $_POST['email'] ?? '';
    $pass    = $_POST['pass'] ?? '';
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    if (
        validate_recaptcha($captcha, $secretKey) &&
        validate_email($email) &&
        validate_password($pass) &&
        validate_unique_email($connection, $email)
    ) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $connection->prepare("INSERT INTO `Импортеры` (`email`, `pass`) VALUES (?, ?)");

        if (!$stmt) {
            $errorCode = mysqli_errno($connection);
            $errorText = mysqli_error($connection);
            $connectError = mysqli_connect_error();
            $error = "Ошибка подготовки запроса (код $errorCode): $errorText. Подключение: $connectError.";
        } else {
            $stmt->bind_param("ss", $email, $hash);
            if (!$stmt->execute()) {
                $errorCode = mysqli_errno($connection);
                $errorText = mysqli_error($connection);
                $error = "Ошибка выполнения запроса (код $errorCode): $errorText.";
            } else {
                $success = 'Регистрация успешна! <a href="login.php">Войти</a>';
            }
        }
    } else {
        $error = 'Форма содержит ошибки. Проверьте введённые данные.';
    }
}

?>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<?php include 'header.php'; ?>

<div class="auth-form">
    <h2>Регистрация</h2>

    <?php if (!empty($_SESSION['flash_errors'])): ?>
        <div class="flash-errors" style="background:#fdd; color:#900; padding:10px; border:1px solid #900; margin-bottom:15px;">
            <?php foreach ($_SESSION['flash_errors'] as $flash_error): ?>
                <div><?= htmlspecialchars($flash_error) ?></div>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['flash_errors']); ?>
    <?php endif; ?>

    <form method="post" action="">
        <label>Email:</label>
        <input type="email" name="email" required value="<?= htmlspecialchars($email ?? '') ?>">

        <label>Пароль:</label>
        <input type="password" name="pass" required>

        <div class="g-recaptcha" data-sitekey="<?= $siteKey ?>"></div>

        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <button type="submit" class="auth-button">Зарегистрироваться</button>

    </form>
    <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
</div>

<?php include 'footer.php'; ?>
