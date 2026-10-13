<?php

declare(strict_types=1);

$page = $_GET['page'] ?? 'home';

if ($page === 'detail') {
    require __DIR__ . '/app/Views/pages/detail.php';
    exit;
}

if ($page === 'register') {
    require __DIR__ . '/app/Views/pages/register.php';
    exit;
}

require __DIR__ . '/app/Views/pages/home.php';