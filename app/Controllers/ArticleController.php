<?php

declare(strict_types=1);

final class ArticleController
{
    public static function all(): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $result = $mysqli->query(
                'SELECT articles.article_id, articles.category_id, articles.title,
                        articles.slug, articles.status, articles.views,
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
}
