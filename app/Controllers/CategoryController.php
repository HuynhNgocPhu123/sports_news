<?php

declare(strict_types=1);

final class CategoryController
{
    public static function create(array $input, ?array $imageFile = null): array
    {
        $categoryName = trim((string) ($input['category_name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));
        $storedImagePath = null;

        $validationError = self::validateFields($categoryName, $slug, $description);
        if ($validationError !== null) {
            return array('success' => false, 'message' => $validationError);
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            self::ensureSlugIsAvailable($mysqli, $slug);
            $mysqli->begin_transaction();

            $emptyImagePath = '';
            $statement = $mysqli->prepare(
                'INSERT INTO categories (category_name, slug, images, description)
                 VALUES (?, ?, ?, ?)'
            );
            $statement->bind_param('ssss', $categoryName, $slug, $emptyImagePath, $description);
            $statement->execute();
            $categoryId = $mysqli->insert_id;
            $statement->close();

            if (is_array($imageFile) && (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $storedImagePath = self::storeCategoryImage($imageFile, $categoryId);
                $imageStatement = $mysqli->prepare(
                    'UPDATE categories SET images = ? WHERE category_id = ?'
                );
                $imageStatement->bind_param('si', $storedImagePath, $categoryId);
                $imageStatement->execute();
                $imageStatement->close();
            }

            $mysqli->commit();
            $mysqli->close();

            return array('success' => true, 'message' => 'Thêm chuyên mục thành công.');
        } catch (RuntimeException $exception) {
            if ($storedImagePath !== null) {
                self::removeStoredImage($storedImagePath);
            }
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback();
                $mysqli->close();
            }

            return array('success' => false, 'message' => $exception->getMessage());
        } catch (mysqli_sql_exception $exception) {
            if ($storedImagePath !== null) {
                self::removeStoredImage($storedImagePath);
            }
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->rollback();
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể thêm chuyên mục lúc này.');
        }
    }

    public static function update(array $input, ?array $imageFile = null): array
    {
        $categoryId = (int) ($input['category_id'] ?? 0);
        $categoryName = trim((string) ($input['category_name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $images = trim((string) ($input['current_images'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($categoryId < 1) {
            return array(
                'success' => false,
                'message' => 'Tên chuyên mục và slug không được để trống.',
            );
        }

        $validationError = self::validateFields($categoryName, $slug, $description);
        if ($validationError !== null) {
            return array(
                'success' => false,
                'message' => $validationError,
            );
        }

        try {
            if (is_array($imageFile) && (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $images = self::storeCategoryImage($imageFile, $categoryId);
            }

            if (mb_strlen($images) > 255) {
                return array(
                    'success' => false,
                    'message' => 'Đường dẫn hình ảnh vượt quá độ dài cho phép.',
                );
            }

            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $statement = $mysqli->prepare(
                'UPDATE categories
                 SET category_name = ?, slug = ?, images = ?, description = ?
                 WHERE category_id = ?'
            );
            $statement->bind_param('ssssi', $categoryName, $slug, $images, $description, $categoryId);
            $statement->execute();
            $updated = $statement->affected_rows >= 0;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $updated,
                'message' => $updated
                    ? 'Cập nhật chuyên mục thành công.'
                    : 'Không tìm thấy chuyên mục cần cập nhật.',
            );
        } catch (RuntimeException $exception) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array(
                'success' => false,
                'message' => $exception->getMessage(),
            );
        } catch (mysqli_sql_exception $exception) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array(
                'success' => false,
                'message' => 'Không thể cập nhật chuyên mục lúc này.',
            );
        }
    }

    public static function delete(array $input): array
    {
        $categoryId = (int) ($input['category_id'] ?? 0);

        if ($categoryId < 1) {
            return array(
                'success' => false,
                'message' => 'Chuyên mục cần xóa không hợp lệ.',
            );
        }

        $mysqli = null;
        $imagePath = '';

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $mysqli->begin_transaction();

            $selectStatement = $mysqli->prepare(
                'SELECT images FROM categories WHERE category_id = ? LIMIT 1'
            );
            $selectStatement->bind_param('i', $categoryId);
            $selectStatement->execute();
            $selectStatement->bind_result($imagePath);

            if (!$selectStatement->fetch()) {
                $selectStatement->close();
                $mysqli->rollback();
                $mysqli->close();

                return array(
                    'success' => false,
                    'message' => 'Không tìm thấy chuyên mục cần xóa.',
                );
            }

            $selectStatement->close();

            $deleteStatement = $mysqli->prepare(
                'DELETE FROM categories WHERE category_id = ?'
            );
            $deleteStatement->bind_param('i', $categoryId);
            $deleteStatement->execute();
            $deleted = $deleteStatement->affected_rows === 1;
            $deleteStatement->close();

            if (!$deleted) {
                throw new RuntimeException('Không thể xóa chuyên mục lúc này.');
            }

            $mysqli->commit();
            $mysqli->close();

            if ($imagePath !== '') {
                self::removeStoredImage($imagePath);
            }

            return array(
                'success' => true,
                'message' => 'Xóa chuyên mục thành công.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->rollback();
                $mysqli->close();
            }

            return array(
                'success' => false,
                'message' => 'Không thể xóa chuyên mục lúc này.',
            );
        } catch (RuntimeException $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->rollback();
                $mysqli->close();
            }

            return array(
                'success' => false,
                'message' => $exception->getMessage(),
            );
        }
    }

    private static function validateFields(string $categoryName, string $slug, string $description): ?string
    {
        if ($categoryName === '' || $slug === '') {
            return 'Tên chuyên mục và slug không được để trống.';
        }

        if (mb_strlen($categoryName) > 100 || mb_strlen($slug) > 100 || mb_strlen($description) > 255) {
            return 'Một trong các trường đã vượt quá độ dài cho phép.';
        }

        return null;
    }

    private static function ensureSlugIsAvailable(mysqli $mysqli, string $slug): void
    {
        $statement = $mysqli->prepare('SELECT category_id FROM categories WHERE slug = ? LIMIT 1');
        $statement->bind_param('s', $slug);
        $statement->execute();
        $statement->store_result();

        if ($statement->num_rows > 0) {
            $statement->close();
            throw new RuntimeException('Slug này đã tồn tại, vui lòng chọn slug khác.');
        }

        $statement->close();
    }

    private static function storeCategoryImage(array $imageFile, int $categoryId): string
    {
        $error = (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE);
        $temporaryPath = (string) ($imageFile['tmp_name'] ?? '');
        $fileSize = (int) ($imageFile['size'] ?? 0);

        if ($error !== UPLOAD_ERR_OK) {
            $uploadErrors = array(
                UPLOAD_ERR_INI_SIZE => 'Hình ảnh vượt quá giới hạn upload của máy chủ.',
                UPLOAD_ERR_FORM_SIZE => 'Hình ảnh vượt quá giới hạn cho phép của biểu mẫu.',
                UPLOAD_ERR_PARTIAL => 'Hình ảnh chỉ được tải lên một phần.',
                UPLOAD_ERR_NO_FILE => 'Bạn chưa chọn hình ảnh.',
                UPLOAD_ERR_NO_TMP_DIR => 'Máy chủ không có thư mục tạm để nhận hình ảnh.',
                UPLOAD_ERR_CANT_WRITE => 'Máy chủ không thể ghi hình ảnh tải lên.',
                UPLOAD_ERR_EXTENSION => 'Một tiện ích mở rộng của máy chủ đã dừng upload.',
            );

            throw new RuntimeException(
                $uploadErrors[$error] ?? 'Không thể nhận tệp hình ảnh tải lên.'
            );
        }

        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('Tệp tải lên không hợp lệ.');
        }

        if ($fileSize > 5 * 1024 * 1024) {
            throw new RuntimeException('Hình ảnh không được vượt quá 5MB.');
        }

        $imageInfo = @getimagesize($temporaryPath);
        $mimeType = '';
        if (is_array($imageInfo) && isset($imageInfo['mime'])) {
            $mimeType = (string) $imageInfo['mime'];
        }

        if (class_exists('finfo')) {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $detectedMimeType = $fileInfo->file($temporaryPath);
            if (is_string($detectedMimeType) && $detectedMimeType !== '') {
                $mimeType = $detectedMimeType;
            }
        }

        $extensions = array(
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        );

        if (!isset($extensions[$mimeType])) {
            throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG, GIF hoặc WEBP.');
        }

        $directory = dirname(__DIR__, 2) . '/public/assets/images/category_image';
        if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
            throw new RuntimeException('Không thể tạo thư mục lưu hình ảnh.');
        }

        $filename = $categoryId . '_' . date('YmdHis') . '.' . $extensions[$mimeType];
        $destination = $directory . '/' . $filename;

        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new RuntimeException('Không thể lưu hình ảnh vào thư mục đích.');
        }

        return 'public/assets/images/category_image/' . $filename;
    }

    private static function removeStoredImage(string $relativePath): void
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
