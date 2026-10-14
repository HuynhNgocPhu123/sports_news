<?php

declare(strict_types=1);
?>
<?php if ($section === 'categories'): ?>
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
            Không thể tải danh mục: <?php echo adminEscape($categoryError); ?>
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
                            <td><?php echo (int) $category['category_id']; ?></td>
                            <td><strong><?php echo adminEscape((string) $category['category_name']); ?></strong></td>
                            <td>
                                <img src="<?php echo adminEscape((string) $category['images']); ?>" alt="<?php echo adminEscape((string) $category['category_name']); ?>" width="100" height="100">
                            </td>
                            <td class="muted"><?php echo adminEscape((string) $category['slug']); ?></td>
                            <td><span class="category-description"><?php echo adminEscape((string) $category['description']); ?></span></td>
                            <td>
                                <div class="actions">
                                    <a class="action edit" href="#" title="Sửa"
                                       data-category-id="<?php echo adminEscape((string) $category['category_id']); ?>"
                                       data-category-name="<?php echo adminEscape((string) $category['category_name']); ?>"
                                       data-category-slug="<?php echo adminEscape((string) $category['slug']); ?>"
                                       data-category-images="<?php echo adminEscape((string) $category['images']); ?>"
                                       data-category-description="<?php echo adminEscape((string) $category['description']); ?>"
                                       onclick="openCategoryModal(event, this);">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <a class="action delete" href="#" title="Xóa"
                                       data-category-id="<?php echo adminEscape((string) $category['category_id']); ?>"
                                       data-category-name="<?php echo adminEscape((string) $category['category_name']); ?>"
                                       onclick="openDeleteCategoryModal(event, this);">
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

    <?php if (is_array($categoryActionResult) && !$categoryActionResult['success']): ?>
        <div class="admin-alert error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <?php echo adminEscape($categoryActionResult['message']); ?>
        </div>
    <?php endif; ?>

    <?php include __DIR__ . '/category-modals.php'; ?>
<?php endif; ?>
