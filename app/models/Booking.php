<?php
namespace App\Models;
use Core\Model;

class Booking extends Model {
    protected string $table = 'bookings';

    public function totalRevenue(): float {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(total_amount), 0) FROM bookings"
        );
    }

    public function thisMonthRevenue(): float {
        return (float)$this->db->fetchColumn(
            "SELECT COALESCE(SUM(total_amount), 0) FROM bookings
             WHERE YEAR(booking_date) = YEAR(NOW()) AND MONTH(booking_date) = MONTH(NOW())"
        );
    }

    public function monthlySales(int $months = 6): array {
        return $this->db->fetchAll(
            "SELECT DATE_FORMAT(booking_date, '%b %Y') AS month_label,
                    COUNT(*) AS count, SUM(total_amount) AS revenue
             FROM bookings
             WHERE booking_date >= DATE_SUB(NOW(), INTERVAL ? MONTH)
             GROUP BY YEAR(booking_date), MONTH(booking_date)
             ORDER BY YEAR(booking_date), MONTH(booking_date)",
            [$months]
        );
    }
}
