<?php

declare(strict_types=1);

$siteName = 'Football News';
$categories = array();
$categoryError = '';

try {
    require_once __DIR__ . '/../../Config/Database.php';
    $mysqli = Database::connect();
    $categoryResult = $mysqli->query(
        'SELECT category_id, category_name, slug, images, description
         FROM categories
         ORDER BY category_id ASC'
    );
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
    $categoryResult->free();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $categoryError = $exception->getMessage();
} finally {
    if (isset($mysqli) && $mysqli instanceof mysqli) {
        $mysqli->close();
    }
}

function detailEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$article = array(
    'category' => 'Bóng đá',
    'title' => 'Man Utd xác nhận kế hoạch chiêu mộ tiền đạo hàng đầu châu Âu',
    'lead' => 'Manchester United đang lên kế hoạch chiêu mộ một tiền đạo đẳng cấp nhằm cải thiện hàng công trong kỳ chuyển nhượng mùa hè 2025.',
    'author' => 'Minh Anh',
    'date' => '12/08/2025 - 08:30',
    'comments' => '24 bình luận',
    'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1200&q=85',
);
$relatedArticles = array(
    array('title' => 'Ronaldo xác nhận tương lai, người hâm mộ có thể yên tâm', 'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=300&q=80'),
    array('title' => 'Đội tuyển Việt Nam hội quân, hướng tới vòng loại Asian Cup 2027', 'image' => 'https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=300&q=80'),
    array('title' => 'Real Madrid thắng đậm ngày ra quân La Liga 2025/26', 'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=300&q=80'),
);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo detailEscape($applicationPath . '/public/assets/css/detail.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo detailEscape($article['title']); ?> - <?php echo detailEscape($siteName); ?></title>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="container topbar-inner">
                <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo detailEscape($siteName); ?></a>
                <div class="top-actions">
                    <div class="search">Tìm kiếm tin tức... <i class="fa-solid fa-magnifying-glass"></i></div>
                    <a class="login" href="#">Đăng nhập</a>
                    <a class="signup" href="#">Đăng ký</a>
                </div>
            </div>
        </div>
        <nav class="nav">
            <div class="container">
                <ul class="nav-list">
                    <li><a href="./">Trang chủ</a></li>
                    <?php foreach ($categories as $category) : ?>
                        <li><a href="?category=<?php echo rawurlencode((string) $category['slug']); ?>"><?php echo detailEscape((string) $category['category_name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="breadcrumb"><a href="./">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i> <?php echo detailEscape($article['category']); ?></div>
        <div class="detail-layout">
            <article class="article">
                <div class="article-category"><?php echo detailEscape($article['category']); ?></div>
                <h1><?php echo detailEscape($article['title']); ?></h1>
                <p class="article-lead"><?php echo detailEscape($article['lead']); ?></p>
                <div class="article-meta">
                    <span><i class="fa-regular fa-user"></i> <?php echo detailEscape($article['author']); ?></span>
                    <span><i class="fa-regular fa-calendar"></i> <?php echo detailEscape($article['date']); ?></span>
                    <span><i class="fa-regular fa-comment"></i> <?php echo detailEscape($article['comments']); ?></span>
                </div>
                <img class="article-cover" src="<?php echo detailEscape($article['image']); ?>" alt="ảnh lỗi">
                <p class="image-caption">Man Utd đang tích cực chuẩn bị cho kỳ chuyển nhượng mới.</p>
                <div class="article-content">
                    <p>Manchester United đang dành sự quan tâm đặc biệt cho một trong những tiền đạo trẻ nổi bật nhất châu Âu. Đội bóng nước Anh muốn sớm hoàn tất kế hoạch tăng cường hàng công trước mùa giải mới.</p>
                    <p>Theo những thông tin mới nhất, ban lãnh đạo câu lạc bộ đã bắt đầu các cuộc đàm phán với đại diện của cầu thủ. Đây được xem là bước đi quan trọng trong kế hoạch xây dựng đội hình cạnh tranh ở mùa giải năm nay.</p>
                    <h2>Mục tiêu nâng cấp hàng công</h2>
                    <p>Hàng công là một trong những vị trí được ưu tiên cải thiện. Ban huấn luyện cần thêm một cầu thủ có tốc độ, khả năng dứt điểm và có thể thích nghi nhanh với môi trường bóng đá Anh.</p>
                    <p>Người hâm mộ đang chờ đợi những diễn biến tiếp theo trong thương vụ này. Nếu mọi việc diễn ra thuận lợi, tân binh mới có thể sớm hội quân cùng các đồng đội.</p>
                </div>
                <div class="share-row">
                    <span>Chia sẻ bài viết:</span>
                    <a class="share-button" href="#"><i class="fa-brands fa-facebook-f"></i></a>
                    <a class="share-button" href="#"><i class="fa-brands fa-x-twitter"></i></a>
                    <a class="share-button" href="#"><i class="fa-solid fa-link"></i></a>
                </div>
                <section class="rating-box">
                    <div>
                        <h2>Đánh giá bài viết</h2>
                        <p>Giúp chúng tôi nâng cao chất lượng nội dung</p>
                    </div>
                    <div class="rating-stars" aria-label="Đánh giá 5 sao">
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-solid fa-star"></i>
                        <i class="fa-regular fa-star"></i>
                        <strong>4.8/5</strong>
                    </div>
                </section>
                <section class="comments">
                    <div class="comments-heading">
                        <h2>Bình luận <span>(24)</span></h2>
                        <select aria-label="Sắp xếp bình luận">
                            <option>Mới nhất</option>
                            <option>Nhiều lượt thích</option>
                        </select>
                    </div>
                    <form class="comment-form" action="#" method="post">
                        <textarea name="comment" rows="3" placeholder="Chia sẻ suy nghĩ của bạn về bài viết..." required></textarea>
                        <div class="comment-form-footer">
                            <span><i class="fa-regular fa-face-smile"></i> Hãy bình luận văn minh</span>
                            <button type="submit">Gửi bình luận <i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </form>
                    <div class="comment-item">
                        <div class="comment-avatar">NA</div>
                        <div class="comment-body">
                            <strong>Nguyễn Anh</strong><span class="comment-time"> · 2 giờ trước</span>
                            <p>Hy vọng thương vụ này sẽ sớm hoàn tất. Đội hình Man Utd rất cần một tiền đạo chất lượng.</p>
                            <a href="#"><i class="fa-regular fa-thumbs-up"></i> Thích</a>
                            <a href="#">Trả lời</a>
                        </div>
                    </div>
                    <div class="comment-item">
                        <div class="comment-avatar comment-avatar-blue">TH</div>
                        <div class="comment-body">
                            <strong>Thanh Hà</strong><span class="comment-time"> · 5 giờ trước</span>
                            <p>Bài viết cập nhật thông tin rất nhanh và dễ hiểu.</p>
                            <a href="#"><i class="fa-regular fa-thumbs-up"></i> Thích</a>
                            <a href="#">Trả lời</a>
                        </div>
                    </div>
                </section>
            </article>

            <aside class="sidebar">
                <h2 class="sidebar-title">Bài viết liên quan</h2>
                <?php foreach ($relatedArticles as $relatedArticle) : ?>
                    <a class="related-list related-item" href="?page=detail">
                        <img src="<?php echo detailEscape($relatedArticle['image']); ?>" alt="ảnh lỗi">
                        <h3><?php echo detailEscape($relatedArticle['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
                <h2 class="sidebar-title">Đọc nhiều nhất</h2>
                <article class="sidebar-card">
                    <img src="<?php echo detailEscape($relatedArticles[0]['image']); ?>" alt="ảnh lỗi">
                    <div class="sidebar-body">
                        <div class="article-category">Chuyển nhượng</div>
                        <h3>Những thương vụ đáng chú ý trong mùa hè năm nay</h3>
                        <div class="meta"><i class="fa-regular fa-eye"></i> 12.458 lượt xem</div>
                    </div>
                </article>
                <h2 class="sidebar-title">Theo dõi chúng tôi</h2>
                <div class="social-card">
                    <a href="#"><i class="fa-brands fa-facebook-f"></i><span>Facebook</span><small>12K người theo dõi</small></a>
                    <a href="#"><i class="fa-brands fa-youtube"></i><span>YouTube</span><small>8K người đăng ký</small></a>
                    <a href="#"><i class="fa-brands fa-x-twitter"></i><span>X / Twitter</span><small>5K người theo dõi</small></a>
                </div>
                <div class="newsletter-card">
                    <i class="fa-regular fa-envelope"></i>
                    <h3>Nhận tin mới mỗi ngày</h3>
                    <p>Đăng ký để không bỏ lỡ tin thể thao mới nhất.</p>
                    <form action="#" method="post">
                        <input type="email" placeholder="Email của bạn" aria-label="Email của bạn" required>
                        <button type="submit"><i class="fa-solid fa-arrow-right"></i></button>
                    </form>
                </div>
            </aside>
        </div>
        <?php if ($categoryError !== '') : ?>
            <p class="database-error">Không thể tải danh mục: <?php echo detailEscape($categoryError); ?></p>
        <?php endif; ?>
    </main>
    <footer>
        <div class="container footer-inner">
            <strong><i class="fa-solid fa-futbol"></i> <?php echo detailEscape($siteName); ?></strong>
            <span>Giới thiệu &nbsp; | &nbsp; Điều khoản sử dụng &nbsp; | &nbsp; Chính sách bảo mật &nbsp; | &nbsp; Liên hệ</span>
            <span>© 2025 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
</body>
</html>
