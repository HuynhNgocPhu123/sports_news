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
$categoryActionResult = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['category_action'] ?? '') !== '') {
    require_once __DIR__ . '/../../Controllers/CategoryController.php';
    $categoryAction = (string) $_POST['category_action'];
    if ($categoryAction === 'create') {
        $categoryActionResult = CategoryController::create($_POST, $_FILES['images'] ?? null);
    } elseif ($categoryAction === 'delete') {
        $categoryActionResult = CategoryController::delete($_POST);
    } else {
        $categoryActionResult = CategoryController::update($_POST, $_FILES['images'] ?? null);
    }
}

try {
    require_once __DIR__ . '/../../Config/Database.php';

    $mysqli = Database::connect();

    $result = $mysqli->query(
        'SELECT category_id, category_name, slug, images, description, created_at
         FROM categories
         ORDER BY category_id ASC'
    );

    $categories = $result->fetch_all(MYSQLI_ASSOC);

    $result->free();
    $mysqli->close();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $categoryError = $exception->getMessage();
}

try {
    $mysqli = Database::connect();
    $userResult = $mysqli->query(
        'SELECT users.user_id, users.username, users.email, users.full_name,
                users.status, users.created_at, roles.role_name
         FROM users
         INNER JOIN roles ON roles.role_id = users.role_id
         ORDER BY users.user_id ASC'
    );
    $users = $userResult->fetch_all(MYSQLI_ASSOC);
    $userResult->free();
    $mysqli->close();
} catch (mysqli_sql_exception | RuntimeException $exception) {
    $userError = $exception->getMessage();
}

function adminEscape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

$mockArticles = [
    [
        'title'    => 'Man Utd xác nhận kế hoạch chiêu mộ tiền đạo hàng đầu châu Âu',
        'category' => 'Bóng đá',
        'status'   => 'Đã đăng',
        'views'    => '12.4K',
    ],
    [
        'title'    => 'Đội tuyển Việt Nam hội quân hướng tới Asian Cup 2027',
        'category' => 'Bóng đá',
        'status'   => 'Đã đăng',
        'views'    => '8.2K',
    ],
    [
        'title'    => 'Những tài năng trẻ đáng xem của làng bóng rổ',
        'category' => 'Bóng rổ',
        'status'   => 'Đang ẩn',
        'views'    => '4.8K',
    ],
];

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
                                    <th>Nội dung</th>
                                    <th>Danh mục</th>
                                    <th>Lượt xem</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($mockArticles as $article): ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?php echo adminEscape($article['title']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo adminEscape($article['category']); ?>
                                        </td>

                                        <td>
                                            <?php echo $article['views']; ?>
                                        </td>

                                        <td>
                                            <span
                                                class="status <?php echo $article['status'] === 'Đã đăng' ? 'on' : 'off'; ?>"
                                            >
                                                <?php echo $article['status']; ?>
                                            </span>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php elseif ($section === 'articles'): ?>

                <div class="section-heading">

                    <div>
                        <h2>Danh sách bài viết</h2>
                        <p>Thêm, sửa, đăng/ẩn và xóa bài viết</p>
                    </div>

                    <button class="button" type="button">
                        <i class="fa-solid fa-plus"></i>
                        Thêm bài viết
                    </button>

                </div>

                <div class="panel">

                    <div class="table-wrap">

                        <table class="data-table">

                            <thead>
                                <tr>
                                    <th>Tiêu đề</th>
                                    <th>Danh mục</th>
                                    <th>Lượt xem</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($mockArticles as $article): ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?php echo adminEscape($article['title']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo adminEscape($article['category']); ?>
                                        </td>

                                        <td>
                                            <?php echo $article['views']; ?>
                                        </td>

                                        <td>
                                            <span
                                                class="status <?php echo $article['status'] === 'Đã đăng' ? 'on' : 'off'; ?>"
                                            >
                                                <?php echo $article['status']; ?>
                                            </span>
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
                                                    class="action"
                                                    href="#"
                                                    title="Đăng/ẩn"
                                                >
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                <a
                                                    class="action delete"
                                                    href="#"
                                                    title="Xóa"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            <?php elseif ($section === 'categories'): ?>

                <div class="section-heading">

                    <div>
                        <h2>Danh sách chuyên mục</h2>
                        <p>Dữ liệu đang được tải trực tiếp từ database</p>
                    </div>

                    <button class="button" type="button" onclick="openCreateCategoryModal();">
                        <i class="fa-solid fa-plus"></i>
                        Thêm chuyên mục
                    </button>

                </div>

                <?php if ($categoryError !== ''): ?>

                    <p class="muted">
                        Không thể tải danh mục:
                        <?php echo adminEscape($categoryError); ?>
                    </p>

                <?php endif; ?>

                <div class="panel">

                    <div class="table-wrap">

                        <table class="data-table">

                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Chuyên mục</th>
                                    <th>Hình ảnh</th>
                                    <th>Slug</th>
                                    <th>Mô tả</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>

                                <?php foreach ($categories as $category): ?>

                                    <tr>
                                        <td>
                                            <?php
                                            echo adminEscape(
                                                (string) $category['category_id']
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <strong>
                                                <?php
                                                echo adminEscape(
                                                    (string) $category['category_name']
                                                );
                                                ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <img src="<?php echo adminEscape((string) $category['images']); ?>" alt="<?php echo adminEscape((string) $category['category_name']); ?>" width="100" height="100">
                                        </td>

                                        <td class="muted">
                                            <?php
                                            echo adminEscape(
                                                (string) $category['slug']
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <span class="category-description">
                                                <?php
                                                echo adminEscape(
                                                    (string) $category['description']
                                                );
                                                ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="actions">

                                                <a
                                                    class="action edit"
                                                    href="#"
                                                    title="Sửa"
                                                    data-category-id="<?php echo adminEscape((string) $category['category_id']); ?>"
                                                    data-category-name="<?php echo adminEscape((string) $category['category_name']); ?>"
                                                    data-category-slug="<?php echo adminEscape((string) $category['slug']); ?>"
                                                    data-category-images="<?php echo adminEscape((string) $category['images']); ?>"
                                                    data-category-description="<?php echo adminEscape((string) $category['description']); ?>"
                                                    onclick="openCategoryModal(event, this);"
                                                >
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>

                                                <a
                                                    class="action delete"
                                                    href="#"
                                                    title="Xóa"
                                                    data-category-id="<?php echo adminEscape((string) $category['category_id']); ?>"
                                                    data-category-name="<?php echo adminEscape((string) $category['category_name']); ?>"
                                                    onclick="openDeleteCategoryModal(event, this);"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </a>

                                            </div>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

                <?php if (is_array($categoryActionResult) && !$categoryActionResult['success']) : ?>
                    <div class="admin-alert error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo adminEscape($categoryActionResult['message']); ?>
                    </div>
                <?php endif; ?>

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
<?php if (is_array($categoryActionResult) && $categoryActionResult['success']) : ?>
    <div class="update-success-modal" role="status" aria-live="polite">
        <div class="update-success-card">
            <div class="update-success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h2>
                <?php
                echo $categoryAction === 'create'
                    ? 'Thêm thành công'
                    : ($categoryAction === 'delete' ? 'Xóa thành công' : 'Cập nhật thành công');
                ?>
            </h2>
            <p><?php echo adminEscape($categoryActionResult['message']); ?> Trang sẽ tải lại ngay.</p>
            <div class="update-progress"><span></span></div>
        </div>
    </div>
<?php endif; ?>
<div class="modal-backdrop" id="category-create-modal" aria-hidden="true">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="category-create-modal-title">
        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeCreateCategoryModal();">&times;</button>
        <h2 id="category-create-modal-title">Thêm chuyên mục</h2>
        <p>Nhập thông tin chuyên mục mới và lưu trực tiếp vào database.</p>
        <form method="post" action="?page=admin&amp;section=categories" enctype="multipart/form-data">
            <input type="hidden" name="category_action" value="create">
            <label for="new-category-name">Tên chuyên mục</label>
            <input type="text" name="category_name" id="new-category-name" maxlength="100" required>
            <label for="new-category-slug">Slug</label>
            <input type="text" name="slug" id="new-category-slug" maxlength="100" required>
            <label for="new-category-images">Chọn hình ảnh</label>
            <input type="file" name="images" id="new-category-images">
            <span class="file-note">Ảnh JPG, PNG, GIF hoặc WEBP, tối đa 5MB. Hệ thống kiểm tra theo nội dung ảnh.</span>
            <label for="new-category-description">Mô tả</label>
            <textarea name="description" id="new-category-description" maxlength="255" rows="3"></textarea>
            <div class="modal-actions">
                <button class="button light" type="button" onclick="closeCreateCategoryModal();">Hủy</button>
                <button class="button" type="submit"><i class="fa-solid fa-plus"></i>Thêm chuyên mục</button>
            </div>
        </form>
    </section>
</div>
<div class="modal-backdrop" id="category-delete-modal" aria-hidden="true">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="category-delete-modal-title">
        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeDeleteCategoryModal();">&times;</button>
        <h2 id="category-delete-modal-title">Xóa chuyên mục?</h2>
        <p>
            Bạn có chắc chắn muốn xóa chuyên mục
            <strong id="delete-category-name"></strong> không?
            Dữ liệu sẽ bị xóa khỏi database và không thể khôi phục.
        </p>
        <form method="post" action="?page=admin&amp;section=categories">
            <input type="hidden" name="category_action" value="delete">
            <input type="hidden" name="category_id" id="delete-category-id">
            <div class="modal-actions">
                <button class="button light" type="button" onclick="closeDeleteCategoryModal();">Hủy</button>
                <button class="button delete-confirm" type="submit">
                    <i class="fa-solid fa-trash"></i>
                    Xóa chuyên mục
                </button>
            </div>
        </form>
    </section>
</div>
<div class="modal-backdrop" id="category-modal" aria-hidden="true">
    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="category-modal-title">
        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeCategoryModal();">&times;</button>
        <h2 id="category-modal-title">Chỉnh sửa chuyên mục</h2>
        <p>Cập nhật thông tin chuyên mục và lưu trực tiếp vào database.</p>
        <form method="post" action="?page=admin&amp;section=categories" enctype="multipart/form-data">
            <input type="hidden" name="category_action" value="update">
            <input type="hidden" name="category_id" id="category-id">
            <label for="category-name">Tên chuyên mục</label>
            <input type="text" name="category_name" id="category-name" maxlength="100" required>
            <label for="category-slug">Slug</label>
            <input type="text" name="slug" id="category-slug" maxlength="100" required>
            <input type="hidden" name="current_images" id="category-current-images">
            <label for="category-images">Chọn hình ảnh</label>
            <input type="file" name="images" id="category-images">
            <span class="file-note" id="category-image-note">Nếu không chọn ảnh mới, ảnh hiện tại sẽ được giữ lại. Hệ thống kiểm tra theo nội dung ảnh.</span>
            <label for="category-description">Mô tả</label>
            <textarea name="description" id="category-description" maxlength="255" rows="3"></textarea>
            <div class="modal-actions">
                <button class="button light" type="button" onclick="closeCategoryModal();">Hủy</button>
                <button class="button" type="submit"><i class="fa-solid fa-floppy-disk"></i>Lưu thay đổi</button>
            </div>
        </form>
    </section>
</div>
<script>
    function openCategoryModal(event, trigger) {
        event.preventDefault();
        document.getElementById('category-id').value = trigger.dataset.categoryId;
        document.getElementById('category-name').value = trigger.dataset.categoryName;
        document.getElementById('category-slug').value = trigger.dataset.categorySlug;
        document.getElementById('category-current-images').value = trigger.dataset.categoryImages;
        document.getElementById('category-image-note').textContent = trigger.dataset.categoryImages
            ? 'Ảnh hiện tại: ' + trigger.dataset.categoryImages
            : 'Chưa có ảnh. Bạn có thể chọn JPG, PNG, GIF hoặc WEBP (tối đa 5MB).';
        document.getElementById('category-description').value = trigger.dataset.categoryDescription;
        document.getElementById('category-modal').classList.add('open');
        document.getElementById('category-modal').setAttribute('aria-hidden', 'false');
    }

    function closeCategoryModal() {
        document.getElementById('category-modal').classList.remove('open');
        document.getElementById('category-modal').setAttribute('aria-hidden', 'true');
    }

    function openCreateCategoryModal() {
        document.getElementById('category-create-modal').classList.add('open');
        document.getElementById('category-create-modal').setAttribute('aria-hidden', 'false');
    }

    function closeCreateCategoryModal() {
        document.getElementById('category-create-modal').classList.remove('open');
        document.getElementById('category-create-modal').setAttribute('aria-hidden', 'true');
    }

    function openDeleteCategoryModal(event, trigger) {
        event.preventDefault();
        document.getElementById('delete-category-id').value = trigger.dataset.categoryId;
        document.getElementById('delete-category-name').textContent = trigger.dataset.categoryName;
        document.getElementById('category-delete-modal').classList.add('open');
        document.getElementById('category-delete-modal').setAttribute('aria-hidden', 'false');
    }

    function closeDeleteCategoryModal() {
        document.getElementById('category-delete-modal').classList.remove('open');
        document.getElementById('category-delete-modal').setAttribute('aria-hidden', 'true');
    }

    document.getElementById('category-modal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeCategoryModal();
        }
    });

    document.getElementById('category-create-modal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeCreateCategoryModal();
        }
    });

    document.getElementById('category-delete-modal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeDeleteCategoryModal();
        }
    });
</script>
<?php if (is_array($categoryActionResult) && $categoryActionResult['success']) : ?>
<script>
    window.setTimeout(function () {
        window.location.replace(window.location.pathname + window.location.search);
    }, 1600);
</script>
<?php endif; ?>
</body>
</html>