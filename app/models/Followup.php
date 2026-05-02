<?php
namespace App\Models;
use Core\Model;

class Followup extends Model {
    protected string $table = 'followups';

    public function forLead(int $leadId): array {
        return $this->db->fetchAll(
            "SELECT f.*, u.name AS done_by
             FROM followups f
             LEFT JOIN users u ON u.id = f.user_id
             WHERE f.lead_id = ?
             ORDER BY f.followup_date DESC",
            [$leadId]
        );
    }
}
