<?php
session_start();
require_once 'config/db.php';

function formatUserName($name) {
    return ucfirst(strtolower($name));
}

function shortenText($text, $length = 150) {
    if(strlen($text) > $length) {
        return substr($text, 0, $length) . '...';
    }
    return $text;
}

function formatDate($date) {
    $months = ['', 'Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun', 
                'Iyul', 'Avgust', 'Sentyabr', 'Oktyabr', 'Noyabr', 'Dekabr'];
    $timestamp = strtotime($date);
    $day = date('d', $timestamp);
    $month = $months[date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return "$day-$month-$year";
}

function generateCaptcha() {
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $operators = ['+', '-', '*'];
    $operator = $operators[array_rand($operators)];
    
    switch($operator) {
        case '+':
            $answer = $num1 + $num2;
            break;
        case '-':
            if($num1 < $num2) {
                $temp = $num1;
                $num1 = $num2;
                $num2 = $temp;
            }
            $answer = $num1 - $num2;
            break;
        case '*':
            $num1 = rand(1, 5);
            $num2 = rand(1, 5);
            $answer = $num1 * $num2;
            break;
    }
    
    $_SESSION['captcha_answer'] = $answer;
    $_SESSION['captcha_question'] = "$num1 $operator $num2 = ?";
}

function checkCaptcha($userAnswer) {
    if(!isset($_SESSION['captcha_answer'])) {
        return false;
    }
    return intval($userAnswer) === $_SESSION['captcha_answer'];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if(!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function generateCSRFToken() {
    if(!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Portfolio'; ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="index.php" class="logo">Portfolio</a>
            <ul class="nav-links">
                <li><a href="index.php">Bosh sahifa</a></li>
                <li><a href="projects.php">Loyihalar</a></li>
                <?php if(isLoggedIn()): ?>
                    <li><a href="dashboard.php">Kabinet</a></li>
                    <li><a href="logout.php">Chiqish</a></li>
                <?php else: ?>
                    <li><a href="login.php">Kirish</a></li>
                    <li><a href="register.php">Ro'yxatdan o'tish</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
    <main class="container">
