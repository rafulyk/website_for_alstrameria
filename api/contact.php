<?php

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    die("Метод не разрешен");
}

$name = trim(htmlspecialchars($_POST['name'] ?? ''));
$phone = trim(htmlspecialchars($_POST['phone'] ?? ''));
$message = trim(htmlspecialchars($_POST['message'] ?? ''));

if (empty($name) || empty($phone)) {
    http_response_code(400);
    die("Заполните обязательные поля: имя и телефон");
}

if (!preg_match('/^[\d\s\-\+\(\)]+$/', $phone)) {
    http_response_code(400);
    die("Неверный формат телефона");
}

$to = "rafulyk@yandex.ru"; // ЗАМЕНИ на свой email!
$subject = "🛠 Новая заявка с сайта Альстрамерия";

$email_body = "
📋 Новая заявка с сайта alstrameria.ru

👤 Имя: $name
📞 Телефон: $phone
💬 Сообщение: " . (empty($message) ? "не указано" : $message) . "

⏰ Время заявки: " . date('d.m.Y H:i:s') . "
🌐 Страница: " . ($_SERVER['HTTP_REFERER'] ?? 'неизвестно') . "
";

$headers = [
    "From: website@alstrameria.ru",
    "Reply-To: $phone",
    "Content-Type: text/plain; charset=utf-8",
    "X-Mailer: PHP/" . phpversion()
];

if (mail($to, $subject, $email_body, implode("\r\n", $headers))) {
    // Успех - возвращаем JSON для AJAX или редирект
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode(["success" => true, "message" => "Заявка отправлена!"]);
    } else {
        
        echo "
        <!DOCTYPE html>
        <html lang='ru'>
        <head>
            <meta charset='UTF-8'>
            <title>Заявка отправлена</title>
            <style>
                body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
                .success { color: green; font-size: 18px; }
                .back-link { margin-top: 20px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='success'>✅ Спасибо! Ваша заявка отправлена.</div>
            <a href='/' class='back-link'>Вернуться на сайт</a>
        </body>
        </html>
        ";
    }
} else {
    
    http_response_code(500);
    if (is_ajax_request()) {
        header('Content-Type: application/json');
        echo json_encode(["success" => false, "message" => "Ошибка сервера"]);
    } else {
        echo "❌ Произошла ошибка при отправке. Пожалуйста, попробуйте позже.";
    }
}


function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

?>