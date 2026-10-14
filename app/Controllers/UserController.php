<?php

declare(strict_types=1);

final class UserController
{
    public static function lock(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $mysqli = null;

        if ($userId < 1) {
            return array('success' => false, 'message' => 'Tài khoản cần khóa không hợp lệ.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $statement = $mysqli->prepare(
                'UPDATE users SET status = 2 WHERE user_id = ? AND role_id = 2'
            );
            $statement->bind_param('i', $userId);
            $statement->execute();
            $locked = $statement->affected_rows === 1;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $locked,
                'message' => $locked
                    ? 'Khóa tài khoản thành công.'
                    : 'Không tìm thấy tài khoản người dùng cần khóa.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể khóa tài khoản lúc này.');
        }
    }

    public static function create(array $input): array
    {
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $status = (int) ($input['status'] ?? 1);
        $roleId = 2;
        $mysqli = null;

        if ($username === '' || $email === '' || $fullName === '' || $password === '') {
            return array('success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin người dùng.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'message' => 'Email không hợp lệ.');
        }
        if (!in_array($status, array(1, 2), true)) {
            return array('success' => false, 'message' => 'Trạng thái người dùng không hợp lệ.');
        }
        if (mb_strlen($username) > 100 || mb_strlen($email) > 255 || mb_strlen($fullName) > 255) {
            return array('success' => false, 'message' => 'Thông tin người dùng vượt quá độ dài cho phép.');
        }
        if (mb_strlen($password) < 6) {
            return array('success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $statement = $mysqli->prepare(
                'INSERT INTO users (role_id, username, email, password, full_name, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $statement->bind_param(
                'issssi',
                $roleId,
                $username,
                $email,
                $passwordHash,
                $fullName,
                $status
            );
            $statement->execute();
            $statement->close();
            $mysqli->close();

            return array('success' => true, 'message' => 'Thêm người dùng thành công.');
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            if ($exception->getCode() === 1062) {
                return array('success' => false, 'message' => 'Username hoặc email đã tồn tại.');
            }

            return array('success' => false, 'message' => 'Không thể thêm người dùng lúc này.');
        }
    }

    public static function update(array $input): array
    {
        $userId = (int) ($input['user_id'] ?? 0);
        $roleId = (int) ($input['role_id'] ?? 0);
        $username = trim((string) ($input['username'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $status = (int) ($input['status'] ?? 0);
        $password = (string) ($input['password'] ?? '');
        $mysqli = null;

        if ($userId < 1 || $roleId < 1 || $username === '' || $email === '' || $fullName === '') {
            return array('success' => false, 'message' => 'Vui lòng nhập đầy đủ thông tin người dùng.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'message' => 'Email không hợp lệ.');
        }
        if (!in_array($status, array(1, 2), true)) {
            return array('success' => false, 'message' => 'Trạng thái người dùng không hợp lệ.');
        }
        if (mb_strlen($username) > 100 || mb_strlen($email) > 255 || mb_strlen($fullName) > 255) {
            return array('success' => false, 'message' => 'Thông tin người dùng vượt quá độ dài cho phép.');
        }
        if ($password !== '' && mb_strlen($password) < 6) {
            return array('success' => false, 'message' => 'Mật khẩu mới phải có ít nhất 6 ký tự.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();

            if ($password === '') {
                $statement = $mysqli->prepare(
                    'UPDATE users
                     SET role_id = ?, username = ?, email = ?, full_name = ?, status = ?
                     WHERE user_id = ?'
                );
                $statement->bind_param(
                    'isssii',
                    $roleId,
                    $username,
                    $email,
                    $fullName,
                    $status,
                    $userId
                );
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $statement = $mysqli->prepare(
                    'UPDATE users
                     SET role_id = ?, username = ?, email = ?, full_name = ?, status = ?, password = ?
                     WHERE user_id = ?'
                );
                $statement->bind_param(
                    'isssisi',
                    $roleId,
                    $username,
                    $email,
                    $fullName,
                    $status,
                    $passwordHash,
                    $userId
                );
            }

            $statement->execute();
            $updated = $statement->affected_rows >= 0;
            $statement->close();
            $mysqli->close();

            return array(
                'success' => $updated,
                'message' => $updated
                    ? 'Cập nhật người dùng thành công.'
                    : 'Không tìm thấy người dùng cần cập nhật.',
            );
        } catch (mysqli_sql_exception $exception) {
            if ($mysqli instanceof mysqli) {
                $mysqli->close();
            }

            if ($exception->getCode() === 1062) {
                return array('success' => false, 'message' => 'Username hoặc email đã tồn tại.');
            }

            return array('success' => false, 'message' => 'Không thể cập nhật người dùng lúc này.');
        }
    }

    public static function all(): array
    {
        require_once __DIR__ . '/../Config/Database.php';
        $mysqli = Database::connect();

        try {
            $result = $mysqli->query(
                'SELECT users.user_id, users.role_id, users.username, users.email, users.full_name,
                        users.status, users.created_at, roles.role_name
                 FROM users
                 INNER JOIN roles ON roles.role_id = users.role_id
                 WHERE users.role_id = 2
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
