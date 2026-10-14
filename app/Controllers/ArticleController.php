<?php

declare(strict_types=1);

final class ArticleController
{
    public static function create(array $input, ?array $imageFile = null): array
    {
        $authorId = (int) ($input['author_id'] ?? 0);
        $categoryId = (int) ($input['category_id'] ?? 0);
        $title = trim((string) ($input['title'] ?? ''));
        $slug = trim((string) ($input['slug'] ?? ''));
        $summary = trim((string) ($input['summary'] ?? ''));
        $content = trim((string) ($input['content'] ?? ''));
        $status = (string) ($input['status'] ?? 'draft');
        $publishedAt = trim((string) ($input['published_at'] ?? ''));
        $storedImage = null;
        $mysqli = null;

        if ($authorId < 1 || $categoryId < 1 || $title === '' || $slug === '' || $content === '') {
            return array('success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin bắt buộc.');
        }
        if (!in_array($status, array('draft', 'published', 'hidden'), true)) {
            return array('success' => false, 'message' => 'Trạng thái bài viết không hợp lệ.');
        }

        $publishedAt = $publishedAt === '' ? null : str_replace('T', ' ', $publishedAt);

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $emptyImage = '';
            $statement = $mysqli->prepare(
                'INSERT INTO articles
                    (category_id, author_id, title, slug, summary, content, status, published_at, images)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param(
                'iisssssss',
                $categoryId,
                $authorId,
                $title,
                $slug,
                $summary,
                $content,
                $status,
                $publishedAt,
                $emptyImage
            );
            $statement->execute();
            $articleId = $mysqli->insert_id;
            $statement->close();

            if (is_array($imageFile) && (int) ($imageFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                $storedImage = self::storeArticleImage($imageFile, $articleId);
                $imageStatement = $mysqli->prepare(
                    'UPDATE articles SET images = ? WHERE article_id = ?'
                );
                $imageStatement->bind_param('si', $storedImage, $articleId);
                $imageStatement->execute();
                $imageStatement->close();
            }

            $mysqli->close();

            return array('success' => true, 'message' => 'Thêm bài viết thành công.');
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

            return array('success' => false, 'message' => 'Không thể thêm bài viết lúc này.');
        }
    }

    public static function toggleVisibility(array $input): array
    {
        $articleId = (int) ($input['article_id'] ?? 0);

        if ($articleId < 1) {
            return array('success' => false, 'message' => 'Bài viết cần cập nhật không hợp lệ.');
        }

        $mysqli = null;

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();

            $select = $mysqli->prepare(
                'SELECT status, published_at FROM articles WHERE article_id = ? LIMIT 1'
            );
            $select->bind_param('i', $articleId);
            $select->execute();
            $select->bind_result($currentStatus, $publishedAt);

            if (!$select->fetch()) {
                $select->close();
                $mysqli->close();

                return array('success' => false, 'message' => 'Không tìm thấy bài viết cần cập nhật.');
            }

            $select->close();
            $nextStatus = $currentStatus === 'published' ? 'hidden' : 'published';

            if ($nextStatus === 'published' && ($publishedAt === null || $publishedAt === '')) {
                $publishedAt = date('Y-m-d H:i:s');
            }

            $update = $mysqli->prepare(
                'UPDATE articles SET status = ?, published_at = ? WHERE article_id = ?'
            );
            $update->bind_param('ssi', $nextStatus, $publishedAt, $articleId);
            $update->execute();
            $updated = $update->affected_rows >= 0;
            $update->close();
            $mysqli->close();

            return array(
                'success' => $updated,
                'message' => $nextStatus === 'published'
                    ? 'Đăng bài viết thành công.'
                    : 'Ẩn bài viết thành công.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể cập nhật trạng thái bài viết lúc này.');
        }
    }

    public static function delete(array $input): array
    {
        $articleId = (int) ($input['article_id'] ?? 0);

        if ($articleId < 1) {
            return array('success' => false, 'message' => 'Bài viết cần xóa không hợp lệ.');
        }

        $mysqli = null;

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $statement = $mysqli->prepare(
                'DELETE FROM articles WHERE article_id = ?'
            );
            $statement->bind_param('i', $articleId);
            $statement->execute();
            $deleted = $statement->affected_rows === 1;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $deleted,
                'message' => $deleted
                    ? 'Xóa bài viết thành công.'
                    : 'Không tìm thấy bài viết cần xóa.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể xóa bài viết lúc này.');
        }
    }

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

    public static function published(?string $categorySlug = null): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $query = 'SELECT articles.article_id, articles.category_id, articles.title,
                             articles.slug, articles.summary, articles.content,
                             articles.status, articles.views, articles.published_at,
                             articles.created_at, articles.images, categories.category_name
                      FROM articles
                      LEFT JOIN categories ON categories.category_id = articles.category_id
                      WHERE articles.status = \'published\'';

            if ($categorySlug !== null && $categorySlug !== '') {
                $query .= ' AND categories.slug = ?';
                $query .= ' ORDER BY COALESCE(articles.published_at, articles.created_at) DESC,
                                   articles.article_id DESC';
                $statement = $mysqli->prepare($query);
                $statement->bind_param('s', $categorySlug);
                $statement->execute();
                $result = $statement->get_result();
            } else {
                $query .= ' ORDER BY COALESCE(articles.published_at, articles.created_at) DESC,
                                   articles.article_id DESC';
                $result = $mysqli->query($query);
            }

            $articles = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
            if (isset($statement)) {
                $statement->close();
            }

            return $articles;
        } finally {
            $mysqli->close();
        }
    }

    public static function searchPublished(string $keyword): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();
        $searchTerm = '%' . $keyword . '%';

        try {
            $statement = $mysqli->prepare(
                'SELECT articles.article_id, articles.category_id, articles.title,
                        articles.slug, articles.summary, articles.content,
                        articles.status, articles.views, articles.published_at,
                        articles.created_at, articles.images, categories.category_name
                 FROM articles
                 LEFT JOIN categories ON categories.category_id = articles.category_id
                 WHERE articles.status = \'published\'
                   AND (
                       articles.title LIKE ?
                       OR articles.summary LIKE ?
                       OR articles.content LIKE ?
                       OR categories.category_name LIKE ?
                   )
                 ORDER BY COALESCE(articles.published_at, articles.created_at) DESC,
                          articles.article_id DESC'
            );
            $statement->bind_param(
                'ssss',
                $searchTerm,
                $searchTerm,
                $searchTerm,
                $searchTerm
            );
            $statement->execute();
            $articles = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return $articles;
        } finally {
            $mysqli->close();
        }
    }

    public static function findPublishedBySlug(string $slug): ?array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $statement = $mysqli->prepare(
                'SELECT articles.article_id, articles.category_id, articles.title,
                        articles.slug, articles.summary, articles.content,
                        articles.status, articles.views, articles.published_at,
                        articles.created_at, articles.images,
                        categories.category_name, categories.slug AS category_slug,
                        users.full_name AS author_name
                 FROM articles
                 LEFT JOIN categories ON categories.category_id = articles.category_id
                 LEFT JOIN users ON users.user_id = articles.author_id
                 WHERE articles.slug = ? AND articles.status = \'published\'
                 LIMIT 1'
            );
            $statement->bind_param('s', $slug);
            $statement->execute();
            $article = $statement->get_result()->fetch_assoc();
            $statement->close();

            return is_array($article) ? $article : null;
        } finally {
            $mysqli->close();
        }
    }

    public static function relatedPublished(int $categoryId, int $articleId, int $limit = 3): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();
        $limit = max(1, min($limit, 10));

        try {
            $statement = $mysqli->prepare(
                'SELECT articles.article_id, articles.title, articles.slug,
                        articles.images, articles.views, categories.category_name
                 FROM articles
                 LEFT JOIN categories ON categories.category_id = articles.category_id
                 WHERE articles.category_id = ? AND articles.article_id <> ?
                   AND articles.status = \'published\'
                 ORDER BY COALESCE(articles.published_at, articles.created_at) DESC,
                          articles.article_id DESC
                 LIMIT ?'
            );
            $statement->bind_param('iii', $categoryId, $articleId, $limit);
            $statement->execute();
            $related = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return $related;
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
