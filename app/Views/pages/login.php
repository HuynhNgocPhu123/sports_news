<?php

declare(strict_types=1);

$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$loginResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Controllers/AuthController.php';
    $loginResult = AuthController::login($_POST);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($applicationPath . '/public/assets/css/login.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Đăng nhập - Football News</title>
</head>
<body>
    <?php if (is_array($loginResult)) : ?>
        <script>
            <?php if ($loginResult['success']) : ?>
                window.alert(<?php echo json_encode('Đăng nhập thành công với role: ' . $loginResult['role_name'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
                window.location.href = <?php echo json_encode((int) $loginResult['role_id'] === 1 ? '?page=admin' : './', JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
            <?php else : ?>
                window.alert(<?php echo json_encode($loginResult['message'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
            <?php endif; ?>
        </script>
    <?php endif; ?>
    <main class="login-page">
        <section class="login-intro">
            <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span>Football News</a>
            <h1>Chào mừng bạn trở lại</h1>
            <p>Đăng nhập để tiếp tục theo dõi những tin tức thể thao mới nhất.</p>
            <ul class="intro-list">
                <li><i class="fa-solid fa-bolt"></i> Cập nhật tin tức nhanh chóng</li>
                <li><i class="fa-regular fa-bookmark"></i> Lưu lại bài viết yêu thích</li>
                <li><i class="fa-regular fa-comment"></i> Tham gia bình luận cùng cộng đồng</li>
            </ul>
        </section>
        <section class="login-card">
            <h2>Đăng nhập</h2>
            <p>Nhập thông tin tài khoản của bạn để tiếp tục.</p>
            <form action="?page=login" method="post">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-envelope"></i>
                        <input id="email" name="email" type="email" placeholder="you@example.com" autocomplete="email" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Mật khẩu</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-lock"></i>
                        <input id="password" name="password" type="password" placeholder="Nhập mật khẩu" autocomplete="current-password" required>
                    </div>
                </div>
                <div class="form-options">
                    <label class="remember"><input type="checkbox" name="remember"> Ghi nhớ đăng nhập</label>
                    <a class="forgot-password" href="#">Quên mật khẩu?</a>
                </div>
                <button class="submit-button" type="submit">Đăng nhập <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <p class="register-link">Bạn chưa có tài khoản? <a href="?page=register">Đăng ký ngay</a></p>
            <a class="back-home" href="./"><i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ</a>
        </section>
    </main>
</body>
</html>
