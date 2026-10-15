<?php

declare(strict_types=1);

$siteName = 'Football News';

$categories = array();
$categoryError = '';
$articles = array();
$articleError = '';
$selectedCategorySlug = trim((string) ($_GET['category'] ?? ''));
$selectedCategoryName = '';
$searchKeyword = trim((string) ($_GET['search'] ?? ''));
$currentUser = $_SESSION['auth'] ?? null;
$homeFlash = (string) ($_SESSION['home_flash'] ?? '');
unset($_SESSION['home_flash']);

try {
    require_once __DIR__ . '/../../Config/Database.php';
    require_once __DIR__ . '/../../Controllers/ArticleController.php';
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

try {
    $articles = $searchKeyword !== ''
        ? ArticleController::searchPublished($searchKeyword)
        : ArticleController::published($selectedCategorySlug);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $articleError = $exception->getMessage();
}

foreach ($categories as $category) {
    if ((string) $category['slug'] === $selectedCategorySlug) {
        $selectedCategoryName = (string) $category['category_name'];
        break;
    }
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function articleMeta(array $article): string
{
    $date = $article['published_at'] ?? $article['created_at'] ?? null;
    $formattedDate = $date ? date('d/m/Y H:i', strtotime((string) $date)) : 'Chưa đăng';

    return '<span><i class="fa-regular fa-calendar"></i> ' . e($formattedDate) . '</span><span><i class="fa-regular fa-eye"></i> ' . e((string) ($article['views'] ?? 0)) . '</span>';
}

$heroArticle = $articles[0] ?? null;
$latestArticles = array_slice($articles, 1, 4);
$featuredPool = $articles;
usort($featuredPool, static function (array $left, array $right): int {
    $viewComparison = (int) ($right['views'] ?? 0) <=> (int) ($left['views'] ?? 0);

    if ($viewComparison !== 0) {
        return $viewComparison;
    }

    return (int) ($right['article_id'] ?? 0) <=> (int) ($left['article_id'] ?? 0);
});
$featuredArticles = array_slice($featuredPool, 0, 4);
$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Football News - Tin bóng đá mới nhất trong ngày">
    <link rel="stylesheet" href="<?php echo e($applicationPath . '/public/assets/css/home.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo e($siteName); ?></title>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="container topbar-inner">
                <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo e($siteName); ?></a>
                <div class="top-actions">
                    <form class="search" action="./" method="get">
                        <input
                            type="search"
                            name="search"
                            value="<?php echo e($searchKeyword); ?>"
                            placeholder="Tìm kiếm tin tức..."
                            aria-label="Tìm kiếm tin tức"
                        >
                        <button type="submit" aria-label="Tìm kiếm">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </button>
                    </form>
                    <?php if (is_array($currentUser)) : ?>
                        <div class="account-menu">
                            <div class="account-dropdown">
                                <span class="account-name"><i class="fa-regular fa-user"></i> <?php echo e((string) $currentUser['full_name']); ?><i class="fa-solid fa-chevron-down account-chevron"></i></span>
                                <?php if ((int) ($currentUser['role_id'] ?? 0) === 2) : ?>
                                    <div class="account-panel">
                                        <strong><?php echo e((string) $currentUser['full_name']); ?></strong>
                                        <small>Tài khoản người dùng</small>
                                        <a class="account-panel-action" href="?page=change-password">
                                            <i class="fa-solid fa-key"></i> Đổi mật khẩu
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
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
                    <li><a class="<?php echo $selectedCategorySlug === '' ? 'active' : ''; ?>" href="./">Trang chủ</a></li>
                    <?php foreach ($categories as $category) : ?>
                        <li>
                            <a
                                class="<?php echo (string) $category['slug'] === $selectedCategorySlug ? 'active' : ''; ?>"
                                href="?category=<?php echo rawurlencode((string) $category['slug']); ?>"
                            >
                                <?php echo e((string) $category['category_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                    <li><a href="?page=contact">Liên hệ</a></li>
                </ul>
            </div>
        </nav>
    </header>
    <script>
        (function () {
            var searchInput = document.querySelector('.search input[name="search"]');
            if (!searchInput) {
                return;
            }

            searchInput.addEventListener('input', function () {
                if (this.value.trim() !== '' || !new URLSearchParams(window.location.search).has('search')) {
                    return;
                }

                var url = new URL(window.location.href);
                url.searchParams.delete('search');
                window.location.replace(url.toString());
            });
        }());
    </script>

    <main class="container">
        <?php if ($homeFlash !== '') : ?>
            <div class="home-flash"><i class="fa-solid fa-circle-check"></i> <?php echo e($homeFlash); ?></div>
        <?php endif; ?>
        <?php if ($searchKeyword !== '') : ?>
            <div class="category-page-heading">
                <h1>Kết quả tìm kiếm</h1>
                <p>Từ khóa: <strong><?php echo e($searchKeyword); ?></strong></p>
            </div>
        <?php elseif ($selectedCategoryName !== '') : ?>
            <div class="category-page-heading">
                <h1><?php echo e($selectedCategoryName); ?></h1>
                <p>Các bài viết mới nhất thuộc danh mục này</p>
            </div>
        <?php endif; ?>
        <section class="top-grid scroll-reveal">
            <article class="hero">
                <?php if ($heroArticle !== null) : ?>
                    <a class="article-image-link" href="?page=detail&amp;slug=<?php echo rawurlencode((string) $heroArticle['slug']); ?>">
                        <img class="hero-image" src="<?php echo e((string) ($heroArticle['images'] ?? '')); ?>" alt="ảnh lỗi">
                    </a>
                    <div class="hero-content">
                        <span class="tag"><?php echo e((string) ($heroArticle['category_name'] ?? 'Chưa phân loại')); ?></span>
                        <h1><a href="?page=detail&amp;slug=<?php echo rawurlencode((string) $heroArticle['slug']); ?>"><?php echo e((string) $heroArticle['title']); ?></a></h1>
                        <p class="hero-description"><?php echo e((string) ($heroArticle['summary'] ?? '')); ?></p>
                        <div class="meta"><?php echo articleMeta($heroArticle); ?></div>
                    </div>
                <?php else : ?>
                    <div class="hero-content">
                        <span class="tag">Tin tức</span>
                        <h1>Chưa có bài viết đã đăng</h1>
                        <p class="hero-description">Các bài viết mới sẽ được hiển thị tại đây.</p>
                    </div>
                <?php endif; ?>
                <div class="slider-dots"><i class="active"></i><i></i><i></i><i></i></div>
            </article>
            <aside>
                <div class="section-title"><h2>Tin mới nhất</h2><a class="view-all" href="?page=articles">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
                <div class="latest-list">
                    <?php foreach ($latestArticles as $article) : ?>
                        <article class="latest-item">
                            <a class="article-image-link" href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>">
                                <img src="<?php echo e((string) ($article['images'] ?? '')); ?>" alt="ảnh lỗi">
                            </a>
                            <div>
                                <div class="article-category"><?php echo e((string) ($article['category_name'] ?? 'Chưa phân loại')); ?></div>
                                <h3><a href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>"><?php echo e((string) $article['title']); ?></a></h3>
                                <div class="meta"><?php echo articleMeta($article); ?></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
        </section>
        <?php if ($selectedCategoryName !== '' && $heroArticle === null) : ?>
            <p class="database-error">Danh mục này chưa có bài viết đã đăng.</p>
        <?php endif; ?>

        <section class="scroll-reveal">
            <div class="section-title"><h2>Tin nổi bật</h2><a class="view-all" href="?page=articles">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="featured-grid">
                <?php foreach ($featuredArticles as $article) : ?>
                    <article class="featured-card">
                        <a class="article-image-link" href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>">
                            <img src="<?php echo e((string) ($article['images'] ?? '')); ?>" alt="ảnh lỗi">
                        </a>
                        <div class="card-body">
                            <div class="article-category"><?php echo e((string) ($article['category_name'] ?? 'Chưa phân loại')); ?></div>
                            <h3><a href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>"><?php echo e((string) $article['title']); ?></a></h3>
                            <div class="meta"><?php echo articleMeta($article); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
            <?php if ($articleError !== '') : ?>
                <p class="database-error">Không thể tải bài viết: <?php echo e($articleError); ?></p>
            <?php endif; ?>
        </section>

        <section class="scroll-reveal">
            <div class="section-title"><h2>Danh mục tin tức</h2><a class="view-all" href="?page=articles">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="category-grid">
                <?php foreach ($categories as $category) : ?>
                    <a class="category" href="?category=<?php echo rawurlencode((string) $category['slug']); ?>">
                        <img class="category-image" src="<?php echo e((string) $category['images']); ?>" alt="ảnh lỗi">
                        <span class="category-content">
                            <strong><?php echo e($category['category_name']); ?></strong>
                            <small><?php echo e((string) $category['description']); ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
            <?php if ($categoryError !== '') : ?>
                <p class="database-error">Không thể tải danh mục: <?php echo e($categoryError); ?></p>
            <?php endif; ?>
        </section>

        <section class="cta scroll-reveal">
            <div>
                <h2>Cập nhật tin tức bóng đá<br>nhanh nhất, chính xác nhất</h2>
                <p>Đồng hành cùng bạn trên mọi hành trình của trái bóng tròn</p>
            </div>
            <a href="?page=register">Đăng ký ngay <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>
    <script>
        (function () {
            var revealItems = document.querySelectorAll('.scroll-reveal');
            if (!('IntersectionObserver' in window)) {
                revealItems.forEach(function (item) {
                    item.classList.add('is-visible');
                });
                return;
            }

            var observer = new IntersectionObserver(function (entries, currentObserver) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) {
                        return;
                    }

                    entry.target.classList.add('is-visible');
                    currentObserver.unobserve(entry.target);
                });
            }, {
                rootMargin: '0px 0px -10% 0px',
                threshold: 0.08
            });

            revealItems.forEach(function (item) {
                observer.observe(item);
            });
        }());
    </script>

    <footer>
        <div class="container footer-inner">
            <strong><i class="fa-solid fa-futbol"></i> <?php echo e($siteName); ?></strong>
            <span>Giới thiệu &nbsp; | &nbsp; Điều khoản sử dụng &nbsp; | &nbsp; Chính sách bảo mật &nbsp; | &nbsp; <a href="?page=contact">Liên hệ</a></span>
            <span>© 2026 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
</body>
</html>
