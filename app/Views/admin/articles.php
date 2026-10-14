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
                                    <a
                                        class="action edit"
                                        href="#"
                                        title="Sửa"
                                        data-article-id="<?php echo (int) $article['article_id']; ?>"
                                        data-category-id="<?php echo (int) $article['category_id']; ?>"
                                        data-title="<?php echo adminEscape((string) $article['title']); ?>"
                                        data-slug="<?php echo adminEscape((string) $article['slug']); ?>"
                                        data-summary="<?php echo adminEscape((string) ($article['summary'] ?? '')); ?>"
                                        data-content="<?php echo adminEscape((string) ($article['content'] ?? '')); ?>"
                                        data-status="<?php echo adminEscape((string) $article['status']); ?>"
                                        data-published-at="<?php echo adminEscape(adminArticleInputDate($article['published_at'] ?? null)); ?>"
                                        data-images="<?php echo adminEscape((string) ($article['images'] ?? '')); ?>"
                                        onclick="openArticleModal(event, this);"
                                    ><i class="fa-solid fa-pen"></i></a>
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
    <?php if ($articleActionResult !== null && !$articleActionResult['success']): ?>
        <div class="admin-alert error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo adminEscape((string) $articleActionResult['message']); ?>
        </div>
    <?php endif; ?>
    <div class="modal-backdrop" id="article-modal" aria-hidden="true">
        <section class="admin-modal article-modal" role="dialog" aria-modal="true" aria-labelledby="article-modal-title">
            <button class="modal-close" type="button" aria-label="Đóng" onclick="closeArticleModal();">&times;</button>
            <h2 id="article-modal-title">Chỉnh sửa bài viết</h2>
            <p>Cập nhật nội dung bài viết và lưu trực tiếp vào database.</p>
            <form method="post" action="?page=admin&amp;section=articles" enctype="multipart/form-data">
                <input type="hidden" name="article_action" value="update">
                <input type="hidden" name="article_id" id="article-id">
                <input type="hidden" name="current_images" id="article-current-images">
                <label for="article-category-id">Danh mục</label>
                <select name="category_id" id="article-category-id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo (int) $category['category_id']; ?>">
                            <?php echo adminEscape((string) $category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <label for="article-title">Tiêu đề</label>
                <input type="text" name="title" id="article-title" maxlength="255" required>
                <label for="article-slug">Slug</label>
                <input type="text" name="slug" id="article-slug" maxlength="255" required>
                <label for="article-summary">Tóm tắt</label>
                <textarea name="summary" id="article-summary" rows="3"></textarea>
                <label for="article-content">Nội dung</label>
                <textarea name="content" id="article-content" rows="8" required></textarea>
                <label for="article-status">Trạng thái</label>
                <select name="status" id="article-status" required>
                    <option value="draft">Bản nháp</option>
                    <option value="published">Đã đăng</option>
                    <option value="hidden">Đang ẩn</option>
                </select>
                <label for="article-published-at">Ngày đăng</label>
                <input type="datetime-local" name="published_at" id="article-published-at">
                <label for="article-images">Hình ảnh</label>
                <input type="file" name="images" id="article-images">
                <span class="file-note" id="article-image-note">Nếu không chọn ảnh mới, ảnh hiện tại sẽ được giữ lại.</span>
                <div class="modal-actions">
                    <button class="button light" type="button" onclick="closeArticleModal();">Hủy</button>
                    <button class="button" type="submit"><i class="fa-solid fa-floppy-disk"></i>Lưu thay đổi</button>
                </div>
            </form>
        </section>
    </div>
    <script>
        function openArticleModal(event, trigger) {
            event.preventDefault();
            document.getElementById('article-id').value = trigger.dataset.articleId;
            document.getElementById('article-category-id').value = trigger.dataset.categoryId;
            document.getElementById('article-title').value = trigger.dataset.title;
            document.getElementById('article-slug').value = trigger.dataset.slug;
            document.getElementById('article-summary').value = trigger.dataset.summary;
            document.getElementById('article-content').value = trigger.dataset.content;
            document.getElementById('article-status').value = trigger.dataset.status;
            document.getElementById('article-published-at').value = trigger.dataset.publishedAt;
            document.getElementById('article-current-images').value = trigger.dataset.images;
            document.getElementById('article-image-note').textContent = trigger.dataset.images
                ? 'Ảnh hiện tại: ' + trigger.dataset.images
                : 'Chưa có ảnh. Bạn có thể chọn ảnh mới.';
            document.getElementById('article-modal').classList.add('open');
            document.getElementById('article-modal').setAttribute('aria-hidden', 'false');
        }
        function closeArticleModal() {
            document.getElementById('article-modal').classList.remove('open');
            document.getElementById('article-modal').setAttribute('aria-hidden', 'true');
        }
        document.getElementById('article-modal').addEventListener('click', function (event) {
            if (event.target === this) {
                closeArticleModal();
            }
        });
    </script>
<?php endif; ?>
