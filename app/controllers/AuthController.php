<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use Core\CSRF;
use App\Models\User;

class AuthController extends Controller {

    public function showLogin(): void {
        if (Session::isLoggedIn()) {
            $this->redirect('dashboard');
        }
        $this->view('auth.login', ['title' => 'Login'], 'auth');
    }

    public function login(): void {
        $this->verifyCsrf();

        $identifier = trim($_POST['employee_id'] ?? '');
        $password   = $_POST['password'] ?? '';

        if (!$identifier || !$password) {
            Session::flash('error', 'Employee ID / Email and password are required.');
            $this->redirect('login');
        }

        $userModel = new User();
        $user      = $userModel->findByEmailOrEmployeeId($identifier);

        if (!$user || !password_verify($password, $user['password'])) {
            Session::flash('error', 'Invalid credentials. Please try again.');
            $this->redirect('login');
        }

        // Store in session (exclude password)
        unset($user['password']);
        Session::set('user', $user);
        Session::regenerate();

        $userModel->updateLastLogin($user['id']);
        logActivity('login', 'auth', $user['id'], 'User logged in');

        Session::flash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirect('dashboard');
    }

    public function logout(): void {
        logActivity('logout', 'auth', Session::user()['id'] ?? null, 'User logged out');
        Session::destroy();
        header('Location: ' . url('/login'));
        exit;
    }

    public function profile(): void {
        $user = (new User())->findWithRole(Session::user()['id']);
        $this->view('auth.profile', ['title' => 'My Profile', 'user' => $user]);
    }

    public function updateProfile(): void {
        $this->verifyCsrf();
        $userId = Session::user()['id'];

        $data = [
            'name'  => $this->sanitize($_POST['name'] ?? ''),
            'email' => $this->sanitize($_POST['email'] ?? ''),
            'phone' => $this->sanitize($_POST['phone'] ?? ''),
        ];

        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 6) {
                Session::flash('error', 'Password must be at least 6 characters.');
                $this->redirect('profile');
            }
            $data['password'] = password_hash($_POST['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
        }

        (new User())->update($userId, $data);

        // Refresh session user data
        $freshUser = (new User())->findWithRole($userId);
        unset($freshUser['password']);
        Session::set('user', $freshUser);

        Session::flash('success', 'Profile updated successfully.');
        $this->redirect('profile');
    }
}
