<?php
namespace App\Middleware;

use Core\Session;

class AdminMiddleware {
    public function handle(): void {
        if (!Session::isLoggedIn()) {
            header('Location: ' . url('/login'));
            exit;
        }
        if (!Session::can(['admin', 'super_admin', 'hr'])) {
            Session::flash('error', 'Access denied.');
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
