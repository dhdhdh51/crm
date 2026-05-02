<?php
namespace App\Models;
use Core\Model;

class Salary extends Model {
    protected string $table = 'salaries';

    public function allWithUser(): array {
        return $this->db->fetchAll(
            "SELECT s.*, u.name, u.employee_id, r.name AS role_name
             FROM salaries s
             JOIN users u ON u.id = s.user_id
             JOIN roles r ON r.id = u.role_id
             ORDER BY s.year DESC, s.month DESC, u.name"
        );
    }

    public function findSlip(int $id): array|false {
        return $this->db->fetch(
            "SELECT s.*, u.name, u.employee_id, u.email, u.phone,
                    r.name AS role_name, u.designation
             FROM salaries s
             JOIN users u ON u.id = s.user_id
             JOIN roles r ON r.id = u.role_id
             WHERE s.id = ? LIMIT 1",
            [$id]
        );
    }

    public function existsForMonth(int $userId, int $month, int $year): bool {
        return (bool)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM salaries WHERE user_id = ? AND month = ? AND year = ?",
            [$userId, $month, $year]
        );
    }

    public function totalPaidThisMonth(): float {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(net_salary), 0) FROM salaries
             WHERE year = YEAR(NOW()) AND month = MONTH(NOW()) AND payment_status = 'paid'"
        );
    }
}
