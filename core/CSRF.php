<?php
namespace Core;

class CSRF {
    private static string $key = '_csrf_token';

    public static function generate(): string {
        if (empty($_SESSION[self::$key])) {
            $_SESSION[self::$key] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::$key];
    }

    public static function token(): string {
        return self::generate();
    }

    public static function validate(string $token): bool {
        $stored = $_SESSION[self::$key] ?? '';
        if (!$stored || !$token) return false;
        return hash_equals($stored, $token);
    }

    public static function field(): string {
        return '<input type="hidden" name="_csrf" value="' . self::token() . '">';
    }

    public static function check(): void {
        $token = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if (!self::validate($token)) {
            http_response_code(419);
            die('<h1>419 – CSRF Token Mismatch</h1><p>Please reload the page and try again.</p>');
        }
        // Rotate token after successful validation
        unset($_SESSION[self::$key]);
    }
}
