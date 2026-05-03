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
        $uid = Session::user()['id'];
        $filters = [
            'date'    => $_GET['date'] ?? '',
            'month'   => (int)($_GET['month'] ?? date('m')),
            'year'    => (int)($_GET['year'] ?? date('Y')),
            'user_id' => Session::can(['admin', 'manager', 'super_admin', 'hr']) ? (int)($_GET['user_id'] ?? 0) : $uid,
            'status'  => $_GET['status'] ?? '',
        ];
        if (!empty($filters['date'])) {
            unset($filters['month'], $filters['year']);
        }

        $records   = $this->model->allWithUsers($filters);
        $employees = Session::can(['admin', 'manager', 'super_admin', 'hr']) ? (new User())->allWithRole() : [];

        $this->view('attendance.index', [
            'title'       => 'Attendance',
            'records'     => $records,
            'employees'   => $employees,
            'filters'     => $filters,
            'todayRecord' => $this->model->todayRecord($uid),
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
        $lat    = isset($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng    = isset($_POST['lng']) ? (float)$_POST['lng'] : null;

        // Save webcam snapshot
        $image = null;
        if (!empty($_POST['image_data'])) {
            $image = $this->saveSnapshot($_POST['image_data'], $userId, $action);
        }

        // Geo-fence validation
        $geoValid = $this->validateGeo($userId, $lat, $lng);

        if ($action === 'checkout') {
            $done = $this->model->checkOutWithMeta($userId, $image, $lat, $lng);
            $msg  = $done ? 'Checked out successfully.' : 'No active check-in found.';
            $this->json(['success' => $done, 'message' => $msg, '_csrf' => \Core\CSRF::token()]);
        }

        $result = $this->model->checkIn($userId, 'face', $image, $lat, $lng, $geoValid);
        if ($result === 'already_checked_in') {
            $this->json(['success' => false, 'message' => 'Already checked in today.', '_csrf' => \Core\CSRF::token()]);
        }
        $geoMsg = $geoValid ? '' : ' (outside office zone — flagged)';
        logActivity('checkin', 'attendance', (int)$result, 'Face+location check-in');
        $this->json(['success' => true, 'message' => 'Check-in recorded.' . $geoMsg, 'geo_valid' => $geoValid, '_csrf' => \Core\CSRF::token()]);
    }

    private function saveSnapshot(string $dataUri, int $userId, string $type): ?string {
        $data = preg_replace('/^data:image\/\w+;base64,/', '', $dataUri);
        $decoded = base64_decode($data);
        if (!$decoded) return null;
        $dir = ROOT.'/storage/uploads/attendance/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        $fname = "{$type}_{$userId}_".date('Ymd_His').'.jpg';
        file_put_contents($dir.$fname, $decoded);
        return 'attendance/'.$fname;
    }

    private function validateGeo(int $userId, ?float $lat, ?float $lng): bool {
        if ($lat === null || $lng === null) return true;

        $office = (new \App\Models\Setting())->getOffice();
        if (!$office['lat'] || !$office['lng']) return true; // no office set — skip check

        return $this->haversine((float)$office['lat'], (float)$office['lng'], $lat, $lng) <= $office['radius'];
    }

    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $R = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLng/2)**2;
        return $R * 2 * atan2(sqrt($a), sqrt(1-$a));
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

    /** Manual self-checkout (POST from attendance index) */
    public function checkoutManual(): void {
        $this->verifyCsrf();
        $userId = (int)($_POST['user_id'] ?? Session::user()['id']);
        if ($userId !== Session::user()['id'] && !Session::can(['admin', 'manager', 'super_admin', 'hr'])) {
            $this->abort(403);
        }
        $done = $this->model->checkOut($userId);
        Session::flash($done ? 'success' : 'error', $done ? 'Checked out successfully.' : 'No active check-in found.');
        $this->redirect('attendance');
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

    /** Manual attendance mark (admin/manager or self) */
    public function manualMark(): void {
        $this->verifyCsrf();
        $userId  = (int)($_POST['user_id'] ?? 0);
        $selfMark = $userId === Session::user()['id'];
        if (!$selfMark && !Session::can(['admin', 'manager', 'super_admin', 'hr'])) {
            $this->abort(403);
        }
        $date    = $_POST['date'] ?? date('Y-m-d');
        $status  = $this->sanitize($_POST['status'] ?? 'present');
        $notes   = $this->sanitize($_POST['notes'] ?? '');

        $this->model->markManual($userId, $date, $status, $notes, Session::user()['id']);
        logActivity('manual_mark', 'attendance', $userId, "Manual attendance: {$status} on {$date}");

        Session::flash('success', 'Attendance marked manually.');
        $this->redirect('attendance');
    }
}
