<?php
namespace App\Middleware;

use Core\Session;

class AdminMiddleware {
    public function handle(): void {
        if (!Session::isLoggedIn()) {
            header('Location: ' . url('/login'));
            exit;
        }
        if (!Session::can(['admin', 'super_admin'])) {
            Session::flash('error', 'Access denied. Admin privileges required.');
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
