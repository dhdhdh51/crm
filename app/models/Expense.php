<?php
namespace App\Models;
use Core\Model;

class Expense extends Model {
    protected string $table = 'expenses';

    public function all(array $f = []): array {
        $where = '1=1';
        $params = [];
        if (!empty($f['category'])) { $where .= ' AND e.category=?'; $params[] = $f['category']; }
        if (!empty($f['from']))     { $where .= ' AND e.expense_date>=?'; $params[] = $f['from']; }
        if (!empty($f['to']))       { $where .= ' AND e.expense_date<=?'; $params[] = $f['to']; }
        return $this->db->fetchAll(
            "SELECT e.*, u.name AS added_by_name FROM expenses e
             LEFT JOIN users u ON u.id=e.added_by
             WHERE $where ORDER BY e.expense_date DESC",
            $params
        );
    }

    public function totalByCategory(): array {
        return $this->db->fetchAll(
            "SELECT category, SUM(amount) AS total FROM expenses GROUP BY category ORDER BY total DESC"
        );
    }

    public function monthTotal(int $month, int $year): float {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount),0) FROM expenses
             WHERE MONTH(expense_date)=? AND YEAR(expense_date)=?",
            [$month, $year]
        );
    }
}
