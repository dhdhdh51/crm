<?php
use Core\Session;
use Core\CSRF;

if (!function_exists('e')) {
    /** XSS-safe output */
    function e(mixed $value): string {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('url')) {
    function url(string $path = ''): string {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    function asset(string $path): string {
        return url('assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string {
        return CSRF::field();
    }
}

if (!function_exists('flash')) {
    function flash(string $key, mixed $value = null): mixed {
        return Session::flash($key, $value);
    }
}

if (!function_exists('old')) {
    /** Retrieve old POST input (stored in flash) */
    function old(string $key, string $default = ''): string {
        $old = Session::flash('_old_input') ?? [];
        return e($old[$key] ?? $default);
    }
}

if (!function_exists('formatMoney')) {
    function formatMoney(float $amount, string $symbol = '₹'): string {
        return $symbol . ' ' . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('formatDate')) {
    function formatDate(?string $date, string $format = 'd M Y'): string {
        if (!$date) return '–';
        return date($format, strtotime($date));
    }
}

if (!function_exists('leadStatusBadge')) {
    function leadStatusBadge(string $status): string {
        $map = [
            'new'    => 'badge-info',
            'hot'    => 'badge-danger',
            'warm'   => 'badge-warning',
            'cold'   => 'badge-secondary',
            'closed' => 'badge-success',
            'lost'   => 'badge-dark',
        ];
        $cls = $map[$status] ?? 'badge-secondary';
        return '<span class="badge ' . $cls . '">' . ucfirst(e($status)) . '</span>';
    }
}

if (!function_exists('unitStatusBadge')) {
    function unitStatusBadge(string $status): string {
        $map = [
            'available' => 'badge-success',
            'booked'    => 'badge-danger',
            'sold'      => 'badge-dark',
            'reserved'  => 'badge-warning',
        ];
        $cls = $map[$status] ?? 'badge-secondary';
        return '<span class="badge ' . $cls . '">' . ucfirst(e($status)) . '</span>';
    }
}

if (!function_exists('visitStatusBadge')) {
    function visitStatusBadge(string $status): string {
        $map = [
            'scheduled'  => 'badge-info',
            'completed'  => 'badge-success',
            'cancelled'  => 'badge-danger',
            'no_show'    => 'badge-warning',
        ];
        $cls = $map[$status] ?? 'badge-secondary';
        return '<span class="badge ' . $cls . '">' . ucfirst(str_replace('_', ' ', e($status))) . '</span>';
    }
}

if (!function_exists('attendanceStatusBadge')) {
    function attendanceStatusBadge(string $status): string {
        $map = [
            'present'  => 'badge-success',
            'absent'   => 'badge-danger',
            'late'     => 'badge-warning',
            'half_day' => 'badge-secondary',
            'holiday'  => 'badge-info',
        ];
        $cls = $map[$status] ?? 'badge-secondary';
        return '<span class="badge ' . $cls . '">' . ucfirst(str_replace('_', ' ', e($status))) . '</span>';
    }
}

if (!function_exists('paginate')) {
    /**
     * Build pagination HTML
     * Returns ['offset' => int, 'html' => string]
     */
    function paginate(int $total, int $perPage, int $currentPage, string $baseUrl): array {
        $totalPages = max(1, (int)ceil($total / $perPage));
        $offset     = ($currentPage - 1) * $perPage;

        if ($totalPages <= 1) return ['offset' => $offset, 'html' => ''];

        $sep = str_contains($baseUrl, '?') ? '&' : '?';
        $html  = '<nav><ul class="pagination">';

        // Previous
        $prevDisabled = $currentPage <= 1 ? ' disabled' : '';
        $prevHref = $currentPage > 1 ? $baseUrl . $sep . 'page=' . ($currentPage - 1) : '#';
        $html .= '<li class="page-item' . $prevDisabled . '"><a class="page-link" href="' . $prevHref . '">‹</a></li>';

        // Pages (show max 7 links with ellipsis)
        $range = range(max(1, $currentPage - 3), min($totalPages, $currentPage + 3));
        if (!in_array(1, $range)) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $sep . 'page=1">1</a></li>';
            if ($range[0] > 2) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
        }
        foreach ($range as $p) {
            $active = $p === $currentPage ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $baseUrl . $sep . 'page=' . $p . '">' . $p . '</a></li>';
        }
        if (!in_array($totalPages, $range)) {
            if ($range[count($range) - 1] < $totalPages - 1) $html .= '<li class="page-item disabled"><span class="page-link">…</span></li>';
            $html .= '<li class="page-item"><a class="page-link" href="' . $baseUrl . $sep . 'page=' . $totalPages . '">' . $totalPages . '</a></li>';
        }

        // Next
        $nextDisabled = $currentPage >= $totalPages ? ' disabled' : '';
        $nextHref = $currentPage < $totalPages ? $baseUrl . $sep . 'page=' . ($currentPage + 1) : '#';
        $html .= '<li class="page-item' . $nextDisabled . '"><a class="page-link" href="' . $nextHref . '">›</a></li>';

        $html .= '</ul></nav>';
        return ['offset' => $offset, 'html' => $html];
    }
}

if (!function_exists('logActivity')) {
    function logActivity(string $action, string $module, ?int $recordId = null, string $details = ''): void {
        try {
            $db = \Core\Database::getInstance();
            $db->execute(
                "INSERT INTO activity_logs (user_id, action, module, record_id, details, ip_address)
                 VALUES (?, ?, ?, ?, ?, ?)",
                [
                    Session::get('user.id') ?? Session::user()['id'] ?? null,
                    $action,
                    $module,
                    $recordId,
                    $details,
                    $_SERVER['REMOTE_ADDR'] ?? null,
                ]
            );
        } catch (\Throwable $e) {
            error_log('[ActivityLog] ' . $e->getMessage());
        }
    }
}

if (!function_exists('redirect')) {
    function redirect(string $path): never {
        header('Location: ' . url($path));
        exit;
    }
}

if (!function_exists('isActive')) {
    /** Returns 'active' class if current URI matches given path prefix */
    function isActive(string $prefix): string {
        $uri = '/' . ltrim(strtok($_SERVER['REQUEST_URI'] ?? '/', '?'), '/');
        return str_starts_with($uri, '/' . ltrim($prefix, '/')) ? 'active' : '';
    }
}
