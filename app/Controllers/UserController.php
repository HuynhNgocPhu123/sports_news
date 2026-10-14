<?php

declare(strict_types=1);

final class UserController
{
    public static function all(): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $result = $mysqli->query(
                'SELECT users.user_id, users.username, users.email, users.full_name,
                        users.status, users.created_at, roles.role_name
                 FROM users
                 INNER JOIN roles ON roles.role_id = users.role_id
                 ORDER BY users.user_id ASC'
            );

            $users = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();

            return $users;
        } finally {
            $mysqli->close();
        }
    }
}
