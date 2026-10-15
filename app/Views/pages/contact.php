<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$siteName = 'Football News';
$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$currentUser = $_SESSION['auth'] ?? null;

function contactEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Liên hệ Football News: góp ý nội dung, hợp tác truyền thông hoặc báo lỗi website.">
    <title>Liên hệ - <?php echo contactEscape($siteName); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="<?php echo contactEscape($applicationPath . '/public/assets/css/contact.css'); ?>">
</head>
<body>
    <header class="hero">
        <i class="fa-solid fa-futbol hero-ball" aria-hidden="true"></i>
        <div class="container">
            <div class="nav-bar">
                <a class="brand" href="./">
                    <span class="brand-mark"><i class="fa-solid fa-futbol"></i></span>
                    <?php echo contactEscape($siteName); ?>
                </a>
                <nav class="nav" aria-label="Điều hướng chính">
                    <a href="./">Trang chủ</a>
                    <a href="?page=articles">Tin tức</a>
                    <a class="is-active" href="?page=contact" aria-current="page">Liên hệ</a>
                    <?php if (is_array($currentUser)) : ?>
                        <a href="?page=logout">Đăng xuất</a>
                    <?php else : ?>
                        <a href="?page=login">Đăng nhập</a>
                    <?php endif; ?>
                </nav>
            </div>

            <div class="hero-copy">
                <span class="badge"><i></i> Phản hồi trong 24 giờ làm việc</span>
                <h1>Liên hệ với <span>Football News</span></h1>
                <p>Góp ý nội dung, đề xuất hợp tác hay báo lỗi website, đội ngũ của chúng tôi luôn sẵn sàng lắng nghe bạn.</p>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <div class="contact-card">
                <aside class="info">
                    <h2>Thông tin liên hệ</h2>
                    <p>Chọn cách thuận tiện nhất để kết nối với chúng tôi.</p>
                    <ul class="info-list">
                        <li><a class="info-item" href="mailto:hello@footballnews.vn">
                            <span class="info-icon"><i class="fa-regular fa-envelope"></i></span>
                            <span><small>Email</small><strong>hello@footballnews.vn</strong></span>
                        </a></li>
                        <li><a class="info-item" href="tel:+842812345678">
                            <span class="info-icon"><i class="fa-solid fa-phone"></i></span>
                            <span><small>Điện thoại</small><strong>+84 (28) 1234 5678</strong></span>
                        </a></li>
                        <li><div class="info-item">
                            <span class="info-icon"><i class="fa-solid fa-location-dot"></i></span>
                            <span><small>Văn phòng</small><strong>TP. Hồ Chí Minh, Việt Nam</strong></span>
                        </div></li>
                    </ul>
                    <div class="mini-map" aria-label="Bản đồ vị trí văn phòng">
                        <span class="map-road map-road-one"></span><span class="map-road map-road-two"></span><span class="map-road map-road-three"></span>
                        <i class="fa-solid fa-location-dot map-pin"></i>
                        <strong>Football News</strong><small>TP. Hồ Chí Minh, Việt Nam</small>
                    </div>
                    <div class="socials">
                        <span>Theo dõi:</span>
                        <a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </aside>

                <section class="form-panel" aria-labelledby="form-title">
                    <h2 id="form-title">Gửi tin nhắn cho chúng tôi</h2>
                    <p class="lead">Điền thông tin bên dưới, chúng tôi sẽ phản hồi qua email.</p>

                    <form id="contact-form" novalidate>
                        <fieldset id="topic-group">
                            <legend>Bạn muốn trao đổi về</legend>
                            <div class="topics">
                                <label class="topic"><input type="radio" name="subject" value="Góp ý nội dung"><span>Góp ý nội dung</span></label>
                                <label class="topic"><input type="radio" name="subject" value="Hợp tác truyền thông"><span>Hợp tác truyền thông</span></label>
                                <label class="topic"><input type="radio" name="subject" value="Báo lỗi website"><span>Báo lỗi website</span></label>
                                <label class="topic"><input type="radio" name="subject" value="Chủ đề khác"><span>Chủ đề khác</span></label>
                            </div>
                            <p class="error">Vui lòng chọn một chủ đề.</p>
                        </fieldset>

                        <div class="row">
                            <label class="field"><span>Họ và tên</span>
                                <span class="control"><i class="fa-regular fa-user"></i><input type="text" name="name" autocomplete="name" placeholder="Nguyễn Văn A" required></span>
                                <em class="error">Vui lòng nhập họ và tên.</em>
                            </label>
                            <label class="field"><span>Email</span>
                                <span class="control"><i class="fa-regular fa-envelope"></i><input type="email" name="email" autocomplete="email" placeholder="ban@example.com" required></span>
                                <em class="error">Vui lòng nhập email hợp lệ.</em>
                            </label>
                        </div>

                        <label class="field"><span>Nội dung</span>
                            <span class="control"><i class="fa-regular fa-comment-dots"></i><textarea name="message" rows="5" placeholder="Bạn muốn nói điều gì?" required></textarea></span>
                            <em class="error">Vui lòng nhập nội dung tin nhắn.</em>
                        </label>

                        <button class="submit" type="submit">Gửi tin nhắn <i class="fa-solid fa-paper-plane"></i></button>
                        <p class="privacy"><i class="fa-solid fa-lock"></i> Thông tin của bạn được bảo mật.</p>
                    </form>
                </section>
            </div>
        </div>

        <section class="newsletter container">
            <i class="fa-regular fa-envelope newsletter-icon" aria-hidden="true"></i>
            <div><h2>Đăng ký nhận bản tin</h2><p>Nhận thông báo tin tức bóng đá mới nhất mỗi ngày!</p></div>
            <form id="newsletter-form"><input type="email" placeholder="Nhập email của bạn" aria-label="Email nhận bản tin" required><button type="submit">Đăng ký</button></form>
        </section>

        <section class="faq container" aria-labelledby="faq-title">
            <div class="faq-head">
                <h2 id="faq-title">Câu hỏi thường gặp</h2>
                <p>Có thể bạn tìm thấy câu trả lời ngay tại đây.</p>
            </div>
            <div class="faq-list">
                <details><summary>Bao lâu tôi sẽ nhận được phản hồi? <i class="fa-solid fa-plus"></i></summary><p>Thông thường, ban biên tập sẽ phản hồi trong vòng 24 giờ làm việc.</p></details>
                <details><summary>Tôi có thể gửi bài viết cho Football News không? <i class="fa-solid fa-plus"></i></summary><p>Được. Hãy chọn chủ đề “Hợp tác truyền thông” và mô tả ý tưởng của bạn trong tin nhắn.</p></details>
                <details><summary>Làm sao để báo lỗi nội dung? <i class="fa-solid fa-plus"></i></summary><p>Hãy gửi kèm đường dẫn bài viết và mô tả lỗi để chúng tôi kiểm tra nhanh nhất.</p></details>
            </div>
        </section>
    </main>

    <div class="toast" id="contact-toast" role="status" aria-live="polite">
        <span class="toast-icon"><i class="fa-solid fa-check"></i></span>
        <span><strong>Đã gửi tin nhắn</strong><small>Chúng tôi sẽ phản hồi qua email của bạn.</small></span>
        <button type="button" aria-label="Đóng thông báo"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div><strong><i class="fa-solid fa-futbol"></i> <?php echo contactEscape($siteName); ?></strong><small>Tin tức bóng đá - Kết nối đam mê</small><span>© 2026 Football News. Bảo lưu mọi quyền.</span></div>
            <div><b>Liên kết nhanh</b><a href="./">Trang chủ</a><a href="?page=articles">Tin tức</a><a href="?page=contact">Liên hệ</a></div>
            <div><b>Kết nối với chúng tôi</b><div class="footer-socials"><a href="#"><i class="fa-brands fa-facebook-f"></i></a><a href="#"><i class="fa-brands fa-youtube"></i></a><a href="#"><i class="fa-brands fa-instagram"></i></a><a href="#"><i class="fa-brands fa-tiktok"></i></a></div></div>
        </div>
    </footer>

    <script>
        (function () {
            var form = document.getElementById('contact-form');
            var toast = document.getElementById('contact-toast');
            var timer;

            function setError(el, hasError) { el.classList.toggle('has-error', hasError); }

            function validate() {
                var ok = true;
                var topic = form.querySelector('input[name="subject"]:checked');
                setError(document.getElementById('topic-group'), !topic);
                if (!topic) ok = false;

                ['name', 'email', 'message'].forEach(function (name) {
                    var input = form.elements[name];
                    var valid = input.value.trim() !== '' && input.checkValidity();
                    setError(input.closest('.field'), !valid);
                    if (!valid) ok = false;
                });
                return ok;
            }

            function hideToast() {
                window.clearTimeout(timer);
                toast.classList.remove('is-visible');
            }

            form.addEventListener('input', function (e) {
                var field = e.target.closest('.field');
                if (field) setError(field, false);
            });
            form.addEventListener('change', function (e) {
                if (e.target.name === 'subject') setError(document.getElementById('topic-group'), false);
            });
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                if (!validate()) return;
                form.reset();
                toast.classList.add('is-visible');
                window.clearTimeout(timer);
                timer = window.setTimeout(hideToast, 5000);
            });
            toast.querySelector('button').addEventListener('click', hideToast);
            document.getElementById('newsletter-form').addEventListener('submit', function (event) {
                event.preventDefault();
                this.reset();
                toast.querySelector('strong').textContent = 'Đăng ký thành công';
                toast.querySelector('small').textContent = 'Bạn sẽ nhận được những tin tức mới nhất.';
                toast.classList.add('is-visible');
                window.clearTimeout(timer);
                timer = window.setTimeout(hideToast, 5000);
            });
        }());
    </script>
</body>
</html>