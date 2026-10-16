<?php

declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$currentUser = $_SESSION['auth'] ?? null;
if (
    !is_array($currentUser)
    || !in_array((int) ($currentUser['role_id'] ?? 0), array(1, 2), true)
) {
    header('Location: ./');
    exit;
}

require_once __DIR__ . '/../../Controllers/UserController.php';

$result = null;
$userId = (int) $currentUser['user_id'];
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $result = UserController::updateProfile($_POST, $userId);

    if ($result['success']) {
        $_SESSION['auth']['full_name'] = $result['full_name'];
    }
}

$profile = UserController::profile($userId);
if (!is_array($profile)) {
    header('Location: ./');
    exit;
}

if (is_array($result)) {
    $profile['username'] = (string) ($result['username'] ?? $_POST['username'] ?? $profile['username']);
    $profile['email'] = (string) ($result['email'] ?? $_POST['email'] ?? $profile['email']);
    $profile['full_name'] = (string) ($result['full_name'] ?? $_POST['full_name'] ?? $profile['full_name']);
}

function profileEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$isAdmin = (int) ($currentUser['role_id'] ?? 0) === 1;
$returnUrl = $isAdmin ? '?page=admin' : './';
$accountType = $isAdmin ? 'Quản trị viên' : 'Tài khoản người dùng';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông tin cá nhân - Football News</title>
    <link rel="stylesheet" href="./public/assets/css/register.css">
    <link rel="stylesheet" href="./public/assets/css/change-password.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="change-password-body">
    <main class="change-password-page profile-page">
        <section class="change-password-intro">
            <a class="change-password-brand" href="./"><span class="change-password-brand-mark"><i class="fa-solid fa-futbol"></i></span>Football News</a>
            <div class="change-password-intro-content">
                <span class="change-password-kicker"><i class="fa-solid fa-user-pen"></i> Tài khoản của bạn</span>
                <h1>Cập nhật thông tin,<br><strong>trải nghiệm tốt hơn.</strong></h1>
                <p>Thông tin cá nhân giúp tài khoản của bạn luôn chính xác và dễ nhận diện trong cộng đồng thể thao.</p>
                <div class="change-password-tip"><i class="fa-solid fa-shield-halved"></i><span>Thông tin của bạn được bảo mật an toàn.</span></div>
            </div>
        </section>
        <section class="change-password-card">
            <a class="change-password-back" href="<?php echo profileEscape($returnUrl); ?>"><i class="fa-solid fa-arrow-left"></i> <?php echo $isAdmin ? 'Về trang quản trị' : 'Về trang chủ'; ?></a>
            <div class="change-password-heading">
                <div class="change-password-icon"><i class="fa-solid fa-user-pen"></i></div>
                <span><?php echo profileEscape($accountType); ?></span>
                <h2>Thông tin cá nhân</h2>
                <p>Cập nhật các thông tin bên dưới rồi lưu thay đổi.</p>
            </div>
            <?php if (is_array($result) && !$result['success']) : ?>
                <div class="change-password-alert" role="alert"><i class="fa-solid fa-circle-exclamation"></i> <?php echo profileEscape((string) $result['message']); ?></div>
            <?php elseif (is_array($result)) : ?>
                <div class="change-password-success profile-success" role="status"><i class="fa-solid fa-check"></i> <?php echo profileEscape((string) $result['message']); ?></div>
            <?php endif; ?>
            <form method="post" class="change-password-form profile-form">
                <label for="profile-full-name">Họ và tên</label>
                <div class="change-password-input"><i class="fa-solid fa-user"></i><input id="profile-full-name" name="full_name" type="text" maxlength="255" value="<?php echo profileEscape((string) $profile['full_name']); ?>" autocomplete="name" required></div>
                <label for="profile-username">Username</label>
                <div class="change-password-input"><i class="fa-solid fa-at"></i><input id="profile-username" name="username" type="text" maxlength="100" value="<?php echo profileEscape((string) $profile['username']); ?>" autocomplete="username" required></div>
                <label for="profile-email">Email</label>
                <div class="change-password-input"><i class="fa-solid fa-envelope"></i><input id="profile-email" name="email" type="email" maxlength="255" value="<?php echo profileEscape((string) $profile['email']); ?>" autocomplete="email" required></div>
                <button class="change-password-submit" type="submit"><i class="fa-solid fa-floppy-disk"></i> Lưu thông tin</button>
            </form>
            <p class="change-password-footer">Muốn đổi mật khẩu? <a href="?page=change-password">Đổi mật khẩu</a></p>
        </section>
    </main>
</body>
</html>
