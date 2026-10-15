<?php

declare(strict_types=1);

$siteName = 'Football News';
$categories = array();
$categoryError = '';
$articleError = '';
$ratingResult = null;

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
$currentUser = $_SESSION['auth'] ?? null;
$ratingResult = $_SESSION['rating_result'] ?? null;
unset($_SESSION['rating_result']);

require_once __DIR__ . '/../../Controllers/ArticleController.php';
require_once __DIR__ . '/../../Controllers/RatingController.php';
$slug = trim((string) ($_GET['slug'] ?? ''));
$article = null;
$relatedArticles = array();
$ratings = array();
$ratingSummary = array('total_ratings' => 0, 'average_rating' => 0);
$hasRated = false;

try {
    if ($slug !== '') {
        $article = ArticleController::findPublishedBySlug($slug);
    }
    if ($article === null) {
        $articleError = 'Không tìm thấy bài viết hoặc bài viết chưa được đăng.';
    } else {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            if (ArticleController::incrementViews((int) $article['article_id'])) {
                $article['views'] = (int) ($article['views'] ?? 0) + 1;
            }
        }

        if (
            ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && in_array(($_POST['rating_action'] ?? ''), array('create', 'update', 'delete'), true)
        ) {
            if (
                !is_array($currentUser)
                || (int) ($currentUser['role_id'] ?? 0) !== 2
            ) {
                $ratingResult = array('success' => false, 'message' => 'Bạn cần đăng nhập bằng tài khoản người dùng để đánh giá.');
            } else {
                $ratingAction = (string) $_POST['rating_action'];
                $ratingResult = $ratingAction === 'create'
                    ? RatingController::create($_POST, (int) $currentUser['user_id'])
                    : ($ratingAction === 'update'
                        ? RatingController::update($_POST, (int) $currentUser['user_id'])
                        : RatingController::delete($_POST, (int) $currentUser['user_id']));
            }

            $_SESSION['rating_result'] = $ratingResult;
            header(
                'Location: ?page=detail&slug=' . rawurlencode((string) $article['slug'])
            );
            exit;
        }
        $relatedArticles = ArticleController::relatedPublished(
            (int) $article['category_id'],
            (int) $article['article_id']
        );
        $ratingSummary = RatingController::summary((int) $article['article_id']);
        $ratings = RatingController::allForArticle((int) $article['article_id']);
        if (is_array($currentUser) && (int) ($currentUser['role_id'] ?? 0) === 2) {
            $hasRated = RatingController::hasRated(
                (int) $article['article_id'],
                (int) $currentUser['user_id']
            );
        }
    }
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $articleError = $exception->getMessage();
}

$articleTitle = is_array($article) ? (string) $article['title'] : 'Không tìm thấy bài viết';
$articleCategory = is_array($article) ? (string) ($article['category_name'] ?? 'Chưa phân loại') : '';
$articleDate = is_array($article) && !empty($article['published_at'])
    ? date('d/m/Y H:i', strtotime((string) $article['published_at']))
    : 'Chưa đăng';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo detailEscape($applicationPath . '/public/assets/css/detail.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo detailEscape($articleTitle); ?> - <?php echo detailEscape($siteName); ?></title>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="container topbar-inner">
                <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo detailEscape($siteName); ?></a>
                <div class="top-actions">
                    <div class="search">Tìm kiếm tin tức... <i class="fa-solid fa-magnifying-glass"></i></div>
                    <?php if (is_array($currentUser)) : ?>
                        <div class="account-menu">
                            <span class="account-name"><i class="fa-regular fa-user"></i> <?php echo detailEscape((string) $currentUser['full_name']); ?></span>
                            <?php if ((int) ($currentUser['role_id'] ?? 0) === 1) : ?>
                                <a class="admin-link" href="?page=admin"><i class="fa-solid fa-gauge-high"></i> Quản trị</a>
                            <?php endif; ?>
                            <a class="logout-link" href="?page=logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Đăng xuất</a>
                        </div>
                    <?php else : ?>
                        <a class="login" href="?page=login">Đăng nhập</a>
                        <a class="signup" href="?page=register">Đăng ký</a>
                    <?php endif; ?>
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
                    <li><a href="?page=contact">Liên hệ</a></li>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <div class="breadcrumb"><a href="./">Trang chủ</a> <i class="fa-solid fa-chevron-right"></i> <?php echo detailEscape($articleCategory); ?></div>
        <?php if ($article === null) : ?>
            <div class="database-error">
                <?php echo detailEscape($articleError); ?>
            </div>
        <?php else : ?>
        <div class="detail-layout">
            <article class="article">
                <div class="article-category"><?php echo detailEscape($articleCategory); ?></div>
                <h1><?php echo detailEscape((string) $article['title']); ?></h1>
                <p class="article-lead"><?php echo detailEscape((string) ($article['summary'] ?? '')); ?></p>
                <div class="article-meta">
                    <span><i class="fa-regular fa-user"></i> <?php echo detailEscape((string) ($article['author_name'] ?? 'Ẩn danh')); ?></span>
                    <span><i class="fa-regular fa-calendar"></i> <?php echo detailEscape($articleDate); ?></span>
                    <span><i class="fa-regular fa-eye"></i> <?php echo number_format((int) $article['views']); ?> lượt xem</span>
                </div>
                <img class="article-cover" src="<?php echo detailEscape((string) ($article['images'] ?? '')); ?>" alt="ảnh lỗi">
                <div class="article-content">
                    <?php echo nl2br(detailEscape((string) $article['content'])); ?>
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
                        <p><?php echo (int) $ratingSummary['total_ratings']; ?> lượt đánh giá</p>
                    </div>
                    <div class="rating-stars" aria-label="Đánh giá 5 sao">
                        <?php for ($star = 1; $star <= 5; $star++) : ?>
                            <i class="fa-<?php echo $star <= round((float) $ratingSummary['average_rating']) ? 'solid' : 'regular'; ?> fa-star"></i>
                        <?php endfor; ?>
                        <strong><?php echo number_format((float) $ratingSummary['average_rating'], 1); ?>/5</strong>
                    </div>
                </section>
                <?php if (is_array($ratingResult)) : ?>
                    <p class="rating-message <?php echo $ratingResult['success'] ? 'success' : 'error'; ?>">
                        <?php echo detailEscape((string) $ratingResult['message']); ?>
                    </p>
                <?php endif; ?>
                <section class="comments">
                    <div class="comments-heading">
                        <h2>Bình luận <span>(<?php echo count($ratings); ?>)</span></h2>
                        <select aria-label="Sắp xếp bình luận">
                            <option>Mới nhất</option>
                            <option>Nhiều lượt thích</option>
                        </select>
                    </div>
                    <?php if (is_array($currentUser) && (int) ($currentUser['role_id'] ?? 0) === 2 && !$hasRated) : ?>
                    <form class="comment-form" action="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>" method="post">
                        <input type="hidden" name="rating_action" value="create">
                        <input type="hidden" name="article_id" value="<?php echo (int) $article['article_id']; ?>">
                        <div class="rating-input">
                            <span>Chọn số sao:</span>
                            <div class="rating-options">
                                <?php for ($star = 1; $star <= 5; $star++) : ?>
                                    <input id="rating-<?php echo $star; ?>" name="rating_value" type="radio" value="<?php echo $star; ?>" required>
                                    <label for="rating-<?php echo $star; ?>" data-rating="<?php echo $star; ?>" title="<?php echo $star; ?> sao"><i class="fa-solid fa-star"></i></label>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <textarea name="comment" rows="3" maxlength="2000" placeholder="Chia sẻ suy nghĩ của bạn về bài viết..." required></textarea>
                        <div class="comment-form-footer">
                            <span><i class="fa-regular fa-face-smile"></i> Hãy bình luận văn minh</span>
                            <button type="submit">Gửi bình luận <i class="fa-solid fa-paper-plane"></i></button>
                        </div>
                    </form>
                    <?php elseif ($hasRated) : ?>
                        <p class="login-to-rate">Bạn đã đánh giá bài viết này. Mỗi tài khoản chỉ được bình luận một lần.</p>
                    <?php else : ?>
                        <p class="login-to-rate">Đăng nhập bằng tài khoản người dùng để đánh giá và bình luận bài viết.</p>
                    <?php endif; ?>
                    <?php foreach ($ratings as $rating) : ?>
                        <div class="comment-item">
                            <div class="comment-avatar"><?php echo detailEscape(strtoupper(substr((string) ($rating['full_name'] ?: $rating['username']), 0, 2))); ?></div>
                            <div class="comment-body">
                                <strong><?php echo detailEscape((string) ($rating['full_name'] ?: $rating['username'])); ?></strong>
                                <p><?php echo detailEscape((string) $rating['comment']); ?></p>
                                <div class="rating-stars small">
                                    <?php for ($star = 1; $star <= 5; $star++) : ?>
                                        <i class="fa-<?php echo $star <= (int) $rating['rating_value'] ? 'solid' : 'regular'; ?> fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <?php if (
                                    is_array($currentUser)
                                    && (int) ($currentUser['role_id'] ?? 0) === 2
                                    && (int) ($rating['user_id'] ?? 0) === (int) $currentUser['user_id']
                                ) : ?>
                                    <div class="comment-actions">
                                        <button type="button" class="comment-action edit-rating" data-rating-id="<?php echo (int) $rating['rating_id']; ?>"><i class="fa-solid fa-pen"></i> Sửa</button>
                                        <form method="post" action="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này không?');">
                                            <input type="hidden" name="rating_action" value="delete">
                                            <input type="hidden" name="article_id" value="<?php echo (int) $article['article_id']; ?>">
                                            <input type="hidden" name="rating_id" value="<?php echo (int) $rating['rating_id']; ?>">
                                            <button type="submit" class="comment-action delete-rating"><i class="fa-solid fa-trash"></i> Xóa</button>
                                        </form>
                                    </div>
                                    <form class="edit-rating-form" id="edit-rating-<?php echo (int) $rating['rating_id']; ?>" method="post" action="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>">
                                        <input type="hidden" name="rating_action" value="update">
                                        <input type="hidden" name="article_id" value="<?php echo (int) $article['article_id']; ?>">
                                        <input type="hidden" name="rating_id" value="<?php echo (int) $rating['rating_id']; ?>">
                                        <div class="rating-input">
                                            <span>Chọn số sao:</span>
                                            <div class="rating-options">
                                                <?php for ($star = 1; $star <= 5; $star++) : ?>
                                                    <input id="edit-rating-<?php echo (int) $rating['rating_id']; ?>-<?php echo $star; ?>" name="rating_value" type="radio" value="<?php echo $star; ?>" <?php echo $star === (int) $rating['rating_value'] ? 'checked' : ''; ?> required>
                                                    <label for="edit-rating-<?php echo (int) $rating['rating_id']; ?>-<?php echo $star; ?>" data-rating="<?php echo $star; ?>" title="<?php echo $star; ?> sao"><i class="fa-solid fa-star"></i></label>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <textarea name="comment" rows="3" maxlength="2000" required><?php echo detailEscape((string) $rating['comment']); ?></textarea>
                                        <button type="submit">Lưu thay đổi</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
            </article>

            <aside class="sidebar">
                <h2 class="sidebar-title">Bài viết liên quan</h2>
                <?php foreach ($relatedArticles as $relatedArticle) : ?>
                    <a class="related-list related-item" href="?page=detail&amp;slug=<?php echo rawurlencode((string) $relatedArticle['slug']); ?>">
                        <img src="<?php echo detailEscape((string) ($relatedArticle['images'] ?? '')); ?>" alt="ảnh lỗi">
                        <h3><?php echo detailEscape($relatedArticle['title']); ?></h3>
                    </a>
                <?php endforeach; ?>
                <h2 class="sidebar-title">Đọc nhiều nhất</h2>
                <?php if (isset($relatedArticles[0])) : ?>
                    <article class="sidebar-card">
                        <img src="<?php echo detailEscape((string) ($relatedArticles[0]['images'] ?? '')); ?>" alt="ảnh lỗi">
                        <div class="sidebar-body">
                            <div class="article-category"><?php echo detailEscape((string) ($relatedArticles[0]['category_name'] ?? '')); ?></div>
                            <h3><?php echo detailEscape((string) ($relatedArticles[0]['title'] ?? '')); ?></h3>
                            <div class="meta"><i class="fa-regular fa-eye"></i> <?php echo number_format((int) ($relatedArticles[0]['views'] ?? 0)); ?> lượt xem</div>
                        </div>
                    </article>
                <?php endif; ?>
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
        <?php endif; ?>
        <?php if ($categoryError !== '') : ?>
            <p class="database-error">Không thể tải danh mục: <?php echo detailEscape($categoryError); ?></p>
        <?php endif; ?>
    </main>
    <footer>
        <div class="container footer-inner">
            <strong><i class="fa-solid fa-futbol"></i> <?php echo detailEscape($siteName); ?></strong>
            <span>Giới thiệu &nbsp; | &nbsp; Điều khoản sử dụng &nbsp; | &nbsp; Chính sách bảo mật &nbsp; | &nbsp; <a href="?page=contact">Liên hệ</a></span>
            <span>© 2026 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
    <script>
        (function () {
            var ratingInput = document.querySelector('.rating-input');
            if (!ratingInput) {
                return;
            }

            var options = ratingInput.querySelector('.rating-options');
            var labels = Array.prototype.slice.call(options.querySelectorAll('label'));
            var radios = Array.prototype.slice.call(options.querySelectorAll('input'));

            function paint(value, className) {
                labels.forEach(function (label) {
                    label.classList.toggle(className, Number(label.dataset.rating) <= value);
                });
            }

            options.addEventListener('mouseover', function (event) {
                var label = event.target.closest('label');
                if (label) {
                    labels.forEach(function (item) {
                        item.classList.remove('is-selected');
                    });
                    paint(Number(label.dataset.rating), 'is-hovered');
                }
            });

            options.addEventListener('mouseleave', function () {
                var selected = options.querySelector('input:checked');
                paint(selected ? Number(selected.value) : 0, 'is-selected');
                labels.forEach(function (label) {
                    label.classList.remove('is-hovered');
                });
            });

            radios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    paint(Number(radio.value), 'is-selected');
                });
            });

            document.querySelectorAll('.edit-rating').forEach(function (button) {
                button.addEventListener('click', function () {
                    var form = document.getElementById('edit-rating-' + button.dataset.ratingId);
                    if (form) {
                        form.classList.toggle('is-visible');
                    }
                });
            });
        }());
    </script>
</body>
</html>
