<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Attendance;
use App\Models\User;

class AttendanceController extends Controller {

    private Attendance $model;

    public function __construct() {
        parent::__construct();
        $this->model = new Attendance();
    }

    /** Attendance list */
    public function index(): void {
        $filters = [
            'date'    => $_GET['date'] ?? '',
            'month'   => (int)($_GET['month'] ?? date('m')),
            'year'    => (int)($_GET['year'] ?? date('Y')),
            'user_id' => Session::can(['admin', 'manager']) ? (int)($_GET['user_id'] ?? 0) : Session::user()['id'],
            'status'  => $_GET['status'] ?? '',
        ];
        if (!empty($filters['date'])) {
            unset($filters['month'], $filters['year']);
        }

        $records   = $this->model->allWithUsers($filters);
        $employees = Session::can(['admin', 'manager']) ? (new User())->allWithRole() : [];

        $this->view('attendance.index', [
            'title'     => 'Attendance',
            'records'   => $records,
            'employees' => $employees,
            'filters'   => $filters,
        ]);
    }

    /** Face recognition check-in page */
    public function checkin(): void {
        $userId  = Session::user()['id'];
        $today   = $this->model->todayRecord($userId);
        $this->view('attendance.checkin', [
            'title'  => 'Face Recognition Check-In',
            'today'  => $today,
        ]);
    }

    /** API: Mark attendance via face match (called from JS) */
    public function markByFace(): void {
        $this->verifyCsrf();
        $userId = Session::user()['id'];

        $action = $_POST['action'] ?? 'checkin';

        if ($action === 'checkout') {
            $done = $this->model->checkOut($userId);
            $this->json(['success' => $done, 'message' => $done ? 'Checked out successfully.' : 'No active check-in found.']);
        }

        $result = $this->model->checkIn($userId, 'face');
        if ($result === 'already_checked_in') {
            $this->json(['success' => false, 'message' => 'Already checked in today.']);
        }

        logActivity('checkin', 'attendance', (int)$result, 'Face recognition check-in');
        $this->json(['success' => true, 'message' => 'Check-in recorded successfully.']);
    }

    /** Enroll face — show page */
    public function enroll(string $id = ''): void {
        $targetId = $id ? (int)$id : Session::user()['id'];

        // Only admin/manager can enroll others
        if ($targetId !== Session::user()['id'] && !Session::can(['admin', 'manager'])) {
            $this->abort(403);
        }

        $user = (new User())->findWithRole($targetId);
        if (!$user) $this->abort(404);

        $this->view('attendance.enroll', [
            'title'    => 'Face Enrollment — ' . $user['name'],
            'employee' => $user,
        ]);
    }

    /** API: Save face descriptor */
    public function saveDescriptor(): void {
        header('Content-Type: application/json');
        $this->verifyCsrf();

        $targetId   = (int)($_POST['user_id'] ?? Session::user()['id']);
        $descriptor = $_POST['descriptor'] ?? '';

        if ($targetId !== Session::user()['id'] && !Session::can(['admin', 'manager'])) {
            $this->json(['success' => false, 'message' => 'Forbidden.'], 403);
        }

        // Validate JSON descriptor
        $decoded = json_decode($descriptor, true);
        if (!is_array($decoded) || count($decoded) !== 128) {
            $this->json(['success' => false, 'message' => 'Invalid face descriptor.']);
        }

        $this->db->execute(
            "UPDATE users SET face_descriptor = ? WHERE id = ?",
            [$descriptor, $targetId]
        );

        logActivity('enroll_face', 'attendance', $targetId, 'Face descriptor enrolled');
        $this->json(['success' => true, 'message' => 'Face enrolled successfully.']);
    }

    /** API: Get all face descriptors for client-side matching */
    public function descriptors(): void {
        // Returns all enrolled user descriptors (id + descriptor) for face matching
        $rows = $this->db->fetchAll(
            "SELECT id, name, employee_id, face_descriptor
             FROM users WHERE is_active = 1 AND face_descriptor IS NOT NULL"
        );

        $data = array_map(function ($r) {
            return [
                'id'          => $r['id'],
                'name'        => $r['name'],
                'employee_id' => $r['employee_id'],
                'descriptor'  => json_decode($r['face_descriptor'], true),
            ];
        }, $rows);

        $this->json($data);
    }

    /** Monthly attendance report */
    public function report(): void {
        if (!Session::can(['admin', 'manager'])) $this->abort(403);

        $month = (int)($_GET['month'] ?? date('m'));
        $year  = (int)($_GET['year']  ?? date('Y'));

        $summary = $this->model->teamSummaryForMonth($month, $year);

        $this->view('attendance.report', [
            'title'   => 'Attendance Report',
            'summary' => $summary,
            'month'   => $month,
            'year'    => $year,
        ]);
    }

    /** Manual attendance mark (admin/manager) */
    public function manualMark(): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);

        $userId  = (int)($_POST['user_id'] ?? 0);
        $date    = $_POST['date'] ?? date('Y-m-d');
        $status  = $this->sanitize($_POST['status'] ?? 'present');
        $notes   = $this->sanitize($_POST['notes'] ?? '');

        $this->model->markManual($userId, $date, $status, $notes, Session::user()['id']);
        logActivity('manual_mark', 'attendance', $userId, "Manual attendance: {$status} on {$date}");

        Session::flash('success', 'Attendance marked manually.');
        $this->redirect('attendance');
    }
}
