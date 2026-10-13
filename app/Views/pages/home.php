<?php

declare(strict_types=1);

$siteName = 'Football News';

$heroArticle = array(
    'category' => 'Ngoại hạng Anh',
    'title' => 'Man Utd xác nhận kế hoạch chiêu mộ tiền đạo hàng đầu châu Âu',
    'description' => 'Manchester United đang lên kế hoạch chiêu mộ một tiền đạo đẳng cấp nhằm cải thiện hàng công trong kỳ chuyển nhượng mùa hè 2025.',
    'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=1200&q=85',
    'date' => '12/08/2025',
    'comments' => 24,
);

$latestArticles = array(
    array(
        'category' => 'Chuyển nhượng',
        'title' => 'Real Madrid chính thức chiêu mộ ngôi sao trẻ từ Bundesliga',
        'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=400&q=80',
        'date' => '11/08/2025',
        'comments' => 15,
    ),
    array(
        'category' => 'Việt Nam',
        'title' => 'Đội tuyển Việt Nam hội quân, hướng tới vòng loại Asian Cup 2027',
        'image' => 'https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=400&q=80',
        'date' => '10/08/2025',
        'comments' => 8,
    ),
    array(
        'category' => 'Champions League',
        'title' => 'UEFA công bố thể thức mới của Champions League mùa tới',
        'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=400&q=80',
        'date' => '09/08/2025',
        'comments' => 12,
    ),
    array(
        'category' => 'La Liga',
        'title' => 'Barcelona tiếp tục khẳng định sức mạnh với chiến thắng 4-0',
        'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=400&q=80',
        'date' => '09/08/2025',
        'comments' => 10,
    ),
);

$featuredArticles = array(
    array(
        'category' => 'Chuyển nhượng',
        'title' => 'Ronaldo xác nhận tương lai, người hâm mộ có thể yên tâm',
        'image' => 'https://images.unsplash.com/photo-1579952363873-27f3bade9f55?auto=format&fit=crop&w=600&q=80',
        'date' => '11/08/2025',
        'comments' => 32,
    ),
    array(
        'category' => 'Ngoại hạng Anh',
        'title' => 'Salah lập kỷ lục mới trong màu áo Liverpool',
        'image' => 'https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=600&q=80',
        'date' => '10/08/2025',
        'comments' => 18,
    ),
    array(
        'category' => 'La Liga',
        'title' => 'Real Madrid thắng đậm ngày ra quân La Liga 2025/26',
        'image' => 'https://images.unsplash.com/photo-1522778119026-d647f0596c20?auto=format&fit=crop&w=600&q=80',
        'date' => '10/08/2025',
        'comments' => 15,
    ),
    array(
        'category' => 'Việt Nam',
        'title' => 'Quang Hải: Tôi luôn muốn cống hiến cho đội tuyển',
        'image' => 'https://images.unsplash.com/photo-1553778263-73a83bab9b0c?auto=format&fit=crop&w=600&q=80',
        'date' => '09/08/2025',
        'comments' => 27,
    ),
);

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

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function articleMeta(array $article): string
{
    return '<span><i class="fa-regular fa-calendar"></i> ' . e($article['date']) . '</span><span><i class="fa-regular fa-comment"></i> ' . e((string) $article['comments']) . '</span>';
}

$applicationPath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$currentUser = $_SESSION['auth'] ?? null;
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
                <a class="brand" href="/sport_news/"><span class="brand-mark"><i class="fa-solid fa-futbol"></i></span><?php echo e($siteName); ?></a>
                <div class="top-actions">
                    <div class="search">Tìm kiếm tin tức... <i class="fa-solid fa-magnifying-glass"></i></div>
                    <?php if (is_array($currentUser)) : ?>
                        <div class="account-menu">
                            <span class="account-name"><i class="fa-regular fa-user"></i> <?php echo e((string) $currentUser['full_name']); ?></span>
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
                    <li><a class="active" href="./">Trang chủ</a></li>
                    <?php foreach ($categories as $category) : ?>
                        <li>
                            <a href="?category=<?php echo rawurlencode((string) $category['slug']); ?>">
                                <?php echo e((string) $category['category_name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </nav>
    </header>

    <main class="container">
        <section class="top-grid">
            <article class="hero">
                <img class="hero-image" src="<?php echo e($heroArticle['image']); ?>" alt="<?php echo e($heroArticle['title']); ?>">
                <div class="hero-content">
                    <span class="tag"><?php echo e($heroArticle['category']); ?></span>
                    <h1><a href="?page=detail"><?php echo e($heroArticle['title']); ?></a></h1>
                    <p class="hero-description"><?php echo e($heroArticle['description']); ?></p>
                    <div class="meta"><?php echo articleMeta($heroArticle); ?></div>
                </div>
                <div class="slider-dots"><i class="active"></i><i></i><i></i><i></i></div>
            </article>
            <aside>
                <div class="section-title"><h2>Tin mới nhất</h2><a class="view-all" href="#">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
                <div class="latest-list">
                    <?php foreach ($latestArticles as $article) : ?>
                        <article class="latest-item">
                            <img src="<?php echo e($article['image']); ?>" alt="">
                            <div>
                                <div class="article-category"><?php echo e($article['category']); ?></div>
                                <h3><?php echo e($article['title']); ?></h3>
                                <div class="meta"><?php echo articleMeta($article); ?></div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </aside>
        </section>

        <section>
            <div class="section-title"><h2>Tin nổi bật</h2><a class="view-all" href="#">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="featured-grid">
                <?php foreach ($featuredArticles as $article) : ?>
                    <article class="featured-card">
                        <img src="<?php echo e($article['image']); ?>" alt="">
                        <div class="card-body">
                            <div class="article-category"><?php echo e($article['category']); ?></div>
                            <h3><?php echo e($article['title']); ?></h3>
                            <div class="meta"><?php echo articleMeta($article); ?></div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <div class="section-title"><h2>Danh mục tin tức</h2><a class="view-all" href="#">Xem tất cả <i class="fa-solid fa-arrow-right"></i></a></div>
            <div class="category-grid">
                <?php foreach ($categories as $category) : ?>
                    <a class="category" href="#">
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

        <section class="cta">
            <div>
                <h2>Cập nhật tin tức bóng đá<br>nhanh nhất, chính xác nhất</h2>
                <p>Đồng hành cùng bạn trên mọi hành trình của trái bóng tròn</p>
            </div>
            <a href="?page=register">Đăng ký ngay <i class="fa-solid fa-arrow-right"></i></a>
        </section>
    </main>

    <footer>
        <div class="container footer-inner">
            <strong><i class="fa-solid fa-futbol"></i> <?php echo e($siteName); ?></strong>
            <span>Giới thiệu &nbsp; | &nbsp; Điều khoản sử dụng &nbsp; | &nbsp; Chính sách bảo mật &nbsp; | &nbsp; Liên hệ</span>
            <span>© 2025 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
</body>
</html>
