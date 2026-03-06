<?php
header('Content-Type: text/html; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        print('Спасибо, результаты сохранены.');
    }
    include('form.php');
    exit();
}

// Проверяем ошибки
$errors = FALSE;
$error_messages = [];

// Проверка ФИО
if (empty($_POST['fullName'])) {
    $error_messages[] = 'Заполните ФИО.';
    $errors = TRUE;
} elseif (strlen($_POST['fullName']) > 150) {
    $error_messages[] = 'ФИО не должно превышать 150 символов.';
    $errors = TRUE;
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]+$/u', $_POST['fullName'])) {
    $error_messages[] = 'ФИО должно содержать только буквы, пробелы и дефисы.';
    $errors = TRUE;
}

// Проверка email
if (empty($_POST['email'])) {
    $error_messages[] = 'Заполните email.';
    $errors = TRUE;
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $error_messages[] = 'Некорректный email.';
    $errors = TRUE;
} elseif (strlen($_POST['email']) > 100) {
    $error_messages[] = 'Email не должен превышать 100 символов.';
    $errors = TRUE;
}

// Проверка телефона (необязательное поле)
if (!empty($_POST['phone'])) {
    $phone = $_POST['phone'];
    // Убираем пробелы и плюсы для подсчета цифр
    $digitsOnly = preg_replace('/[\s\+]/', '', $phone);
    
    if (!preg_match('/^[\d\s\+]+$/', $phone)) {
        $error_messages[] = 'Телефон может содержать только цифры, пробелы и символ +.';
        $errors = TRUE;
    } elseif (strlen($digitsOnly) != 10) {
        $error_messages[] = 'Телефон должен содержать ровно 10 цифр.';
        $errors = TRUE;
    } elseif (strlen($phone) > 20) {
        $error_messages[] = 'Телефон не должен превышать 20 символов.';
        $errors = TRUE;
    }
}

// Проверка даты рождения
if (empty($_POST['birthdate'])) {
    $error_messages[] = 'Заполните дату рождения.';
    $errors = TRUE;
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birthdate']) {
        $error_messages[] = 'Некорректная дата рождения.';
        $errors = TRUE;
    }
}

// Проверка пола
if (empty($_POST['gender'])) {
    $error_messages[] = 'Выберите пол.';
    $errors = TRUE;
} elseif (!in_array($_POST['gender'], ['male', 'female'])) {
    $error_messages[] = 'Некорректное значение пола.';
    $errors = TRUE;
}

// Проверка языков
if (empty($_POST['languages']) || !is_array($_POST['languages'])) {
    $error_messages[] = 'Выберите хотя бы один язык программирования.';
    $errors = TRUE;
} else {
    $allowed_langs = ['pascal','c','cpp','javascript','php','python','java','haskell','clojure','prolog','scala','go'];
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_langs)) {
            $error_messages[] = 'Некорректный язык программирования.';
            $errors = TRUE;
            break;
        }
    }
}

// Проверка биографии
if (empty($_POST['message'])) {
    $error_messages[] = 'Заполните биографию.';
    $errors = TRUE;
} elseif (strlen($_POST['message']) < 4) {
    $error_messages[] = 'Биография должна содержать минимум 4 символа.';
    $errors = TRUE;
} elseif (strlen($_POST['message']) > 65535) {
    $error_messages[] = 'Биография слишком длинная.';
    $errors = TRUE;
}

// Проверка чекбокса
if (!isset($_POST['contract']) || $_POST['contract'] !== 'on') {
    $error_messages[] = 'Подтвердите ознакомление с контрактом.';
    $errors = TRUE;
}

if ($errors) {
    // Выводим все ошибки
    foreach ($error_messages as $message) {
        print($message . '<br/>');
    }
    exit();
}

// Сохранение в базу данных
$user = 'u82278';
$pass = '3700374';
$db = new PDO('mysql:host=localhost;dbname=u82278', $user, $pass,
    [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

try {
    // Начинаем транзакцию
    $db->beginTransaction();
    
    // Вставляем основную информацию
    $stmt = $db->prepare("INSERT INTO application (fio, phone, email, birth_date, gender, bio, contract) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['fullName'],
        $_POST['phone'] ?? '',
        $_POST['email'],
        $_POST['birthdate'],
        $_POST['gender'],
        $_POST['message'],
        isset($_POST['contract']) && $_POST['contract'] === 'on' ? 1 : 0
    ]);
    
    $app_id = $db->lastInsertId();
    
    // Получаем соответствие кодов языков и ID из базы
    $lang_map = [];
    $stmt = $db->query("SELECT id, code FROM languages");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $lang_map[$row['code']] = $row['id'];
    }
    
    // Вставляем выбранные языки
    $stmt = $db->prepare("INSERT INTO app_languages (app_id, lang_id) VALUES (?, ?)");
    foreach ($_POST['languages'] as $lang) {
        if (isset($lang_map[$lang])) {
            $stmt->execute([$app_id, $lang_map[$lang]]);
        }
    }
    
    // Подтверждаем транзакцию
    $db->commit();
    
    // Успешное сохранение - редирект
    header('Location: ?save=1');
    exit();
    
} catch(PDOException $e) {
    $db->rollBack();
    print('Ошибка базы данных: ' . $e->getMessage());
    exit();
}
?>
