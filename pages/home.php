<?php

declare(strict_types=1);

$siteName = 'Football News';

$navigationItems = array(
    'Trang chủ',
    'Việt Nam',
    'Ngoại hạng Anh',
    'Champions League',
    'La Liga',
    'Serie A',
    'Bundesliga',
    'Chuyển nhượng',
    'Kết quả & Lịch thi đấu',
);

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

$categories = array(
    array('name' => 'Tin bóng đá Việt Nam', 'count' => '128 bài viết', 'icon' => '🇻🇳'),
    array('name' => 'Ngoại hạng Anh', 'count' => '256 bài viết', 'icon' => '⚽'),
    array('name' => 'Champions League', 'count' => '98 bài viết', 'icon' => '🏆'),
    array('name' => 'La Liga', 'count' => '87 bài viết', 'icon' => '⚽'),
    array('name' => 'Serie A', 'count' => '64 bài viết', 'icon' => '🔵'),
    array('name' => 'Bundesliga', 'count' => '52 bài viết', 'icon' => '🔴'),
    array('name' => 'Chuyển nhượng', 'count' => '134 bài viết', 'icon' => '↔'),
    array('name' => 'Kết quả & Lịch thi đấu', 'count' => '76 bài viết', 'icon' => '▣'),
);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function articleMeta(array $article): string
{
    return '<span>◷ ' . e($article['date']) . '</span><span>◌ ' . e((string) $article['comments']) . '</span>';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Football News - Tin bóng đá mới nhất trong ngày">
    <title><?php echo e($siteName); ?></title>
    <style>
        :root {
            --green: #087c62;
            --dark: #142938;
            --muted: #73808a;
            --line: #e7edf0;
            --soft: #f5f8f9;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: #fff; color: var(--dark); font: 13px/1.5 Arial, sans-serif; }
        a { color: inherit; text-decoration: none; }
        .container { width: min(1180px, calc(100% - 40px)); margin: auto; }
        .topbar { border-bottom: 1px solid var(--line); }
        .topbar-inner { align-items: center; display: flex; height: 64px; justify-content: space-between; }
        .brand { align-items: center; display: flex; font-size: 21px; font-weight: 700; gap: 10px; }
        .brand-mark { align-items: center; border: 2px solid var(--green); border-radius: 50%; color: var(--green); display: flex; font-size: 20px; height: 30px; justify-content: center; width: 30px; }
        .top-actions { align-items: center; display: flex; gap: 20px; }
        .search { background: var(--soft); border: 1px solid #edf1f2; border-radius: 20px; color: #9aa5ab; padding: 8px 14px; width: 220px; }
        .login { font-size: 12px; font-weight: 700; }
        .signup { background: var(--green); border-radius: 18px; color: white; padding: 8px 17px; }
        .nav { border-bottom: 1px solid var(--line); }
        .nav-list { display: flex; gap: 25px; list-style: none; margin: 0; overflow-x: auto; padding: 0; white-space: nowrap; }
        .nav a { display: block; padding: 13px 0; }
        .nav a:hover, .nav a.active { border-bottom: 2px solid var(--green); color: var(--green); font-weight: 700; }
        main { padding: 24px 0 35px; }
        .top-grid { display: grid; gap: 18px; grid-template-columns: minmax(0, 2fr) minmax(320px, 1fr); }
        .hero { background: #18252c; border-radius: 5px; color: white; min-height: 350px; overflow: hidden; position: relative; }
        .hero::before { background: linear-gradient(90deg, rgba(0,0,0,.85), rgba(0,0,0,.05)), linear-gradient(0deg, rgba(0,0,0,.5), transparent 45%); content: ''; inset: 0; position: absolute; z-index: 1; }
        .hero-image { height: 100%; object-fit: cover; position: absolute; width: 100%; }
        .hero-content { bottom: 25px; left: 27px; max-width: 580px; position: absolute; z-index: 2; }
        .tag { background: var(--green); border-radius: 10px; display: inline-block; font-size: 10px; font-weight: 700; padding: 4px 10px; }
        h1 { font-size: clamp(23px, 3vw, 31px); line-height: 1.15; margin: 10px 0; }
        .hero-description { color: #e4e8e9; margin: 0 0 16px; }
        .meta { color: var(--muted); display: flex; font-size: 11px; gap: 16px; }
        .hero .meta { color: #eef3f2; }
        .slider-dots { bottom: 16px; display: flex; gap: 6px; position: absolute; right: 24px; z-index: 2; }
        .slider-dots i { background: #cad3d5; border-radius: 50%; height: 7px; width: 7px; }
        .slider-dots i.active { background: var(--green); }
        .section-title { align-items: center; display: flex; justify-content: space-between; margin: 23px 0 12px; }
        h2 { font-size: 18px; margin: 0; }
        h2::before { border-left: 3px solid var(--green); content: ''; margin-right: 9px; }
        .view-all { color: var(--green); font-size: 11px; }
        .latest-list { border: 1px solid var(--line); border-radius: 4px; padding: 8px; }
        .latest-item { border-bottom: 1px solid var(--line); display: flex; gap: 10px; padding: 8px 0; }
        .latest-item:last-child { border: 0; }
        .latest-item img { border-radius: 3px; height: 61px; object-fit: cover; width: 88px; }
        .article-category { color: var(--green); font-size: 10px; }
        .latest-item h3 { font-size: 12px; line-height: 1.3; margin: 2px 0 6px; }
        .latest-item .meta { font-size: 10px; }
        .featured-grid { display: grid; gap: 14px; grid-template-columns: repeat(4, 1fr); }
        .featured-card { border: 1px solid var(--line); border-radius: 4px; overflow: hidden; }
        .featured-card img { display: block; height: 104px; object-fit: cover; width: 100%; }
        .card-body { padding: 9px 10px 11px; }
        .featured-card h3 { font-size: 12px; line-height: 1.35; margin: 4px 0 9px; min-height: 33px; }
        .category-grid { display: grid; gap: 12px; grid-template-columns: repeat(8, 1fr); }
        .category { background: linear-gradient(135deg, #123d48, #172b39); border-radius: 4px; color: white; min-height: 90px; padding: 12px 5px; text-align: center; }
        .category-icon { display: block; font-size: 25px; margin-bottom: 5px; }
        .category strong { display: block; font-size: 10px; line-height: 1.25; }
        .category small { color: #c7d8d9; display: block; font-size: 9px; margin-top: 4px; }
        .cta { align-items: center; background: linear-gradient(90deg, #087c62, #69a83a); border-radius: 5px; color: white; display: flex; justify-content: space-between; margin-top: 14px; overflow: hidden; padding: 17px 42px; }
        .cta h2 { margin-bottom: 3px; }
        .cta h2::before { display: none; }
        .cta p { margin: 0; }
        .cta a { background: white; border-radius: 18px; color: var(--green); font-size: 11px; font-weight: 700; padding: 9px 15px; }
        footer { border-top: 1px solid var(--line); color: var(--muted); font-size: 10px; padding: 18px 0; }
        .footer-inner { align-items: center; display: flex; justify-content: space-between; }
        @media (max-width: 850px) {
            .top-grid { grid-template-columns: 1fr; }
            .featured-grid { grid-template-columns: repeat(2, 1fr); }
            .category-grid { grid-template-columns: repeat(4, 1fr); }
        }
        @media (max-width: 600px) {
            .container { width: min(100% - 24px, 1180px); }
            .top-actions .search, .login { display: none; }
            .hero { min-height: 310px; }
            .featured-grid { gap: 9px; }
            .category-grid { gap: 7px; }
            .cta { align-items: flex-start; flex-direction: column; gap: 14px; padding: 18px; }
            .footer-inner { align-items: flex-start; flex-direction: column; gap: 8px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="topbar">
            <div class="container topbar-inner">
                <a class="brand" href="/sport_news/"><span class="brand-mark">⚽</span><?php echo e($siteName); ?></a>
                <div class="top-actions">
                    <div class="search">Tìm kiếm tin tức... &nbsp;⌕</div>
                    <a class="login" href="#">Đăng nhập</a>
                    <a class="signup" href="#">Đăng ký</a>
                </div>
            </div>
        </div>
        <nav class="nav">
            <div class="container">
                <ul class="nav-list">
                    <?php foreach ($navigationItems as $index => $navigationItem) : ?>
                        <li><a class="<?php echo $index === 0 ? 'active' : ''; ?>" href="#"><?php echo e($navigationItem); ?></a></li>
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
                    <h1><?php echo e($heroArticle['title']); ?></h1>
                    <p class="hero-description"><?php echo e($heroArticle['description']); ?></p>
                    <div class="meta"><?php echo articleMeta($heroArticle); ?></div>
                </div>
                <div class="slider-dots"><i class="active"></i><i></i><i></i><i></i></div>
            </article>
            <aside>
                <div class="section-title"><h2>Tin mới nhất</h2><a class="view-all" href="#">Xem tất cả →</a></div>
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
            <div class="section-title"><h2>Tin nổi bật</h2><a class="view-all" href="#">Xem tất cả →</a></div>
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
            <div class="section-title"><h2>Danh mục tin tức</h2><a class="view-all" href="#">Xem tất cả →</a></div>
            <div class="category-grid">
                <?php foreach ($categories as $category) : ?>
                    <a class="category" href="#">
                        <span class="category-icon"><?php echo e($category['icon']); ?></span>
                        <strong><?php echo e($category['name']); ?></strong>
                        <small><?php echo e($category['count']); ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="cta">
            <div>
                <h2>Cập nhật tin tức bóng đá<br>nhanh nhất, chính xác nhất</h2>
                <p>Đồng hành cùng bạn trên mọi hành trình của trái bóng tròn</p>
            </div>
            <a href="#">Đăng ký ngay →</a>
        </section>
    </main>

    <footer>
        <div class="container footer-inner">
            <strong>⚽ <?php echo e($siteName); ?></strong>
            <span>Giới thiệu &nbsp; | &nbsp; Điều khoản sử dụng &nbsp; | &nbsp; Chính sách bảo mật &nbsp; | &nbsp; Liên hệ</span>
            <span>© 2025 Football News. Tất cả quyền được bảo lưu.</span>
        </div>
    </footer>
</body>
</html>
