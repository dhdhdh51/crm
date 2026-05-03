<?php
namespace App\Models;

use Core\Model;

class Attendance extends Model {
    protected string $table = 'attendance';

    public function todayRecord(int $userId): array|false {
        return $this->db->fetch(
            "SELECT * FROM attendance WHERE user_id = ? AND date = CURDATE() LIMIT 1",
            [$userId]
        );
    }

    public function allWithUsers(array $filters = []): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['date'])) {
            $where[] = 'a.date = ?';
            $params[] = $filters['date'];
        }
        if (!empty($filters['month']) && !empty($filters['year'])) {
            // Use range instead of MONTH()/YEAR() so index on date is used
            $from = sprintf('%04d-%02d-01', $filters['year'], $filters['month']);
            $to   = date('Y-m-t', strtotime($from));
            $where[]  = 'a.date BETWEEN ? AND ?';
            $params[] = $from;
            $params[] = $to;
        }
        if (!empty($filters['user_id'])) {
            $where[] = 'a.user_id = ?';
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['status'])) {
            $where[] = 'a.status = ?';
            $params[] = $filters['status'];
        }

        $sql = "SELECT a.*, u.name AS employee_name, u.employee_id AS emp_code,
                       r.name AS role_name
                FROM attendance a
                JOIN users u ON u.id = a.user_id
                JOIN roles r ON r.id = u.role_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY a.date DESC, a.check_in DESC";

        return $this->db->fetchAll($sql, $params);
    }

    public function monthSummary(int $userId, int $month, int $year): array {
        return $this->db->fetch(
            "SELECT
                COUNT(*) AS total_days,
                SUM(status = 'present') AS present,
                SUM(status = 'absent')  AS absent,
                SUM(status = 'late')    AS late,
                SUM(status = 'half_day') AS half_day,
                SUM(status = 'holiday') AS holiday,
                SEC_TO_TIME(AVG(TIME_TO_SEC(check_in))) AS avg_check_in
             FROM attendance
             WHERE user_id = ? AND MONTH(date) = ? AND YEAR(date) = ?",
            [$userId, $month, $year]
        ) ?: [];
    }

    public function teamSummaryForMonth(int $month, int $year): array {
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to   = date('Y-m-t', strtotime($from));
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.employee_id AS emp_code,
                    COUNT(a.id)                   AS total_days,
                    SUM(a.status = 'present')     AS present,
                    SUM(a.status = 'absent')      AS absent,
                    SUM(a.status = 'late')        AS late,
                    SUM(a.status = 'half_day')    AS half_day
             FROM users u
             LEFT JOIN attendance a ON a.user_id = u.id AND a.date BETWEEN ? AND ?
             WHERE u.is_active = 1
             GROUP BY u.id, u.name, u.employee_id
             ORDER BY u.name",
            [$from, $to]
        );
    }

    public function checkIn(int $userId, string $method = 'face', ?string $image = null, ?float $lat = null, ?float $lng = null, bool $geoValid = true): int|string {
        if ($this->todayRecord($userId)) return 'already_checked_in';
        $hour   = (int)date('H');
        $status = $hour >= 10 ? 'late' : 'present';
        return $this->insert([
            'user_id'       => $userId,
            'date'          => date('Y-m-d'),
            'check_in'      => date('H:i:s'),
            'status'        => $status,
            'method'        => $method,
            'checkin_image' => $image,
            'checkin_lat'   => $lat,
            'checkin_lng'   => $lng,
            'geo_valid'     => $geoValid ? 1 : 0,
            'marked_by'     => $userId,
        ]);
    }

    public function checkOut(int $userId): bool {
        $record = $this->todayRecord($userId);
        if (!$record || $record['check_out']) return false;
        $this->db->execute("UPDATE attendance SET check_out=? WHERE id=?", [date('H:i:s'), $record['id']]);
        return true;
    }

    public function checkOutWithMeta(int $userId, ?string $image, ?float $lat, ?float $lng): bool {
        $record = $this->todayRecord($userId);
        if (!$record || $record['check_out']) return false;
        $this->db->execute(
            "UPDATE attendance SET check_out=?,checkout_image=?,checkout_lat=?,checkout_lng=? WHERE id=?",
            [date('H:i:s'), $image, $lat, $lng, $record['id']]
        );
        return true;
    }

    public function markManual(int $userId, string $date, string $status, ?string $notes, int $markedBy): int|string {
        $existing = $this->db->fetch(
            "SELECT id FROM attendance WHERE user_id = ? AND date = ?",
            [$userId, $date]
        );
        if ($existing) {
            $this->db->execute(
                "UPDATE attendance SET status = ?, notes = ?, method = 'manual', marked_by = ? WHERE id = ?",
                [$status, $notes, $markedBy, $existing['id']]
            );
            return $existing['id'];
        }
        $row = [
            'user_id'   => $userId,
            'date'      => $date,
            'status'    => $status,
            'method'    => 'manual',
            'notes'     => $notes,
            'marked_by' => $markedBy,
        ];
        if ($date === date('Y-m-d')) {
            $row['check_in'] = date('H:i:s');
        }
        return $this->insert($row);
    }
}
