<?php

declare(strict_types=1);
?>
<?php if ($section === 'articles'): ?>
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
        <?php if ($articleError !== ''): ?>
            <p class="admin-alert error">
                Không thể tải bài viết: <?php echo adminEscape($articleError); ?>
            </p>
        <?php endif; ?>
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Danh mục</th>
                        <th>Ngày đăng</th>
                        <th>Lượt xem</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($articles as $article): ?>
                        <tr>
                            <td class="muted">#<?php echo (int) $article['article_id']; ?></td>
                            <td>
                                <img src="<?php echo adminEscape((string) ($article['images'] ?? '')); ?>" alt="Hình ảnh" width="100" height="100">
                            </td>
                            <td><strong><?php echo adminEscape((string) $article['title']); ?></strong></td>
                            <td><?php echo adminEscape((string) ($article['category_name'] ?? 'Chưa phân loại')); ?></td>
                            <td class="muted"><?php echo adminEscape(adminArticleDate($article['published_at'] ?? null)); ?></td>
                            <td><?php echo number_format((int) $article['views']); ?></td>
                            <td>
                                <span class="status <?php echo $article['status'] === 'published' ? 'on' : 'off'; ?>">
                                    <?php echo $article['status'] === 'published'
                                        ? 'Đã đăng'
                                        : ($article['status'] === 'hidden' ? 'Đang ẩn' : 'Bản nháp'); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a class="action edit" href="#" title="Sửa"><i class="fa-solid fa-pen"></i></a>
                                    <a class="action" href="#" title="Đăng/ẩn"><i class="fa-solid fa-eye"></i></a>
                                    <a class="action delete" href="#" title="Xóa"><i class="fa-solid fa-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
