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

// Проверка ФИО
if (empty($_POST['fullName'])) {
    print('Заполните ФИО.<br/>');
    $errors = TRUE;
} elseif (strlen($_POST['fullName']) > 150) {
    print('ФИО не должно превышать 150 символов.<br/>');
    $errors = TRUE;
} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s-]+$/u', $_POST['fullName'])) {
    print('ФИО должно содержать только буквы, пробелы и дефисы.<br/>');
    $errors = TRUE;
}

// Проверка email
if (empty($_POST['email'])) {
    print('Заполните email.<br/>');
    $errors = TRUE;
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    print('Некорректный email.<br/>');
    $errors = TRUE;
} elseif (strlen($_POST['email']) > 100) {
    print('Email не должен превышать 100 символов.<br/>');
    $errors = TRUE;
}

// Проверка телефона (необязательное поле)
if (!empty($_POST['phone'])) {
    $phone = $_POST['phone'];
    // Убираем пробелы и плюсы для подсчета цифр
    $digitsOnly = preg_replace('/[\s\+]/', '', $phone);
    
    if (!preg_match('/^[\d\s\+]+$/', $phone)) {
        print('Телефон может содержать только цифры, пробелы и символ +.<br/>');
        $errors = TRUE;
    } elseif (strlen($digitsOnly) != 10) {
        print('Телефон должен содержать ровно 10 цифр.<br/>');
        $errors = TRUE;
    } elseif (strlen($phone) > 20) {
        print('Телефон не должен превышать 20 символов.<br/>');
        $errors = TRUE;
    }
}

// Проверка даты рождения
if (empty($_POST['birthdate'])) {
    print('Заполните дату рождения.<br/>');
    $errors = TRUE;
} else {
    $date = DateTime::createFromFormat('Y-m-d', $_POST['birthdate']);
    if (!$date || $date->format('Y-m-d') !== $_POST['birthdate']) {
        print('Некорректная дата рождения.<br/>');
        $errors = TRUE;
    }
}

// Проверка пола
if (empty($_POST['gender'])) {
    print('Выберите пол.<br/>');
    $errors = TRUE;
} elseif (!in_array($_POST['gender'], ['male', 'female'])) {
    print('Некорректное значение пола.<br/>');
    $errors = TRUE;
}

// Проверка языков
if (empty($_POST['languages'])) {
    print('Выберите хотя бы один язык программирования.<br/>');
    $errors = TRUE;
} else {
    $allowed_langs = ['pascal','c','cpp','javascript','php','python','java','haskell','clojure','prolog','scala','go'];
    foreach ($_POST['languages'] as $lang) {
        if (!in_array($lang, $allowed_langs)) {
            print('Некорректный язык программирования.<br/>');
            $errors = TRUE;
            break;
        }
    }
}

// Проверка биографии
if (empty($_POST['message'])) {
    print('Заполните биографию.<br/>');
    $errors = TRUE;
} elseif (strlen($_POST['message']) < 4) {
    print('Биография должна содержать минимум 4 символа.<br/>');
    $errors = TRUE;
} elseif (strlen($_POST['message']) > 65535) {
    print('Биография слишком длинная.<br/>');
    $errors = TRUE;
}

// Проверка чекбокса
if (!isset($_POST['contract'])) {
    print('Подтвердите ознакомление с контрактом.<br/>');
    $errors = TRUE;
}

if ($errors) {
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
        1
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
    
} catch(PDOException $e) {
    $db->rollBack();
    print('Ошибка базы данных: ' . $e->getMessage());
    exit();
}

header('Location: ?save=1');
?>
