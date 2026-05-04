<?php
namespace App\Models;
use Core\Model;

class Attendance extends Model {
    protected string $table = 'attendance';

    public function todayRecord(int $userId): array|false {
        return $this->db->fetch(
            "SELECT * FROM attendance WHERE user_id=? AND date=CURDATE() LIMIT 1",
            [$userId]
        );
    }

    public function checkIn(int $userId, array $data): int {
        return $this->insert([
            'user_id'     => $userId,
            'date'        => date('Y-m-d'),
            'check_in'    => date('H:i:s'),
            'checkin_lat' => $data['lat'] ?? null,
            'checkin_lng' => $data['lng'] ?? null,
            'geo_valid'   => $data['geo_valid'] ?? 1,
            'image'       => $data['image'] ?? null,
            'method'      => $data['method'] ?? 'manual',
            'marked_by'   => $userId,
        ]);
    }

    public function checkOut(int $userId, array $data = []): bool {
        $r = $this->todayRecord($userId);
        if (!$r || $r['check_out']) return false;
        $this->db->execute(
            "UPDATE attendance SET check_out=?, checkout_lat=?, checkout_lng=? WHERE id=?",
            [date('H:i:s'), $data['lat'] ?? null, $data['lng'] ?? null, $r['id']]
        );
        return true;
    }

    public function allWithUsers(array $f = []): array {
        $where = ['1=1']; $p = [];
        if (!empty($f['user_id'])) { $where[] = 'a.user_id=?'; $p[] = $f['user_id']; }
        if (!empty($f['date']))    { $where[] = 'a.date=?';    $p[] = $f['date']; }
        if (!empty($f['month']) && !empty($f['year'])) {
            $from = sprintf('%04d-%02d-01', $f['year'], $f['month']);
            $where[] = 'a.date BETWEEN ? AND ?';
            $p[] = $from; $p[] = date('Y-m-t', strtotime($from));
        }
        return $this->db->fetchAll(
            "SELECT a.*, u.name AS emp_name, u.employee_id AS emp_code
             FROM attendance a JOIN users u ON u.id=a.user_id
             WHERE " . implode(' AND ', $where) . " ORDER BY a.date DESC, a.check_in DESC",
            $p
        );
    }

    public static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1); $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
    }

    public function saveImage(string $base64, int $userId): ?string {
        $data = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
        $decoded = base64_decode($data, true);
        if (!$decoded) return null;
        $dir = ROOT . '/storage/uploads/attendance/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = $userId . '_' . date('Ymd_His') . '.jpg';
        file_put_contents($dir . $fname, $decoded);
        return 'attendance/' . $fname;
    }
}
