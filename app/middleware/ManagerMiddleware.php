<?php
namespace App\Middleware;

use Core\Session;

class ManagerMiddleware {
    public function handle(): void {
        if (!Session::isLoggedIn()) {
            header('Location: ' . url('/login'));
            exit;
        }
        if (!Session::can(['admin', 'manager'])) {
            Session::flash('error', 'Access denied. Manager privileges required.');
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
