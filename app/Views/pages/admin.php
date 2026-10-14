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
$userActionResult = null;

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

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST' && ($_POST['user_action'] ?? '') !== '') {
    $userAction = (string) $_POST['user_action'];
    if ($userAction === 'create') {
        $userActionResult = UserController::create($_POST);
    } elseif ($userAction === 'lock') {
        $userActionResult = UserController::lock($_POST);
    } elseif ($userAction === 'update') {
        $userActionResult = UserController::update($_POST);
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

                    <button class="button" type="button" onclick="openCreateUserModal();">
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
                                                    data-user-id="<?php echo (int) $user['user_id']; ?>"
                                                    data-role-id="<?php echo (int) $user['role_id']; ?>"
                                                    data-username="<?php echo adminEscape((string) $user['username']); ?>"
                                                    data-email="<?php echo adminEscape((string) $user['email']); ?>"
                                                    data-full-name="<?php echo adminEscape((string) ($user['full_name'] ?? '')); ?>"
                                                    data-status="<?php echo (int) $user['status']; ?>"
                                                    onclick="openUserModal(event, this);"
                                                >
                                                    <i class="fa-solid fa-pen"></i>
                                                </a>

                                                <a
                                                    class="action hide"
                                                    href="#"
                                                    title="Khóa tài khoản"
                                                    data-user-id="<?php echo (int) $user['user_id']; ?>"
                                                    data-user-name="<?php echo adminEscape((string) ($user['full_name'] ?: $user['username'])); ?>"
                                                    onclick="openLockUserModal(event, this);"
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

                <?php if ($userActionResult !== null && !$userActionResult['success']): ?>
                    <div class="admin-alert error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo adminEscape((string) $userActionResult['message']); ?>
                    </div>
                <?php endif; ?>

                <div class="modal-backdrop" id="user-create-modal" aria-hidden="true">
                    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="user-create-modal-title">
                        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeCreateUserModal();">&times;</button>
                        <h2 id="user-create-modal-title">Thêm người dùng</h2>
                        <p>Tạo tài khoản người dùng mới và lưu trực tiếp vào database.</p>
                        <form method="post" action="?page=admin&amp;section=users">
                            <input type="hidden" name="user_action" value="create">
                            <label for="new-user-username">Username</label>
                            <input type="text" name="username" id="new-user-username" maxlength="100" required>
                            <label for="new-user-email">Email</label>
                            <input type="email" name="email" id="new-user-email" maxlength="255" required>
                            <label for="new-user-full-name">Họ và tên</label>
                            <input type="text" name="full_name" id="new-user-full-name" maxlength="255" required>
                            <label for="new-user-password">Mật khẩu</label>
                            <input type="password" name="password" id="new-user-password" minlength="6" required>
                            <label for="new-user-status">Trạng thái</label>
                            <select name="status" id="new-user-status" required>
                                <option value="1">Hoạt động</option>
                                <option value="2">Đã khóa</option>
                            </select>
                            <div class="modal-actions">
                                <button class="button light" type="button" onclick="closeCreateUserModal();">Hủy</button>
                                <button class="button" type="submit"><i class="fa-solid fa-plus"></i>Thêm người dùng</button>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="modal-backdrop" id="user-lock-modal" aria-hidden="true">
                    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="user-lock-modal-title">
                        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeLockUserModal();">&times;</button>
                        <h2 id="user-lock-modal-title">Khóa tài khoản?</h2>
                        <p>
                            Bạn có chắc chắn muốn khóa tài khoản
                            <strong id="lock-user-name"></strong> không?
                        </p>
                        <form method="post" action="?page=admin&amp;section=users">
                            <input type="hidden" name="user_action" value="lock">
                            <input type="hidden" name="user_id" id="lock-user-id">
                            <div class="modal-actions">
                                <button class="button light" type="button" onclick="closeLockUserModal();">Hủy</button>
                                <button class="button delete-confirm" type="submit">
                                    <i class="fa-solid fa-lock"></i>
                                    Khóa tài khoản
                                </button>
                            </div>
                        </form>
                    </section>
                </div>

                <div class="modal-backdrop" id="user-modal" aria-hidden="true">
                    <section class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
                        <button class="modal-close" type="button" aria-label="Đóng" onclick="closeUserModal();">&times;</button>
                        <h2 id="user-modal-title">Chỉnh sửa người dùng</h2>
                        <p>Cập nhật thông tin tài khoản và lưu trực tiếp vào database.</p>
                        <form method="post" action="?page=admin&amp;section=users">
                            <input type="hidden" name="user_action" value="update">
                            <input type="hidden" name="user_id" id="user-id">
                            <label for="user-username">Username</label>
                            <input type="text" name="username" id="user-username" maxlength="100" required>
                            <label for="user-email">Email</label>
                            <input type="email" name="email" id="user-email" maxlength="255" required>
                            <label for="user-full-name">Họ và tên</label>
                            <input type="text" name="full_name" id="user-full-name" maxlength="255" required>
                            <label for="user-role-id">Vai trò</label>
                            <select name="role_id" id="user-role-id" required>
                                <option value="1">Quản trị viên</option>
                                <option value="2">Người dùng</option>
                            </select>
                            <label for="user-status">Trạng thái</label>
                            <select name="status" id="user-status" required>
                                <option value="1">Hoạt động</option>
                                <option value="2">Đã khóa</option>
                            </select>
                            <label for="user-password">Mật khẩu mới</label>
                            <input type="password" name="password" id="user-password" minlength="6">
                            <span class="file-note">Để trống nếu không muốn thay đổi mật khẩu.</span>
                            <div class="modal-actions">
                                <button class="button light" type="button" onclick="closeUserModal();">Hủy</button>
                                <button class="button" type="submit"><i class="fa-solid fa-floppy-disk"></i>Lưu thay đổi</button>
                            </div>
                        </form>
                    </section>
                </div>
                <script>
                    function openUserModal(event, trigger) {
                        event.preventDefault();
                        document.getElementById('user-id').value = trigger.dataset.userId;
                        document.getElementById('user-role-id').value = trigger.dataset.roleId;
                        document.getElementById('user-username').value = trigger.dataset.username;
                        document.getElementById('user-email').value = trigger.dataset.email;
                        document.getElementById('user-full-name').value = trigger.dataset.fullName;
                        document.getElementById('user-status').value = trigger.dataset.status;
                        document.getElementById('user-password').value = '';
                        document.getElementById('user-modal').classList.add('open');
                        document.getElementById('user-modal').setAttribute('aria-hidden', 'false');
                    }
                    function closeUserModal() {
                        document.getElementById('user-modal').classList.remove('open');
                        document.getElementById('user-modal').setAttribute('aria-hidden', 'true');
                    }
                    function openCreateUserModal() {
                        document.getElementById('user-create-modal').classList.add('open');
                        document.getElementById('user-create-modal').setAttribute('aria-hidden', 'false');
                    }
                    function closeCreateUserModal() {
                        document.getElementById('user-create-modal').classList.remove('open');
                        document.getElementById('user-create-modal').setAttribute('aria-hidden', 'true');
                    }
                    function openLockUserModal(event, trigger) {
                        event.preventDefault();
                        document.getElementById('lock-user-id').value = trigger.dataset.userId;
                        document.getElementById('lock-user-name').textContent = trigger.dataset.userName;
                        document.getElementById('user-lock-modal').classList.add('open');
                        document.getElementById('user-lock-modal').setAttribute('aria-hidden', 'false');
                    }
                    function closeLockUserModal() {
                        document.getElementById('user-lock-modal').classList.remove('open');
                        document.getElementById('user-lock-modal').setAttribute('aria-hidden', 'true');
                    }
                    document.getElementById('user-modal').addEventListener('click', function (event) {
                        if (event.target === this) {
                            closeUserModal();
                        }
                    });
                    document.getElementById('user-create-modal').addEventListener('click', function (event) {
                        if (event.target === this) {
                            closeCreateUserModal();
                        }
                    });
                    document.getElementById('user-lock-modal').addEventListener('click', function (event) {
                        if (event.target === this) {
                            closeLockUserModal();
                        }
                    });
                </script>

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
    || (is_array($userActionResult) && $userActionResult['success'])
) : ?>
    <div class="update-success-modal" role="status" aria-live="polite">
        <div class="update-success-card">
            <div class="update-success-icon"><i class="fa-solid fa-circle-check"></i></div>
            <h2>
                <?php
                echo $userActionResult !== null
                    ? ($userAction === 'create'
                        ? 'Thêm thành công'
                        : ($userAction === 'lock' ? 'Khóa thành công' : 'Cập nhật thành công'))
                    : ($articleActionResult !== null
                    ? ($articleAction === 'create'
                        ? 'Thêm thành công'
                        : ($articleAction === 'delete' ? 'Xóa thành công' : 'Cập nhật thành công'))
                    : ($categoryAction === 'create'
                        ? 'Thêm thành công'
                        : ($categoryAction === 'delete' ? 'Xóa thành công' : 'Cập nhật thành công')));
                ?>
            </h2>
            <p><?php echo adminEscape(
                (string) (($userActionResult ?? $articleActionResult ?? $categoryActionResult)['message'] ?? '')
            ); ?> Trang sẽ tải lại ngay.</p>
            <div class="update-progress"><span></span></div>
        </div>
    </div>
<?php endif; ?>
<?php if (
    (is_array($categoryActionResult) && $categoryActionResult['success'])
    || (is_array($articleActionResult) && $articleActionResult['success'])
    || (is_array($userActionResult) && $userActionResult['success'])
) : ?>
<script>
    window.setTimeout(function () {
        window.location.replace(window.location.pathname + window.location.search);
    }, 1600);
</script>
<?php endif; ?>
</body>
</html>