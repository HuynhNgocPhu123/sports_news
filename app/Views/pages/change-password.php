<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentUser = $_SESSION['auth'] ?? null;
if (!is_array($currentUser) || (int) ($currentUser['role_id'] ?? 0) !== 2) {
    header('Location: ./');
    exit;
}

require_once __DIR__ . '/../../Controllers/UserController.php';

$result = null;
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = UserController::changePassword($_POST, (int) $currentUser['user_id']);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$siteName = 'Football News';
$passwordChanged = is_array($result) && $result['success'];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - <?php echo e($siteName); ?></title>
    <link rel="stylesheet" href="./public/assets/css/register.css">
    <link rel="stylesheet" href="./public/assets/css/change-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="change-password-body">
    <main class="change-password-page">
        <section class="change-password-intro">
            <a class="change-password-brand" href="./"><span class="change-password-brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo e($siteName); ?></a>
            <div class="change-password-intro-content">
                <span class="change-password-kicker"><i class="fa-solid fa-lock"></i> Bảo mật tài khoản</span>
                <h1>Một mật khẩu mới,<br><strong>một lớp bảo vệ tốt hơn.</strong></h1>
                <p>Đổi mật khẩu định kỳ giúp tài khoản của bạn an toàn hơn khi đọc và tương tác cùng cộng đồng thể thao.</p>
                <div class="change-password-tip"><i class="fa-solid fa-shield-halved"></i><span>Không chia sẻ mật khẩu với bất kỳ ai.</span></div>
            </div>
        </section>
        <section class="change-password-card">
            <a class="change-password-back" href="./"><i class="fa-solid fa-arrow-left"></i> Về trang chủ</a>
            <div class="change-password-heading">
                <div class="change-password-icon"><i class="fa-solid fa-key"></i></div>
                <span>Xin chào, <?php echo e((string) $currentUser['full_name']); ?></span>
                <h2>Đổi mật khẩu</h2>
                <p>Nhập thông tin bên dưới để cập nhật mật khẩu của bạn.</p>
            </div>
            <?php if ($passwordChanged) : ?>
                <div class="change-password-success" role="status">
                    <div class="change-password-success-icon"><i class="fa-solid fa-check"></i></div>
                    <h3>Đổi mật khẩu thành công!</h3>
                    <p>Mật khẩu của bạn đã được cập nhật. Vui lòng xác nhận để quay lại trang chủ.</p>
                    <a class="change-password-home-button" href="./"><i class="fa-solid fa-house"></i> Xác nhận và về trang chủ</a>
                </div>
            <?php else : ?>
                <?php if (is_array($result)) : ?>
                    <div class="change-password-alert" role="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo e((string) $result['message']); ?></div>
                <?php endif; ?>
                <form method="post" class="change-password-form">
                    <label for="current-password">Mật khẩu hiện tại</label>
                    <div class="change-password-input"><i class="fa-solid fa-lock"></i><input id="current-password" name="current_password" type="password" autocomplete="current-password" required></div>
                    <label for="new-password">Mật khẩu mới</label>
                    <div class="change-password-input"><i class="fa-solid fa-key"></i><input id="new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" required></div>
                    <label for="new-password-confirmation">Xác nhận mật khẩu mới</label>
                    <div class="change-password-input"><i class="fa-solid fa-check"></i><input id="new-password-confirmation" name="new_password_confirmation" type="password" minlength="8" autocomplete="new-password" required></div>
                    <small class="change-password-note"><i class="fa-solid fa-circle-info"></i> Mật khẩu mới cần có ít nhất 8 ký tự.</small>
                    <button class="change-password-submit" type="submit"><i class="fa-solid fa-check"></i> Cập nhật mật khẩu</button>
                </form>
            <?php endif; ?>
            <p class="change-password-footer">Bạn nhớ mật khẩu rồi? <a href="./">Quay lại trang chủ</a></p>
        </section>
    </main>
</body>
</html>
