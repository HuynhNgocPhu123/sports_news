<?php

declare(strict_types=1);

final class AuthController
{
    public static function login(array $input): array
    {
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || $password === '') {
            return array('success' => false, 'message' => 'Vui lòng nhập email và mật khẩu.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'message' => 'Địa chỉ email không hợp lệ.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();
            $statement = $mysqli->prepare(
                'SELECT users.user_id, users.password, users.role_id, users.status, roles.role_name, users.full_name
                 FROM users
                 INNER JOIN roles ON roles.role_id = users.role_id
                 WHERE users.email = ?
                 LIMIT 1'
            );
            $statement->bind_param('s', $email);
            $statement->execute();
            $statement->bind_result($userId, $passwordHash, $roleId, $status, $roleName, $fullName);

            if (!$statement->fetch()) {
                $statement->close();
                $mysqli->close();

                return array('success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.');
            }

            $statement->close();
            $mysqli->close();

            if ((int) $status === 2) {
                return array(
                    'success' => false,
                    'message' => 'Tài khoản của bạn đã bị khóa vì vi phạm nguyên tắc cộng đồng.',
                );
            }

            if ((int) $status !== 1) {
                return array('success' => false, 'message' => 'Tài khoản của bạn hiện không thể đăng nhập.');
            }

            if (!password_verify($password, $passwordHash)) {
                return array('success' => false, 'message' => 'Email hoặc mật khẩu không chính xác.');
            }

            $loginData = array(
                'success' => true,
                'message' => 'Đăng nhập thành công.',
                'user_id' => $userId,
                'role_id' => $roleId,
                'role_name' => $roleName,
                'full_name' => $fullName,
            );

            session_regenerate_id(true);
            $_SESSION['auth'] = array(
                'user_id' => $loginData['user_id'],
                'role_id' => $loginData['role_id'],
                'role_name' => $loginData['role_name'],
                'full_name' => $loginData['full_name'],
            );

            return $loginData;
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể đăng nhập lúc này. Vui lòng thử lại sau.');
        }
    }

    public static function register(array $input): array
    {
        $fullName = trim((string) ($input['full_name'] ?? ''));
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $passwordConfirmation = (string) ($input['password_confirmation'] ?? '');

        if ($fullName === '' || $email === '' || $password === '' || $passwordConfirmation === '') {
            return array('success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin bắt buộc.');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return array('success' => false, 'message' => 'Địa chỉ email không hợp lệ.');
        }

        if (mb_strlen($fullName) > 100 || mb_strlen($email) > 100) {
            return array('success' => false, 'message' => 'Họ tên hoặc email vượt quá độ dài cho phép.');
        }

        if (strlen($password) < 8) {
            return array('success' => false, 'message' => 'Mật khẩu phải có ít nhất 8 ký tự.');
        }

        if ($password !== $passwordConfirmation) {
            return array('success' => false, 'message' => 'Mật khẩu xác nhận không khớp.');
        }

        if (!isset($input['terms'])) {
            return array('success' => false, 'message' => 'Bạn cần đồng ý với điều khoản sử dụng.');
        }

        try {
            require_once __DIR__ . '/../Config/Database.php';
            $mysqli = Database::connect();

            $emailStatement = $mysqli->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
            $emailStatement->bind_param('s', $email);
            $emailStatement->execute();
            $emailStatement->store_result();

            if ($emailStatement->num_rows > 0) {
                $emailStatement->close();
                $mysqli->close();

                return array('success' => false, 'message' => 'Email này đã được đăng ký.');
            }

            $emailStatement->close();
            $username = self::createUsername($mysqli, $email);
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $roleId = 2;
            $status = 1;

            if ($passwordHash === false) {
                throw new RuntimeException('Không thể mã hóa mật khẩu.');
            }

            $insertStatement = $mysqli->prepare(
                'INSERT INTO users (role_id, username, email, password, full_name, status)
                 VALUES (?, ?, ?, ?, ?, ?)'
            );
            $insertStatement->bind_param('issssi', $roleId, $username, $email, $passwordHash, $fullName, $status);
            $insertStatement->execute();
            $insertStatement->close();
            $mysqli->close();

            return array('success' => true, 'message' => 'Đăng ký tài khoản thành công. Chào mừng bạn đến với Football News!');
        } catch (mysqli_sql_exception | RuntimeException $exception) {
            if (isset($mysqli) && $mysqli instanceof mysqli) {
                $mysqli->close();
            }

            return array('success' => false, 'message' => 'Không thể tạo tài khoản lúc này. Vui lòng thử lại sau.');
        }
    }

    private static function createUsername(mysqli $mysqli, string $email): string
    {
        $localPart = strstr($email, '@', true);
        $baseUsername = preg_replace('/[^a-zA-Z0-9_]/', '', (string) $localPart);
        $baseUsername = substr($baseUsername !== '' ? $baseUsername : 'user', 0, 44);
        $username = $baseUsername;
        $suffix = 1;

        while (self::usernameExists($mysqli, $username)) {
            $suffixText = (string) $suffix;
            $username = substr($baseUsername, 0, 50 - strlen($suffixText)) . $suffixText;
            $suffix++;
        }

        return $username;
    }

    private static function usernameExists(mysqli $mysqli, string $username): bool
    {
        $statement = $mysqli->prepare('SELECT user_id FROM users WHERE username = ? LIMIT 1');
        $statement->bind_param('s', $username);
        $statement->execute();
        $statement->store_result();
        $exists = $statement->num_rows > 0;
        $statement->close();

        return $exists;
    }
}
