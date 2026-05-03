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

    private static function isAjax(): bool {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
            || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')
            || str_contains($_SERVER['CONTENT_TYPE'] ?? '', 'application/json');
    }

    public static function check(): void {
        // Accept both _csrf (forms) and _token (legacy JS) and header
        $token = $_POST['_csrf'] ?? ($_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? ''));
        if (!self::validate($token)) {
            http_response_code(419);
            if (self::isAjax()) {
                header('Content-Type: application/json');
                die(json_encode(['success' => false, 'message' => 'Session expired. Please reload the page.', 'csrf_error' => true]));
            }
            die('<h1>419 – CSRF Token Mismatch</h1><p>Please reload the page and try again.</p>');
        }
        // Don't rotate on AJAX — JS may call multiple endpoints from the same page load
        if (!self::isAjax()) {
            unset($_SESSION[self::$key]);
        }
    }

    /** Rotate and return new token (call after state-changing AJAX to refresh client) */
    public static function rotate(): string {
        unset($_SESSION[self::$key]);
        return self::generate();
    }
}
