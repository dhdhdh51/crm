<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Notification;

class NotificationController extends Controller {

    public function index(): void {
        $userId = Session::user()['id'];
        $model  = new Notification();
        $notifs = $model->forUser($userId, 50);
        $model->markAllRead($userId);
        $this->view('notifications.index', ['title' => 'Notifications', 'notifs' => $notifs]);
    }

    public function markAllRead(): void {
        $this->verifyCsrf();
        (new Notification())->markAllRead(Session::user()['id']);
        Session::flash('success', 'All notifications marked as read.');
        $this->redirect('notifications');
    }

    public function unreadCount(): void {
        $count = (new Notification())->unreadCount(Session::user()['id']);
        $this->json(['count' => $count]);
    }
}
