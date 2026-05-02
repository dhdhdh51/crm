<?php
namespace App\Models;
use Core\Model;

class SiteVisit extends Model {
    protected string $table = 'site_visits';

    public function allWithDetails(): array {
        return $this->db->fetchAll(
            "SELECT sv.*, l.name AS lead_name, l.phone AS lead_phone,
                    p.name AS project_name, u.name AS assigned_name
             FROM site_visits sv
             JOIN leads l ON l.id = sv.lead_id
             LEFT JOIN projects p ON p.id = sv.project_id
             LEFT JOIN users u ON u.id = sv.assigned_to
             ORDER BY sv.visit_date DESC"
        );
    }

    public function findWithDetails(int $id): array|false {
        return $this->db->fetch(
            "SELECT sv.*, l.name AS lead_name, l.phone AS lead_phone,
                    p.name AS project_name, u.name AS assigned_name
             FROM site_visits sv
             JOIN leads l ON l.id = sv.lead_id
             LEFT JOIN projects p ON p.id = sv.project_id
             LEFT JOIN users u ON u.id = sv.assigned_to
             WHERE sv.id = ? LIMIT 1",
            [$id]
        );
    }

    public function countThisMonth(): int {
        return (int)$this->db->fetchColumn(
            "SELECT COUNT(*) FROM site_visits
             WHERE YEAR(visit_date) = YEAR(NOW()) AND MONTH(visit_date) = MONTH(NOW())"
        );
    }

    public function filterByEmployee(int $userId): array {
        return $this->db->fetchAll(
            "SELECT sv.*, l.name AS lead_name, p.name AS project_name
             FROM site_visits sv
             JOIN leads l ON l.id = sv.lead_id
             LEFT JOIN projects p ON p.id = sv.project_id
             WHERE sv.assigned_to = ?
             ORDER BY sv.visit_date DESC",
            [$userId]
        );
    }
}
