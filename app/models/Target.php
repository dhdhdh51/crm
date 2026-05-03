<?php
namespace App\Models;
use Core\Model;

class Target extends Model {
    protected string $table = 'targets';

    public function forUser(int $userId, int $month, int $year): array|false {
        return $this->db->fetch(
            "SELECT * FROM targets WHERE user_id=? AND month=? AND year=?",
            [$userId, $month, $year]
        );
    }

    public function allForMonth(int $month, int $year): array {
        return $this->db->fetchAll(
            "SELECT t.*, u.name, u.employee_id,
              (SELECT COUNT(*) FROM leads l WHERE l.assigned_to=t.user_id
               AND MONTH(l.created_at)=t.month AND YEAR(l.created_at)=t.year) AS actual_leads,
              (SELECT COUNT(*) FROM site_visits sv WHERE sv.assigned_to=t.user_id
               AND MONTH(sv.visit_date)=t.month AND YEAR(sv.visit_date)=t.year) AS actual_visits
             FROM targets t JOIN users u ON u.id=t.user_id
             WHERE t.month=? AND t.year=? ORDER BY u.name",
            [$month, $year]
        );
    }

    public function upsert(array $data): void {
        $exists = $this->forUser($data['user_id'], $data['month'], $data['year']);
        if ($exists) {
            $this->db->execute(
                "UPDATE targets SET target_leads=?,target_visits=?,target_sales=?,target_revenue=? WHERE id=?",
                [$data['target_leads'],$data['target_visits'],$data['target_sales'],$data['target_revenue'],$exists['id']]
            );
        } else {
            $this->insert($data);
        }
    }
}
