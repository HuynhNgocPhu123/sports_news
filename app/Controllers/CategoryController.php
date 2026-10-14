<?php

declare(strict_types=1);

final class CategoryController
{
    public static function update(array $input, ?array $imageFile = null): array
    {
        $categoryId = (int) ($input['category_id'] ?? 0);
        $categoryName = trim((string) ($input['category_name'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $images = trim((string) ($input['current_images'] ?? ''));
        $description = trim((string) ($input['description'] ?? ''));

        if ($categoryId < 1 || $categoryName === '' || $slug === '') {
            return array(
                'success' => false,
                'message' => 'Tên chuyên mục và slug không được để trống.',
            );
        }

        if (mb_strlen($categoryName) > 100 || mb_strlen($slug) > 100 || mb_strlen($description) > 255) {
            return array(
                'success' => false,
                'message' => 'Một trong các trường đã vượt quá độ dài cho phép.',
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

    private static function storeCategoryImage(array $imageFile, int $categoryId): string
    {
        $error = (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE);
        $temporaryPath = (string) ($imageFile['tmp_name'] ?? '');
        $fileSize = (int) ($imageFile['size'] ?? 0);

        if ($error !== UPLOAD_ERR_OK || $temporaryPath === '' || !is_uploaded_file($temporaryPath)) {
            throw new RuntimeException('Không thể nhận tệp hình ảnh tải lên.');
        }

        if ($fileSize > 5 * 1024 * 1024) {
            throw new RuntimeException('Hình ảnh không được vượt quá 5MB.');
        }

        $imageInfo = @getimagesize($temporaryPath);
        $mimeType = $imageInfo['mime'] ?? '';
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
}
