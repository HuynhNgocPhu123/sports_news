<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$page = $_GET['page'] ?? 'home';

if ($page === 'logout') {
    $_SESSION = array();
    session_destroy();
    header('Location: ./');
    exit;
}

if ($page === 'detail') {
    require __DIR__ . '/app/Views/pages/detail.php';
    exit;
}

if ($page === 'register') {
    require __DIR__ . '/app/Views/pages/register.php';
    exit;
}

if ($page === 'login') {
    require __DIR__ . '/app/Views/pages/login.php';
    exit;
}

require __DIR__ . '/app/Views/pages/home.php';