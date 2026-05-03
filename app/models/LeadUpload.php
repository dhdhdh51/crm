<?php
namespace App\Models;
use Core\Model;

class LeadUpload extends Model {
    protected string $table = 'lead_upload_logs';

    public function logs(): array {
        return $this->db->fetchAll(
            "SELECT l.*, u.name AS uploader FROM lead_upload_logs l
             LEFT JOIN users u ON u.id=l.uploaded_by ORDER BY l.created_at DESC LIMIT 50"
        );
    }

    public function importRows(array $rows, int $assignTo, int $uploadedBy): array {
        $imported = 0; $failed = 0; $errors = [];
        $db = $this->db;
        foreach ($rows as $i => $r) {
            $name  = trim($r[0] ?? '');
            $email = trim($r[1] ?? '');
            $phone = trim($r[2] ?? '');
            $city  = trim($r[3] ?? '');
            $proj  = trim($r[4] ?? '');
            if (!$name || !$phone) { $failed++; $errors[] = "Row ".($i+2).": name/phone required"; continue; }
            try {
                $projectId = null;
                if ($proj) {
                    $p = $db->fetch("SELECT id FROM projects WHERE name LIKE ? LIMIT 1", ["%$proj%"]);
                    $projectId = $p ? $p['id'] : null;
                }
                $db->execute(
                    "INSERT IGNORE INTO leads (name,email,phone,city,assigned_to,interested_project_id,source,status,created_by,created_at)
                     VALUES (?,?,?,?,?,?,'csv_import','new',?,NOW())",
                    [$name,$email,$phone,$city,$assignTo,$projectId,$uploadedBy]
                );
                $imported++;
            } catch (\Throwable $e) {
                $failed++; $errors[] = "Row ".($i+2).": ".$e->getMessage();
            }
        }
        $this->insert([
            'filename'    => 'csv_import',
            'total_rows'  => count($rows),
            'imported'    => $imported,
            'failed'      => $failed,
            'uploaded_by' => $uploadedBy,
        ]);
        return ['imported'=>$imported,'failed'=>$failed,'errors'=>$errors];
    }
}
