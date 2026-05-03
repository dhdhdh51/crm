<?php
namespace Core;

class Session {
    private static int $timeout = 7200; // 2 hours

    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.cookie_samesite', 'Lax');
            session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            session_name('VVCRMSS');
            session_start();
        }
        self::checkTimeout();
    }

    private static function checkTimeout(): void {
        if (isset($_SESSION['_last_activity'])) {
            if (time() - $_SESSION['_last_activity'] > self::$timeout) {
                self::destroy();
                return;
            }
        }
        $_SESSION['_last_activity'] = time();
    }

    public static function set(string $key, mixed $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }

    public static function regenerate(): void {
        session_regenerate_id(true);
    }

    /** Flash messages — survive one redirect */
    public static function flash(string $key, mixed $value = null): mixed {
        if ($value !== null) {
            $_SESSION['_flash'][$key] = $value;
            return null;
        }
        $v = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $v;
    }

    public static function hasFlash(string $key): bool {
        return isset($_SESSION['_flash'][$key]);
    }

    /** Auth helpers */
    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function isLoggedIn(): bool {
        return isset($_SESSION['user']['id']);
    }

    public static function role(): string {
        return $_SESSION['user']['role_slug'] ?? '';
    }

    public static function can(string|array $roles): bool {
        $roles = (array)$roles;
        return in_array(self::role(), $roles, true);
    }
}
