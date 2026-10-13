<?php

declare(strict_types=1);

$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$registrationResult = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../../Controllers/AuthController.php';
    $registrationResult = AuthController::register($_POST);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($applicationPath . '/public/assets/css/register.css', ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Đăng ký - Football News</title>
</head>
<body>
    <?php if (is_array($registrationResult)) : ?>
        <div class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="registration-message-title">
            <div class="result-modal <?php echo $registrationResult['success'] ? 'success' : 'error'; ?>">
                <button class="modal-close" type="button" aria-label="Đóng thông báo" onclick="this.closest('.modal-backdrop').remove();">&times;</button>
                <div class="modal-icon"><i class="fa-solid <?php echo $registrationResult['success'] ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i></div>
                <h2 id="registration-message-title"><?php echo $registrationResult['success'] ? 'Đăng ký thành công' : 'Đăng ký chưa thành công'; ?></h2>
                <p><?php echo htmlspecialchars($registrationResult['message'], ENT_QUOTES, 'UTF-8'); ?></p>
                <?php if ($registrationResult['success']) : ?>
                    <a class="modal-action" href="./">Về trang chủ</a>
                <?php else : ?>
                    <button class="modal-action" type="button" onclick="this.closest('.modal-backdrop').remove();">Thử lại</button>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    <main class="register-page">
        <section class="register-intro">
            <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span>Football News</a>
            <h1>Tham gia cộng đồng thể thao</h1>
            <p>Đăng ký tài khoản để theo dõi những tin tức thể thao mới nhất mỗi ngày.</p>
            <ul class="intro-list">
                <li><i class="fa-solid fa-newspaper"></i> Cập nhật tin tức nhanh chóng</li>
                <li><i class="fa-regular fa-comment"></i> Tham gia bình luận bài viết</li>
                <li><i class="fa-regular fa-bookmark"></i> Lưu lại nội dung yêu thích</li>
            </ul>
        </section>
        <section class="register-card">
            <h2>Tạo tài khoản</h2>
            <p>Điền thông tin để bắt đầu sử dụng Football News.</p>
            <form action="?page=register" method="post">
                <div class="form-group">
                    <label for="full_name">Họ và tên</label>
                    <div class="input-wrap">
                        <i class="fa-regular fa-user"></i>
                        <input id="full_name" name="full_name" type="text" placeholder="Nhập họ và tên" autocomplete="name" required>
                    </div>
                </div>
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
                        <input id="password" name="password" type="password" placeholder="Tối thiểu 8 ký tự" autocomplete="new-password" minlength="8" required>
                    </div>
                    <span class="password-note">Mật khẩu nên có chữ hoa, chữ thường và số.</span>
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Xác nhận mật khẩu</label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-shield-halved"></i>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Nhập lại mật khẩu" autocomplete="new-password" minlength="8" required>
                    </div>
                </div>
                <label class="terms">
                    <input type="checkbox" name="terms" required>
                    <span>Tôi đồng ý với <a href="#">điều khoản sử dụng</a> và <a href="#">chính sách bảo mật</a>.</span>
                </label>
                <button class="submit-button" type="submit">Đăng ký tài khoản <i class="fa-solid fa-arrow-right"></i></button>
            </form>
            <p class="login-link">Bạn đã có tài khoản? <a href="?page=login">Đăng nhập</a></p>
            <a class="back-home" href="./"><i class="fa-solid fa-arrow-left"></i> Quay lại trang chủ</a>
        </section>
    </main>
</body>
</html>
