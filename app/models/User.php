<?php
namespace App\Models;

use Core\Model;

class User extends Model {
    protected string $table = 'users';

    public function findByEmployeeId(string $empId): array|false {
        return $this->db->fetch(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug, r.permissions
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.employee_id = ? AND u.is_active = 1 LIMIT 1",
            [$empId]
        );
    }

    public function findWithRole(int $id): array|false {
        return $this->db->fetch(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.id = ? LIMIT 1",
            [$id]
        );
    }

    public function allWithRole(): array {
        return $this->db->fetchAll(
            "SELECT u.*, r.name AS role_name, r.slug AS role_slug
             FROM users u
             JOIN roles r ON r.id = u.role_id
             ORDER BY u.name"
        );
    }

    public function salesExecutives(): array {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, u.employee_id
             FROM users u
             JOIN roles r ON r.id = u.role_id
             WHERE u.is_active = 1 AND r.slug IN ('admin','manager','sales_executive')
             ORDER BY u.name"
        );
    }

    public function updateLastLogin(int $id): void {
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$id]
        );
    }

    public function leadCountByEmployee(): array {
        return $this->db->fetchAll(
            "SELECT u.id, u.name, COUNT(l.id) AS lead_count,
                    SUM(l.status = 'closed') AS closed_count
             FROM users u
             LEFT JOIN leads l ON l.assigned_to = u.id
             GROUP BY u.id ORDER BY closed_count DESC"
        );
    }
}
