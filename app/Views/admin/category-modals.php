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
            <span class="file-note">Ảnh JPG, PNG, GIF hoặc WEBP, tối đa 5MB.</span>
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
        <p>Bạn có chắc chắn muốn xóa chuyên mục <strong id="delete-category-name"></strong> không? Dữ liệu không thể khôi phục.</p>
        <form method="post" action="?page=admin&amp;section=categories">
            <input type="hidden" name="category_action" value="delete">
            <input type="hidden" name="category_id" id="delete-category-id">
            <div class="modal-actions">
                <button class="button light" type="button" onclick="closeDeleteCategoryModal();">Hủy</button>
                <button class="button delete-confirm" type="submit"><i class="fa-solid fa-trash"></i>Xóa chuyên mục</button>
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
            <span class="file-note" id="category-image-note">Nếu không chọn ảnh mới, ảnh hiện tại sẽ được giữ lại.</span>
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
        : 'Chưa có ảnh. Bạn có thể chọn ảnh mới.';
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
['category-modal', 'category-create-modal', 'category-delete-modal'].forEach(function (id) {
    document.getElementById(id).addEventListener('click', function (event) {
        if (event.target === this) {
            this.classList.remove('open');
            this.setAttribute('aria-hidden', 'true');
        }
    });
});
</script>
