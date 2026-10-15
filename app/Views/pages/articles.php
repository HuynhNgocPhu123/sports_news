<?php

declare(strict_types=1);

$siteName = 'Football News';
$categories = array();
$articles = array();
$categoryError = '';
$articleError = '';
$selectedCategorySlug = trim((string) ($_GET['category'] ?? ''));
$searchKeyword = trim((string) ($_GET['search'] ?? ''));
$selectedCategoryName = '';
$currentUser = $_SESSION['auth'] ?? null;

require_once __DIR__ . '/../../Config/Database.php';
require_once __DIR__ . '/../../Controllers/ArticleController.php';

try {
    $mysqli = Database::connect();
    $categoryResult = $mysqli->query(
        'SELECT category_id, category_name, slug
         FROM categories
         ORDER BY category_id ASC'
    );
    $categories = $categoryResult->fetch_all(MYSQLI_ASSOC);
    $categoryResult->free();
    $mysqli->close();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $categoryError = $exception->getMessage();
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

function articlesEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function articlesMeta(array $article): string
{
    $date = $article['published_at'] ?? $article['created_at'] ?? null;
    $formattedDate = $date ? date('d/m/Y H:i', strtotime((string) $date)) : 'Chưa đăng';

    return '<span><i class="fa-regular fa-calendar"></i> ' . articlesEscape($formattedDate) . '</span>'
        . '<span><i class="fa-regular fa-eye"></i> ' . articlesEscape((string) ($article['views'] ?? 0)) . '</span>';
}

$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo articlesEscape($applicationPath . '/public/assets/css/home.css'); ?>">
    <link rel="stylesheet" href="<?php echo articlesEscape($applicationPath . '/public/assets/css/articles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Tất cả bài viết - <?php echo articlesEscape($siteName); ?></title>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="container topbar-inner">
                <a class="brand" href="./"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo articlesEscape($siteName); ?></a>
                <div class="top-actions">
                    <form class="search" action="?page=articles" method="get">
                        <input type="hidden" name="page" value="articles">
                        <input type="search" name="search" value="<?php echo articlesEscape($searchKeyword); ?>" placeholder="Tìm kiếm tin tức..." aria-label="Tìm kiếm tin tức">
                        <button type="submit" aria-label="Tìm kiếm"><i class="fa-solid fa-magnifying-glass"></i></button>
                    </form>
                    <?php if (is_array($currentUser)) : ?>
                        <div class="account-menu">
                            <span class="account-name"><i class="fa-regular fa-user"></i> <?php echo articlesEscape((string) $currentUser['full_name']); ?></span>
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
                        <li><a class="<?php echo (string) $category['slug'] === $selectedCategorySlug ? 'active' : ''; ?>" href="?page=articles&amp;category=<?php echo rawurlencode((string) $category['slug']); ?>"><?php echo articlesEscape((string) $category['category_name']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container articles-page">
        <div class="category-page-heading articles-intro">
            <h1>
                <?php echo $searchKeyword !== '' ? 'Kết quả tìm kiếm' : ($selectedCategoryName !== '' ? articlesEscape($selectedCategoryName) : 'Tất cả bài viết'); ?>
            </h1>
            <p>
                <?php if ($searchKeyword !== '') : ?>
                    Từ khóa: <strong><?php echo articlesEscape($searchKeyword); ?></strong>
                <?php elseif ($selectedCategoryName !== '') : ?>
                    Các bài viết mới nhất thuộc danh mục này
                <?php else : ?>
                    Toàn bộ bài viết đã được đăng trên Football News
                <?php endif; ?>
            </p>
        </div>
        <form class="articles-search articles-intro" action="./" method="get">
            <input type="hidden" name="page" value="articles">
            <?php if ($selectedCategorySlug !== '') : ?>
                <input type="hidden" name="category" value="<?php echo articlesEscape($selectedCategorySlug); ?>">
            <?php endif; ?>
            <label for="articles-search-input">Tìm kiếm bài viết</label>
            <div class="articles-search-controls">
                <input
                    id="articles-search-input"
                    type="search"
                    name="search"
                    value="<?php echo articlesEscape($searchKeyword); ?>"
                    placeholder="Nhập tiêu đề, nội dung hoặc danh mục..."
                    aria-label="Tìm kiếm bài viết"
                >
                <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Tìm kiếm</button>
                <?php if ($searchKeyword !== '') : ?>
                    <a class="clear-search" href="?page=articles<?php echo $selectedCategorySlug !== '' ? '&amp;category=' . rawurlencode($selectedCategorySlug) : ''; ?>">Xóa tìm kiếm</a>
                <?php endif; ?>
            </div>
        </form>

        <?php if ($articleError !== '') : ?>
            <p class="database-error">Không thể tải bài viết: <?php echo articlesEscape($articleError); ?></p>
        <?php elseif ($articles === array()) : ?>
            <div class="empty-articles"><i class="fa-regular fa-newspaper"></i><p>Chưa có bài viết phù hợp.</p><a href="./">Về trang chủ</a></div>
        <?php else : ?>
            <div class="all-articles-grid">
                <?php foreach ($articles as $article) : ?>
                    <article class="all-article-card article-reveal">
                        <a class="article-image-link" href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>">
                            <img src="<?php echo articlesEscape((string) ($article['images'] ?? '')); ?>" alt="ảnh bài viết">
                        </a>
                        <div class="all-article-body">
                            <div class="article-category"><?php echo articlesEscape((string) ($article['category_name'] ?? 'Chưa phân loại')); ?></div>
                            <h2><a href="?page=detail&amp;slug=<?php echo rawurlencode((string) $article['slug']); ?>"><?php echo articlesEscape((string) $article['title']); ?></a></h2>
                            <p><?php echo articlesEscape((string) ($article['summary'] ?? '')); ?></p>
                            <div class="meta"><?php echo articlesMeta($article); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        (function () {
            var revealItems = document.querySelectorAll('.articles-intro, .article-reveal, .empty-articles');

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
                rootMargin: '0px 0px -8% 0px',
                threshold: 0.08
            });

            revealItems.forEach(function (item) {
                observer.observe(item);
            });
        }());
    </script>

    <footer>
        <div class="container footer-inner">
            <strong><i class="fa-solid fa-futbol"></i> <?php echo articlesEscape($siteName); ?></strong>
            <span>© 2026 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
</body>
</html>
