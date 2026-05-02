<?php
namespace App\Models;

use Core\Model;

class Project extends Model {
    protected string $table = 'projects';

    public function allActive(): array {
        return $this->findAll("status != 'archived'", [], 'name ASC');
    }

    public function withUnitStats(): array {
        return $this->db->fetchAll(
            "SELECT p.*,
                    COUNT(u.id)                         AS unit_count,
                    SUM(u.status = 'available')         AS available,
                    SUM(u.status = 'booked')            AS booked,
                    SUM(u.status = 'sold')              AS sold
             FROM projects p
             LEFT JOIN units u ON u.project_id = p.id
             GROUP BY p.id
             ORDER BY p.created_at DESC"
        );
    }

    public function getUnits(int $projectId): array {
        return $this->db->fetchAll(
            "SELECT * FROM units WHERE project_id = ? ORDER BY floor, unit_number",
            [$projectId]
        );
    }

    public function revenueByProject(): array {
        return $this->db->fetchAll(
            "SELECT p.name, COUNT(b.id) AS bookings, SUM(b.total_amount) AS revenue
             FROM projects p
             LEFT JOIN units u ON u.project_id = p.id
             LEFT JOIN bookings b ON b.unit_id = u.id
             GROUP BY p.id
             ORDER BY revenue DESC"
        );
    }
}
