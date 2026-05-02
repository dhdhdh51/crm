<?php
namespace App\Controllers;

use Core\Controller;
use App\Models\Lead;
use App\Models\Booking;
use App\Models\User;
use App\Models\Project;

class ReportController extends Controller {

    public function index(): void {
        $leadModel  = new Lead();
        $bookModel  = new Booking();
        $this->view('reports.index', [
            'title'       => 'Reports',
            'leadStats'   => $leadModel->stats(),
            'convRate'    => $leadModel->conversionRate(),
            'totalRevenue'=> $bookModel->totalRevenue(),
            'monthlySales'=> json_encode($bookModel->monthlySales(6)),
        ]);
    }

    public function leads(): void {
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate   = $_GET['to_date']   ?? date('Y-m-d');

        $data = $this->db->fetchAll(
            "SELECT l.*, u.name AS assigned_name, p.name AS project_name
             FROM leads l
             LEFT JOIN users u ON u.id = l.assigned_to
             LEFT JOIN projects p ON p.id = l.interested_project_id
             WHERE DATE(l.created_at) BETWEEN ? AND ?
             ORDER BY l.created_at DESC",
            [$fromDate, $toDate]
        );

        $summary = $this->db->fetch(
            "SELECT COUNT(*) AS total,
                    SUM(status='hot') AS hot,
                    SUM(status='warm') AS warm,
                    SUM(status='closed') AS closed,
                    SUM(status='lost') AS lost
             FROM leads WHERE DATE(created_at) BETWEEN ? AND ?",
            [$fromDate, $toDate]
        );

        $sourceData = $this->db->fetchAll(
            "SELECT source, COUNT(*) AS cnt
             FROM leads WHERE DATE(created_at) BETWEEN ? AND ?
             GROUP BY source ORDER BY cnt DESC",
            [$fromDate, $toDate]
        );

        $this->view('reports.leads', [
            'title'      => 'Lead Report',
            'data'       => $data,
            'summary'    => $summary,
            'sourceData' => $sourceData,
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
        ]);
    }

    public function sales(): void {
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate   = $_GET['to_date']   ?? date('Y-m-d');

        $bookings = $this->db->fetchAll(
            "SELECT b.*, l.name AS lead_name, l.phone AS lead_phone,
                    u2.unit_number, p.name AS project_name,
                    uh.name AS handled_by_name
             FROM bookings b
             JOIN leads l ON l.id = b.lead_id
             JOIN units u2 ON u2.id = b.unit_id
             JOIN projects p ON p.id = u2.project_id
             LEFT JOIN users uh ON uh.id = b.handled_by
             WHERE DATE(b.booking_date) BETWEEN ? AND ?
             ORDER BY b.booking_date DESC",
            [$fromDate, $toDate]
        );

        $totalRevenue = array_sum(array_column($bookings, 'total_amount'));

        $this->view('reports.sales', [
            'title'        => 'Sales Report',
            'bookings'     => $bookings,
            'totalRevenue' => $totalRevenue,
            'fromDate'     => $fromDate,
            'toDate'       => $toDate,
        ]);
    }

    public function performance(): void {
        $fromDate = $_GET['from_date'] ?? date('Y-m-01');
        $toDate   = $_GET['to_date']   ?? date('Y-m-d');

        $perf = $this->db->fetchAll(
            "SELECT u.id, u.name, u.employee_id, r.name AS role_name,
                    COUNT(DISTINCT l.id)                          AS total_leads,
                    SUM(l.status = 'closed')                      AS closed_leads,
                    COUNT(DISTINCT sv.id)                         AS site_visits,
                    SUM(l.closed_value)                           AS revenue,
                    COUNT(DISTINCT f.id)                          AS followups
             FROM users u
             JOIN roles r ON r.id = u.role_id
             LEFT JOIN leads l   ON l.assigned_to = u.id AND DATE(l.created_at) BETWEEN ? AND ?
             LEFT JOIN site_visits sv ON sv.assigned_to = u.id AND DATE(sv.visit_date) BETWEEN ? AND ?
             LEFT JOIN followups f ON f.user_id = u.id AND DATE(f.followup_date) BETWEEN ? AND ?
             WHERE u.is_active = 1
             GROUP BY u.id
             ORDER BY revenue DESC",
            [$fromDate, $toDate, $fromDate, $toDate, $fromDate, $toDate]
        );

        $this->view('reports.performance', [
            'title'    => 'Performance Report',
            'perf'     => $perf,
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
        ]);
    }
}
