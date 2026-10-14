<?php

declare(strict_types=1);

final class RatingController
{
    public static function create(array $input, int $userId): array
    {
        $articleId = (int) ($input['article_id'] ?? 0);
        $ratingValue = (int) ($input['rating_value'] ?? 0);
        $comment = trim((string) ($input['comment'] ?? ''));
        $mysqli = null;

        if ($userId < 1 || $articleId < 1 || $ratingValue < 1 || $ratingValue > 5 || $comment === '') {
            return array('success' => false, 'message' => 'Vui lòng chọn số sao và nhập bình luận.');
        }
        if (mb_strlen($comment) > 2000) {
            return array('success' => false, 'message' => 'Bình luận không được vượt quá 2000 ký tự.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $statement = $mysqli->prepare(
                'INSERT INTO ratings (article_id, user_id, comment, rating_value)
                 SELECT ?, ?, ?, ?
                 FROM articles
                 WHERE article_id = ? AND status = \'published\'
                   AND NOT EXISTS (
                       SELECT 1
                       FROM ratings
                       WHERE article_id = ? AND user_id = ?
                   )'
            );
            $statement->bind_param(
                'iisiiii',
                $articleId,
                $userId,
                $comment,
                $ratingValue,
                $articleId,
                $articleId,
                $userId
            );
            $statement->execute();
            $created = $statement->affected_rows === 1;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $created,
                'message' => $created
                    ? 'Đánh giá bài viết thành công.'
                    : 'Bạn đã đánh giá bài viết này rồi hoặc bài viết không còn khả dụng.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể lưu đánh giá lúc này.');
        }
    }

    public static function summary(int $articleId): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $statement = $mysqli->prepare(
                'SELECT COUNT(*) AS total_ratings, COALESCE(AVG(rating_value), 0) AS average_rating
                 FROM ratings WHERE article_id = ? AND status = 1'
            );
            $statement->bind_param('i', $articleId);
            $statement->execute();
            $summary = $statement->get_result()->fetch_assoc();
            $statement->close();

            return is_array($summary) ? $summary : array('total_ratings' => 0, 'average_rating' => 0);
        } finally {
            $mysqli->close();
        }
    }

    public static function update(array $input, int $userId): array
    {
        $ratingId = (int) ($input['rating_id'] ?? 0);
        $articleId = (int) ($input['article_id'] ?? 0);
        $ratingValue = (int) ($input['rating_value'] ?? 0);
        $comment = trim((string) ($input['comment'] ?? ''));

        if ($userId < 1 || $ratingId < 1 || $articleId < 1 || $ratingValue < 1 || $ratingValue > 5 || $comment === '') {
            return array('success' => false, 'message' => 'Vui lòng chọn số sao và nhập bình luận.');
        }
        if (mb_strlen($comment) > 2000) {
            return array('success' => false, 'message' => 'Bình luận không được vượt quá 2000 ký tự.');
        }

        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();
        try {
            $ownership = $mysqli->prepare(
                'SELECT rating_id FROM ratings WHERE rating_id = ? AND article_id = ? AND user_id = ? LIMIT 1'
            );
            $ownership->bind_param('iii', $ratingId, $articleId, $userId);
            $ownership->execute();
            $ownership->store_result();
            $ownsRating = $ownership->num_rows === 1;
            $ownership->close();
            if (!$ownsRating) {
                return array('success' => false, 'message' => 'Bạn không có quyền sửa đánh giá này.');
            }

            $statement = $mysqli->prepare(
                'UPDATE ratings
                 SET comment = ?, rating_value = ?
                 WHERE rating_id = ? AND article_id = ? AND user_id = ?'
            );
            $statement->bind_param('siiii', $comment, $ratingValue, $ratingId, $articleId, $userId);
            $statement->execute();
            $updated = $statement->errno === 0;
            $statement->close();

            return array(
                'success' => $updated,
                'message' => $updated ? 'Đã cập nhật đánh giá.' : 'Không thể cập nhật đánh giá.',
            );
        } finally {
            $mysqli->close();
        }
    }

    public static function delete(array $input, int $userId): array
    {
        $ratingId = (int) ($input['rating_id'] ?? 0);
        $articleId = (int) ($input['article_id'] ?? 0);
        if ($userId < 1 || $ratingId < 1 || $articleId < 1) {
            return array('success' => false, 'message' => 'Đánh giá không hợp lệ.');
        }

        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();
        try {
            $statement = $mysqli->prepare(
                'DELETE FROM ratings WHERE rating_id = ? AND article_id = ? AND user_id = ?'
            );
            $statement->bind_param('iii', $ratingId, $articleId, $userId);
            $statement->execute();
            $deleted = $statement->affected_rows === 1;
            $statement->close();

            return array(
                'success' => $deleted,
                'message' => $deleted ? 'Đã xóa đánh giá.' : 'Không thể xóa đánh giá.',
            );
        } finally {
            $mysqli->close();
        }
    }

    public static function hasRated(int $articleId, int $userId): bool
    {
        if ($articleId < 1 || $userId < 1) {
            return false;
        }

        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $statement = $mysqli->prepare(
                'SELECT rating_id FROM ratings WHERE article_id = ? AND user_id = ? LIMIT 1'
            );
            $statement->bind_param('ii', $articleId, $userId);
            $statement->execute();
            $statement->store_result();
            $hasRated = $statement->num_rows > 0;
            $statement->close();

            return $hasRated;
        } finally {
            $mysqli->close();
        }
    }

    public static function allForArticle(int $articleId): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $statement = $mysqli->prepare(
                'SELECT ratings.rating_id, ratings.user_id, ratings.comment, ratings.rating_value, ratings.created_at,
                        users.full_name, users.username
                 FROM ratings
                 INNER JOIN users ON users.user_id = ratings.user_id
                 WHERE ratings.article_id = ? AND ratings.status = 1
                 ORDER BY ratings.created_at DESC, ratings.rating_id DESC'
            );
            $statement->bind_param('i', $articleId);
            $statement->execute();
            $ratings = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
            $statement->close();

            return $ratings;
        } finally {
            $mysqli->close();
        }
    }

    public static function allForAdmin(): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $result = $mysqli->query(
                'SELECT ratings.rating_id, ratings.article_id, ratings.user_id, ratings.comment,
                        ratings.rating_value, ratings.status, ratings.created_at,
                        users.full_name, users.username,
                        articles.title AS article_title
                 FROM ratings
                 INNER JOIN users ON users.user_id = ratings.user_id
                 INNER JOIN articles ON articles.article_id = ratings.article_id
                 ORDER BY ratings.created_at DESC, ratings.rating_id DESC'
            );

            return $result->fetch_all(MYSQLI_ASSOC);
        } finally {
            $mysqli->close();
        }
    }

    public static function toggleVisibility(array $input): array
    {
        $ratingId = (int) ($input['rating_id'] ?? 0);
        if ($ratingId < 1) {
            return array('success' => false, 'message' => 'Đánh giá không hợp lệ.');
        }

        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();
        try {
            $statement = $mysqli->prepare(
                'UPDATE ratings
                 SET status = CASE WHEN status = 1 THEN 2 ELSE 1 END
                 WHERE rating_id = ?'
            );
            $statement->bind_param('i', $ratingId);
            $statement->execute();
            $updated = $statement->affected_rows === 1;
            $statement->close();

            return array(
                'success' => $updated,
                'message' => $updated ? 'Đã cập nhật trạng thái đánh giá.' : 'Không thể cập nhật đánh giá.',
            );
        } finally {
            $mysqli->close();
        }
    }
}
