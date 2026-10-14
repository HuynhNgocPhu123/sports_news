<?php

declare(strict_types=1);

final class ArticleController
{
    public static function update(array $input, ?array $imageFile = null): array
    {
        $articleId = (int) ($input['article_id'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $summary = trim((string) ($input['summary'] ?? ''));
        $content = trim((string) ($input['content'] ?? ''));
        $status = (string) ($input['status'] ?? 'draft');
        $publishedAt = trim((string) ($input['published_at'] ?? ''));
        $currentImage = trim((string) ($input['current_images'] ?? ''));
        $storedImage = null;
        $mysqli = null;

        if ($articleId < 1 || $categoryId < 1 || $title === '' || $slug === '' || $content === '') {
            return array('success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
        }

        if (!in_array($status, array('draft', 'published', 'hidden'), true)) {
            return array('success' => false, 'message' => 'Trạng thái bài viết không hợp lệ.');
        }

        if (mb_strlen($title) > 255 || mb_strlen($slug) > 255) {
            return array('success' => false, 'message' => 'Tiêu đề hoặc slug vượt quá độ dài cho phép.');
        }

        $publishedAt = $publishedAt === '' ? null : str_replace('T', ' ', $publishedAt);

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();

            if (is_array($imageFile) && (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $storedImage = self::storeArticleImage($imageFile, $articleId);
                $currentImage = $storedImage;
            }

            $statement = $mysqli->prepare(
                'UPDATE articles
                 SET category_id = ?, title = ?, slug = ?, summary = ?, content = ?,
                     status = ?, published_at = ?, images = ?
                 WHERE article_id = ?'
            );
            $statement->bind_param(
                'isssssssi',
                $categoryId,
                $title,
                $slug,
                $summary,
                $content,
                $status,
                $publishedAt,
                $currentImage,
                $articleId
            );
            $statement->execute();
            $updated = $statement->affected_rows >= 0;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $updated,
                'message' => $updated ? 'Cập nhật bài viết thành công.' : 'Không tìm thấy bài viết cần cập nhật.',
            );
        } catch (RuntimeException $exception) {
            if ($storedImage !== null) {
                self::removeStoredImage($storedImage);
            }
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => $exception->getMessage());
        } catch (mysqli_sql_exception $exception) {
            if ($storedImage !== null) {
                self::removeStoredImage($storedImage);
            }
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể cập nhật bài viết lúc này.');
        }
    }

    public static function all(): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $result = $mysqli->query(
                'SELECT articles.article_id, articles.category_id, articles.title,
                        articles.slug, articles.summary, articles.content,
                        articles.status, articles.views,
                        articles.published_at, articles.created_at, articles.images,
                        categories.category_name
                 FROM articles
                 LEFT JOIN categories ON categories.category_id = articles.category_id
                 ORDER BY articles.created_at DESC, articles.article_id DESC'
            );

            $articles = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();

            return $articles;
        } finally {
            $mysqli->close();
        }
    }

    private static function storeArticleImage(array $imageFile, int $articleId): string
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
            $mimeType = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';
            $extensions = array(
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            );
            if (!isset($extensions[$mimeType])) {
                throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG, GIF hoặc WEBP.');
            }

            $directory = dirname(__DIR__, 2) . '/public/assets/images/article_image';
            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                throw new RuntimeException('Không thể tạo thư mục lưu hình ảnh bài viết.');
            }

            $filename = $articleId . '_' . date('YmdHis') . '.' . $extensions[$mimeType];
            if (!move_uploaded_file($temporaryPath, $directory . '/' . $filename)) {
                throw new RuntimeException('Không thể lưu hình ảnh bài viết.');
            }

            return 'public/assets/images/article_image/' . $filename;
    }

    private static function removeStoredImage(string $relativePath): void
    {
        $absolutePath = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
