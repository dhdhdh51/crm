<?php
namespace App\Models;
use Core\Model;

class Notification extends Model {
    protected string $table = 'notifications';

    public function forUser(int $userId, int $limit = 20): array {
        return $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id = ?
             ORDER BY created_at DESC LIMIT {$limit}",
            [$userId]
        );
    }

    public function unreadCount(int $userId): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
    }

    public function markAllRead(int $userId): void {
        $this->db->execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
            [$userId]
        );
    }

    public static function create(int $userId, string $type, string $message, string $link = ''): void {
        $db = \Core\Database::getInstance();
        $db->execute(
            "INSERT INTO notifications (user_id, type, message, link) VALUES (?, ?, ?, ?)",
            [$userId, $type, $message, $link]
        );
    }
}
