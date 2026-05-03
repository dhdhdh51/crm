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

    public function calcFromAttendance(int $userId, int $month, int $year, float $baseSalary): array {
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', strtotime($from));
        $row  = $this->db->fetch(
            "SELECT SUM(status IN ('present','late')) AS days_present,
                    SUM(status = 'half_day') AS half_days,
                    SUM(status = 'absent')   AS absent_days,
                    COUNT(*) AS total_days
             FROM attendance
             WHERE user_id = ? AND date BETWEEN ? AND ?",
            [$userId, $from, $to]
        );
        $present  = (int)($row['days_present'] ?? 0);
        $half     = (int)($row['half_days'] ?? 0);
        $absent   = (int)($row['absent_days'] ?? 0);
        $workDays = max($present + $half + $absent, 1);
        $earned   = round($baseSalary * ($present + $half * 0.5) / $workDays, 2);
        $deduct   = round($baseSalary * $absent / $workDays, 2);
        return ['base_salary'=>$baseSalary,'earned'=>$earned,'deductions'=>$deduct,'present'=>$present,'half'=>$half,'absent'=>$absent];
    }

    public function totalPaidThisMonth(): float {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(net_salary), 0) FROM salaries
             WHERE year = YEAR(NOW()) AND month = MONTH(NOW()) AND payment_status = 'paid'"
        );
    }
}
