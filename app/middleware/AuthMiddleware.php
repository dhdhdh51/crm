<?php
namespace App\Middleware;

use Core\Session;

class AuthMiddleware {
    public function handle(): void {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Please log in to continue.');
            header('Location: ' . url('/login'));
            exit;
        }
    }
}
