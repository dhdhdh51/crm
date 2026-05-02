<?php
namespace App\Models;

use Core\Model;
use Core\Session;

class Lead extends Model {
    protected string $table = 'leads';

    /** Rich query with joins */
    private function baseSelect(): string {
        return "SELECT l.*,
                    p.name AS project_name,
                    u.name AS assigned_name, u.employee_id AS assigned_emp_id
                FROM leads l
                LEFT JOIN projects p ON p.id = l.interested_project_id
                LEFT JOIN users u ON u.id = l.assigned_to";
    }

    public function findWithDetails(int $id): array|false {
        $sql = $this->baseSelect() . " WHERE l.id = ? LIMIT 1";
        return $this->db->fetch($sql, [$id]);
    }

    /**
     * Paginated list with dynamic filters
     * Returns ['data', 'total', 'pages', 'currentPage', 'perPage']
     */
    public function filter(array $filters, int $page, int $perPage): array {
        [$where, $params] = $this->buildFilters($filters);

        $countSql = "SELECT COUNT(*) FROM leads l {$where}";
        $total    = (int)$this->db->fetchColumn($countSql, $params);

        $offset = ($page - 1) * $perPage;
        $dataSql = $this->baseSelect() . " {$where} ORDER BY l.created_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $data   = $this->db->fetchAll($dataSql, $params);

        return [
            'data'        => $data,
            'total'       => $total,
            'pages'       => max(1, (int)ceil($total / $perPage)),
            'currentPage' => $page,
            'perPage'     => $perPage,
        ];
    }

    private function buildFilters(array $f): array {
        $conditions = ['1=1'];
        $params     = [];

        // Sales executives can only see their own leads
        if (Session::role() === 'sales_executive') {
            $conditions[] = 'l.assigned_to = ?';
            $params[]     = Session::user()['id'];
        }

        if (!empty($f['search'])) {
            $conditions[] = '(l.name LIKE ? OR l.phone LIKE ? OR l.email LIKE ?)';
            $s = '%' . $f['search'] . '%';
            array_push($params, $s, $s, $s);
        }

        if (!empty($f['status'])) {
            $conditions[] = 'l.status = ?';
            $params[]     = $f['status'];
        }

        if (!empty($f['source'])) {
            $conditions[] = 'l.source = ?';
            $params[]     = $f['source'];
        }

        if (!empty($f['assigned_to'])) {
            $conditions[] = 'l.assigned_to = ?';
            $params[]     = (int)$f['assigned_to'];
        }

        if (!empty($f['project_id'])) {
            $conditions[] = 'l.interested_project_id = ?';
            $params[]     = (int)$f['project_id'];
        }

        if (!empty($f['from_date'])) {
            $conditions[] = 'DATE(l.created_at) >= ?';
            $params[]     = $f['from_date'];
        }

        if (!empty($f['to_date'])) {
            $conditions[] = 'DATE(l.created_at) <= ?';
            $params[]     = $f['to_date'];
        }

        $where = 'WHERE ' . implode(' AND ', $conditions);
        return [$where, $params];
    }

    public function getFollowups(int $leadId): array {
        return $this->db->fetchAll(
            "SELECT f.*, u.name AS done_by
             FROM followups f
             LEFT JOIN users u ON u.id = f.user_id
             WHERE f.lead_id = ?
             ORDER BY f.followup_date DESC",
            [$leadId]
        );
    }

    public function pendingFollowups(?int $userId = null): array {
        $params = [date('Y-m-d')];
        $extra  = '';
        if ($userId) { $extra = ' AND l.assigned_to = ?'; $params[] = $userId; }

        return $this->db->fetchAll(
            "SELECT l.id, l.name, l.phone, l.status, l.next_followup_date,
                    u.name AS assigned_name
             FROM leads l
             LEFT JOIN users u ON u.id = l.assigned_to
             WHERE l.next_followup_date <= ? AND l.status NOT IN ('closed','lost'){$extra}
             ORDER BY l.next_followup_date ASC
             LIMIT 20",
            $params
        );
    }

    public function stats(): array {
        return $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'new')    AS new_count,
                SUM(status = 'hot')    AS hot_count,
                SUM(status = 'warm')   AS warm_count,
                SUM(status = 'cold')   AS cold_count,
                SUM(status = 'closed') AS closed_count,
                SUM(status = 'lost')   AS lost_count,
                SUM(DATE(created_at) = CURDATE()) AS today,
                SUM(YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())) AS this_month
             FROM leads"
        );
    }

    public function monthlyTrend(int $months = 6): array {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%b %Y') AS month_label,
                    YEAR(created_at)  AS yr,
                    MONTH(created_at) AS mo,
                    COUNT(*)          AS total,
                    SUM(status = 'closed') AS closed
             FROM leads
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY yr, mo
             ORDER BY yr, mo",
            [$months]
        );
    }

    public function sourceBreakdown(): array {
        return $this->db->fetchAll(
            "SELECT source, COUNT(*) AS cnt
             FROM leads
             GROUP BY source
             ORDER BY cnt DESC"
        );
    }

    public function conversionRate(): float {
        $total  = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM leads");
        $closed = (int)$this->db->fetchColumn("SELECT COUNT(*) FROM leads WHERE status = 'closed'");
        return $total > 0 ? round(($closed / $total) * 100, 1) : 0.0;
    }
}
