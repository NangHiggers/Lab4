<?php

set_error_handler('customErrorHandler');

function customErrorHandler($errno, $errstr, $errfile, $errline)
{
    $datetime = date("Y-m-d H:i:s");

    $types = [
        E_USER_ERROR      => 'Критическая ошибка',
        E_USER_WARNING    => 'Предупреждение',
        E_USER_NOTICE     => 'Уведомление',
        E_WARNING         => 'Предупреждение ',
        E_NOTICE          => 'Уведомление ',
    ];

    $type = $types[$errno] ?? "Неизвестный тип [$errno]";
    $log = "[{$datetime}] {$type}: {$errstr} в {$errfile} на строке {$errline}\n";

    $log_dir = __DIR__ . '/logs';
    if (!file_exists($log_dir)) mkdir($log_dir, 0777, true);
    file_put_contents("$log_dir/user_errors.log", $log, FILE_APPEND);

    if (!isset($_SESSION['flash_errors'])) {
        $_SESSION['flash_errors'] = [];
    }
    $_SESSION['flash_errors'][] = "[{$type}] {$errstr}";

    if ($errno === E_USER_ERROR) {
        exit("<strong>Критическая ошибка:</strong> $errstr<br>");
    }

    return true;
}


function validate_email($email)
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        trigger_error("Некорректный формат email: $email", E_USER_NOTICE);
        return false;
    }
    return true;
}

function validate_password($pass)
{
    if (mb_strlen($pass) < 6) {
        trigger_error("Пароль должен быть не менее 6 символов", E_USER_WARNING);
        return false;
    }
    return true;
}

function validate_unique_email($connection, $email)
{
    $stmt = $connection->prepare("SELECT 1 FROM `Импортеры` WHERE `email` = ?");
    if (!$stmt) {
        trigger_error("Ошибка подготовки запроса: " . $connection->error, E_USER_ERROR);
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        trigger_error("Пользователь с таким email уже зарегистрирован", E_USER_NOTICE);
        return false;
    }

    return true;
}

function validate_recaptcha($captchaResponse, $secretKey)
{
    if (empty($captchaResponse)) {
        trigger_error("Не пройдена проверка reCAPTCHA", E_USER_WARNING);
        return false;
    }

    $verify = file_get_contents(
        'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey)
        . '&response=' . urlencode($captchaResponse)
    );
    $data = json_decode($verify, true);

    if (empty($data['success'])) {
        $codes = implode(', ', $data['error-codes'] ?? []);
        trigger_error("Ошибка reCAPTCHA: $codes", E_USER_NOTICE);
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