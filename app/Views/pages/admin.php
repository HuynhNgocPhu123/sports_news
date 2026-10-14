<?php
declare(strict_types=1);

$currentUser = $_SESSION['auth'] ?? null;

if (
    !is_array($currentUser)
    || (int) ($currentUser['role_id'] ?? 0) !== 1
) {
    header('Location: ./');
    exit;
}

$categories = [];
$categoryError = '';
$users = [];
$userError = '';
$articles = [];
$articleError = '';
$categoryActionResult = null;
$articleActionResult = null;

require_once __DIR__ . '/../../Controllers/CategoryController.php';
require_once __DIR__ . '/../../Controllers/ArticleController.php';
require_once __DIR__ . '/../../Controllers/UserController.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['category_action'] ?? '') !== '') {
    $categoryAction = (string) $_POST['category_action'];
    if ($categoryAction === 'create') {
        $categoryActionResult = CategoryController::create($_POST, $_FILES['images'] ?? null);
    } elseif ($categoryAction === 'delete') {
        $categoryActionResult = CategoryController::delete($_POST);
    } else {
        $categoryActionResult = CategoryController::update($_POST, $_FILES['images'] ?? null);
    }

}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['article_action'] ?? '') !== '') {
    $articleAction = (string) $_POST['article_action'];
    if ($articleAction === 'toggle') {
        $articleActionResult = ArticleController::toggleVisibility($_POST);
    } elseif ($articleAction === 'delete') {
        $articleActionResult = ArticleController::delete($_POST);
    } elseif ($articleAction === 'create') {
        $_POST['author_id'] = (int) ($currentUser['user_id'] ?? 0);
        $articleActionResult = ArticleController::create($_POST, $_FILES['images'] ?? null);
    } else {
        $articleActionResult = ArticleController::update($_POST, $_FILES['images'] ?? null);
    }
}

try {
    $categories = CategoryController::all();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $categoryError = $exception->getMessage();
}

try {
    $users = UserController::all();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $userError = $exception->getMessage();
}

try {
    $articles = ArticleController::all();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $articleError = $exception->getMessage();
}

function adminEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function adminArticleDate(?string $date): string
{
    if ($date === null || $date === '') {
        return 'Chưa đăng';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? 'Chưa đăng' : date('d/m/Y H:i', $timestamp);
}

function adminArticleInputDate(?string $date): string
{
    if ($date === null || $date === '') {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp === false ? '' : date('Y-m-d\TH:i', $timestamp);
}

$section = $_GET['section'] ?? 'dashboard';

$sectionTitles = [
    'dashboard'  => 'Tổng quan',
    'articles'   => 'Quản trị bài viết',
    'categories' => 'Quản trị danh mục',
    'users'      => 'Quản lý người dùng',
    'comments'   => 'Quản lý bình luận',
];

$title = $sectionTitles[$section] ?? $sectionTitles['dashboard'];

$applicationPath = rtrim(
    str_replace(
        '\\',
        '/',
        dirname($_SERVER['SCRIPT_NAME'] ?? '/')
    ),
    '/'
);

$mockComments = [
    [
        'user'    => 'Nguyễn Văn An',
        'content' => 'Hy vọng thương vụ này sẽ sớm hoàn tất.',
        'article' => 'Man Utd xác nhận kế hoạch...',
        'status'  => 'Hiển thị',
    ],
    [
        'user'    => 'Thanh Hà',
        'content' => 'Bài viết cập nhật thông tin rất nhanh.',
        'article' => 'Đội tuyển Việt Nam hội quân...',
        'status'  => 'Đang ẩn',
    ],
];

$navigationItems = [
    'dashboard'  => 'Tổng quan',
    'articles'   => 'Bài viết',
    'categories' => 'Danh mục',
    'users'      => 'Người dùng',
    'comments'   => 'Bình luận',
];

$navigationIcons = [
    'dashboard'  => 'fa-chart-line',
    'articles'   => 'fa-newspaper',
    'categories' => 'fa-layer-group',
    'users'      => 'fa-users',
    'comments'   => 'fa-comments',
];

?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="stylesheet"
        href="<?php echo adminEscape(
            $applicationPath . '/public/assets/css/admin.css'
        ); ?>"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <title>
        <?php echo adminEscape($title); ?> - Football News
    </title>
</head>

<body>

<div class="admin-shell">

    <aside class="sidebar">

        <a class="admin-brand" href="?page=admin">
            <i class="fa-solid fa-futbol"></i>
            Football News
        </a>

        <div class="sidebar-label">
            Quản trị hệ thống
        </div>

        <nav class="admin-nav">

            <?php foreach ($navigationItems as $key => $label): ?>

                <a
                    class="<?php echo $section === $key ? 'active' : ''; ?>"
                    href="?page=admin&amp;section=<?php echo $key; ?>"
                >
                    <i
                        class="fa-solid <?php echo $navigationIcons[$key]; ?>"
                    ></i>

                    <?php echo $label; ?>
                </a>

            <?php endforeach; ?>

        </nav>

        <div class="sidebar-label">
            Hệ thống
        </div>

        <nav class="admin-nav">

            <a href="./">
                <i class="fa-solid fa-house"></i>
                Xem website
            </a>

            <a href="?page=logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i>
                Đăng xuất
            </a>

        </nav>

    </aside>

    <div class="admin-main">

        <header class="admin-header">

            <h1>
                <?php echo adminEscape($title); ?>
            </h1>

            <div class="admin-user">

                <div class="admin-avatar">
                    <?php
                    echo adminEscape(
                        strtoupper(
                            substr(
                                (string) ($currentUser['full_name'] ?? ''),
                                0,
                                1
                            )
                        )
                    );
                    ?>
                </div>

                <div>
                    <strong>
                        <?php
                        echo adminEscape(
                            (string) ($currentUser['full_name'] ?? '')
                        );
                        ?>
                    </strong>

                    <small>Quản trị viên</small>
                </div>

            </div>

        </header>

        <main class="admin-content">

            <?php if ($section === 'dashboard'): ?>

                <div class="stats-grid">

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-newspaper"></i>
                        </div>

                        <div>
                            <strong>128</strong>
                            <span>Tổng bài viết</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>

                        <div>
                            <strong><?php echo count($categories); ?></strong>
                            <span>Danh mục</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>

                        <div>
                            <strong><?php echo count($users); ?></strong>
                            <span>Người dùng</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fa-solid fa-comments"></i>
                        </div>

                        <div>
                            <strong>356</strong>
                            <span>Bình luận</span>
                        </div>
                    </div>

                </div>

                <div class="section-heading">

                    <div>
                        <h2>Hoạt động gần đây</h2>
                        <p>Tổng quan nội dung trên hệ thống</p>
                    </div>

                    <a
                        class="button light"
                        href="?page=admin&amp;section=articles"
                    >
                        Xem bài viết
                    </a>

                </div>

                <div class="panel">

                    <div class="table-wrap">

                        <table class="data-table">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nội dung</th>
                                    <th>Danh mục</th>
                                    <th>Ngày đăng</th>
                                    <th>Lượt xem</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($articles as $article): ?>

                                    <tr>
                                        <td class="muted">
                                            #<?php echo (int) $article['article_id']; ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?php echo adminEscape($article['title']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo adminEscape((string) ($article['category_name'] ?? 'Chưa phân loại')); ?>
                                        </td>

                                        <td class="muted">
                                            <?php echo adminEscape(adminArticleDate($article['published_at'] ?? null)); ?>
                                        </td>

                                        <td>
                                            <?php echo number_format((int) $article['views']); ?>
                                        </td>

                                        <td>
                                            <span
                                                class="status <?php echo $article['status'] === 'published' ? 'on' : 'off'; ?>"
                                            >
                                                <?php echo $article['status'] === 'published'
                                                    ? 'Đã đăng'
                                                    : ($article['status'] === 'hidden' ? 'Đang ẩn' : 'Bản nháp'); ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php elseif ($section === 'articles'): ?>
                <?php include __DIR__ . '/../admin/articles.php'; ?>

            <?php elseif ($section === 'categories'): ?>
                <?php include __DIR__ . '/../admin/categories.php'; ?>

            <?php elseif ($section === 'users'): ?>

                <div class="section-heading">

                    <div>
                        <h2>Danh sách người dùng</h2>
                        <p>Thêm, sửa và khóa tài khoản</p>
                    </div>

                    <button class="button">
                        <i class="fa-solid fa-plus"></i>
                        Thêm người dùng
                    </button>

                </div>

                <?php if ($userError !== ''): ?>
                    <p class="muted">
                        Không thể tải người dùng:
                        <?php echo adminEscape($userError); ?>
                    </p>
                <?php endif; ?>

                <div class="panel">

                    <div class="table-wrap">

                        <table class="data-table">

                            <thead>
                                <tr>
                                    <th>Tài khoản</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($users as $user): ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?php
                                                echo adminEscape(
                                                    (string) (
                                                        $user['full_name']
                                                        ?: $user['username']
                                                    )
                                                );
                                                ?>
                                            </strong>
                                        </td>

                                        <td class="muted">
                                            <?php echo adminEscape($user['email']); ?>
                                        </td>

                                        <td>
                                            <?php echo adminEscape((string) $user['role_name']); ?>
                                        </td>

                                        <td>
                                            <?php if ((int) $user['status'] === 1): ?>
                                                <span class="status on">Hoạt động</span>
                                            <?php elseif ((int) $user['status'] === 2): ?>
                                                <span class="status off">Đã khóa</span>
                                            <?php else: ?>
                                                <span class="status wait">Không xác định</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <div class="actions">

                                                <a
                                                    class="action edit"
                                                    href="#"
                                                    title="Sửa"
                                                >
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>

                                                <a
                                                    class="action hide"
                                                    href="#"
                                                    title="Khóa tài khoản"
                                                >
                                                    <i class="fa-solid fa-lock"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php else: ?>

                <div class="section-heading">

                    <div>
                        <h2>Danh sách bình luận</h2>
                        <p>Kiểm duyệt và ẩn bình luận</p>
                    </div>

                </div>

                <div class="panel">

                    <div class="table-wrap">

                        <table class="data-table">

                            <thead>
                                <tr>
                                    <th>Người dùng</th>
                                    <th>Nội dung</th>
                                    <th>Bài viết</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($mockComments as $comment): ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?php echo adminEscape($comment['user']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo adminEscape($comment['content']); ?>
                                        </td>

                                        <td class="muted">
                                            <?php echo adminEscape($comment['article']); ?>
                                        </td>

                                        <td>
                                            <span
                                                class="status <?php echo $comment['status'] === 'Hiển thị' ? 'on' : 'off'; ?>"
                                            >
                                                <?php echo $comment['status']; ?>
                                            </span>
                                        </td>

                                        <td>
                                            <a
                                                class="action hide"
                                                href="#"
                                                title="Ẩn bình luận"
                                            >
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </a>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php endif; ?>

        </main>

        <footer class="admin-footer">
            Football News Admin &copy; 2025 - Khu vực quản trị nội dung
        </footer>

    </div>

</div>
<?php if (
    (is_array($categoryActionResult) && $categoryActionResult['success'])
    || (is_array($articleActionResult) && $articleActionResult['success'])
) : ?>
    <div class="update-success-modal" role="status" aria-live="polite">
        <div class="update-success-card">
            <div class="update-success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h2>
                <?php
                echo $articleActionResult !== null
                    ? ($articleAction === 'create'
                        ? 'Thêm thành công'
                        : ($articleAction === 'delete' ? 'Xóa thành công' : 'Cập nhật thành công'))
                    : ($categoryAction === 'create'
                        ? 'Thêm thành công'
                        : ($categoryAction === 'delete' ? 'Xóa thành công' : 'Cập nhật thành công'));
                ?>
            </h2>
            <p><?php echo adminEscape(
                (string) (($articleActionResult ?? $categoryActionResult)['message'] ?? '')
            ); ?> Trang sẽ tải lại ngay.</p>
            <div class="update-progress"><span></span></div>
        </div>
    </div>
<?php endif; ?>
<?php if (
    (is_array($categoryActionResult) && $categoryActionResult['success'])
    || (is_array($articleActionResult) && $articleActionResult['success'])
) : ?>
<script>
    window.setTimeout(function () {
        window.location.replace(window.location.pathname + window.location.search);
    }, 1600);
</script>
<?php endif; ?>
</body>
</html>