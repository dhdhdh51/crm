<?php
namespace Core;

abstract class Controller {
    protected Database $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /** Render a view with layout */
    protected function view(string $view, array $data = [], string $layout = 'main'): void {
        extract($data);
        $viewFile = ROOT . '/app/views/' . str_replace('.', '/', $view) . '.php';
        if (!file_exists($viewFile)) {
            throw new \RuntimeException("View [{$view}] not found.");
        }

        // Capture view content
        ob_start();
        include $viewFile;
        $content = ob_get_clean();

        // Render in layout
        $layoutFile = ROOT . '/app/views/layouts/' . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    /** Render without layout */
    protected function partial(string $view, array $data = []): void {
        extract($data);
        include ROOT . '/app/views/' . str_replace('.', '/', $view) . '.php';
    }

    /** Send JSON response */
    protected function json(mixed $data, int $status = 200): never {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /** Redirect helper */
    protected function redirect(string $path): never {
        header('Location: ' . url($path));
        exit;
    }

    /** Validate CSRF on POST */
    protected function verifyCsrf(): void {
        CSRF::check();
    }

    /** Sanitize a single value */
    protected function sanitize(mixed $value): string {
        return trim(strip_tags((string)$value));
    }

    /** Sanitize entire array */
    protected function sanitizeAll(array $data, array $keys): array {
        $out = [];
        foreach ($keys as $key) {
            $out[$key] = isset($data[$key]) ? $this->sanitize($data[$key]) : '';
        }
        return $out;
    }

    /** Current logged-in user */
    protected function currentUser(): ?array {
        return Session::user();
    }

    /** Abort with HTTP status */
    protected function abort(int $code, string $message = ''): never {
        http_response_code($code);
        $errorView = ROOT . '/app/views/errors/' . $code . '.php';
        if (file_exists($errorView)) {
            $content = '<p>' . e($message) . '</p>';
            include $errorView;
        } else {
            echo "<h1>Error {$code}</h1><p>" . e($message) . "</p>";
        }
        exit;
    }

    /** Store old POST input in flash (for form repopulation) */
    protected function withInput(array $input): void {
        Session::flash('_old_input', $input);
    }
}
