<?php
// error_handler.php

function log_custom_error($type, $message, $file = '', $line = '', $exit = false)
{
    $datetime = date("Y-m-d H:i:s");
    $types = [
        'error'    => 'Ошибка',
        'warning'  => 'Предупреждение',
        'notice'   => 'Уведомление',
        'validate' => 'Ошибка валидации',
        'captcha'  => 'Ошибка капчи',
        'db'       => 'Ошибка базы данных',
    ];
    $typename = $types[strtolower($type)] ?? 'Неизвестный тип';

    $log = "[{$datetime}] {$typename}: {$message}";
    if ($file !== '') $log .= " в {$file}";
    if ($line !== '') $log .= " на строке {$line}";
    $log .= "\n";

    $log_dir = __DIR__ . '/logs';
    $log_file = $log_dir . '/errors.log';
    if (!file_exists($log_dir)) {
        mkdir($log_dir, 0777, true);
    }

    file_put_contents($log_file, $log, FILE_APPEND);

    if (!isset($_SESSION['flash_errors'])) {
        $_SESSION['flash_errors'] = [];
    }

    $typename = $types[strtolower($type)] ?? 'Неизвестный тип';
    $_SESSION['flash_errors'][] = "[{$typename}] {$message}";

    if ($exit) exit("<div>Работа остановлена.</div>");
}

function validate_email($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        log_custom_error('validate', "Некорректный формат email: $email", __FILE__, __LINE__);
        return false;
    }
    return true;
}

function validate_password($pass)
{
    if (mb_strlen($pass) < 6) {
        log_custom_error('validate', "Пароль должен быть не менее 6 символов", __FILE__, __LINE__);
        return false;
    }
    return true;
}

function validate_unique_email($connection, $email)
{
    $stmt = $connection->prepare("SELECT 1 FROM `Импортеры` WHERE `email` = ?");
    if (!$stmt) {
        log_custom_error('db', "Ошибка подготовки запроса: " . $connection->error, __FILE__, __LINE__);
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        log_custom_error('validate', "Пользователь с таким email уже зарегистрирован", __FILE__, __LINE__);
        return false;
    }

    return true;
}

function validate_recaptcha($captchaResponse, $secretKey)
{
    if (empty($captchaResponse)) {
        log_custom_error('captcha', "Не пройдена проверка reCAPTCHA", __FILE__, __LINE__);
        return false;
    }

    $verify = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey)
        . '&response=' . urlencode($captchaResponse)
    );
    $data = json_decode($verify, true);

    if (empty($data['success'])) {
        $codes = implode(', ', $data['error-codes'] ?? []);
        log_custom_error('captcha', "Ошибка reCAPTCHA: $codes", __FILE__, __LINE__);
        return false;
    }

    return true;
}

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        $fatalErrors = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];
        if (in_array($error['type'], $fatalErrors)) {
            $dt = date("Y-m-d H:i:s (T)");
            $errortype = [
                E_ERROR             => 'Ошибка',
                E_CORE_ERROR        => 'Ошибка ядра',
                E_COMPILE_ERROR     => 'Ошибка компиляции',
                E_PARSE             => 'Ошибка разбора исходного кода',
            ];
            $type = $errortype[$error['type']] ?? 'Фатальная ошибка';

            $err = "<errorentry>\n";
            $err .= "\t<datetime>$dt</datetime>\n";
            $err .= "\t<errornum>{$error['type']}</errornum>\n";
            $err .= "\t<errortype>$type</errortype>\n";
            $err .= "\t<errormsg>{$error['message']}</errormsg>\n";
            $err .= "\t<scriptname>{$error['file']}</scriptname>\n";
            $err .= "\t<scriptlinenum>{$error['line']}</scriptlinenum>\n";
            $err .= "</errorentry>\n\n";

            error_log($err, 3, "errors.log");

            echo "<pre>$err</pre>";
        }
    }
});