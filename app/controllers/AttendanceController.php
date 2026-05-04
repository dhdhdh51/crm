<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Attendance;
use App\Models\OfficeSetting;
use App\Models\User;

class AttendanceController extends Controller {

    private Attendance $model;

    public function __construct() {
        parent::__construct();
        $this->model = new Attendance();
    }

    public function index(): void {
        $uid = Session::user()['id'];
        $isAdmin = Session::can(['admin','super_admin','hr','manager']);
        $f = [
            'user_id' => $isAdmin ? (int)($_GET['user_id'] ?? 0) : $uid,
            'month'   => (int)($_GET['month'] ?? date('m')),
            'year'    => (int)($_GET['year']  ?? date('Y')),
            'date'    => $_GET['date'] ?? '',
        ];
        if ($f['date']) unset($f['month'], $f['year']);

        $this->view('attendance.index', [
            'title'       => 'Attendance',
            'records'     => $this->model->allWithUsers($f),
            'filters'     => $f,
            'today'       => $this->model->todayRecord($uid),
            'employees'   => $isAdmin ? (new User())->allWithRole() : [],
            'isAdmin'     => $isAdmin,
        ]);
    }

    public function checkin(): void {
        $uid = Session::user()['id'];
        // check if employee attendance is enabled
        $active = $this->db->fetchColumn("SELECT is_active FROM users WHERE id=?", [$uid]);
        if (!$active) { Session::flash('error','Your account is inactive.'); $this->redirect('attendance'); }
        $this->view('attendance.checkin', [
            'title'  => 'Check In / Out',
            'today'  => $this->model->todayRecord($uid),
            'office' => (new OfficeSetting())->get(),
        ]);
    }

    public function mark(): void {
        $this->verifyCsrf();
        $uid    = Session::user()['id'];
        $action = $_POST['action'] ?? 'checkin';
        $lat    = isset($_POST['lat']) && $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
        $lng    = isset($_POST['lng']) && $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;

        // Geo validation
        $geoValid = true;
        if ($lat !== null && $lng !== null) {
            $office = (new OfficeSetting())->get();
            if ($office['lat'] && $office['lng']) {
                $dist = Attendance::haversine((float)$office['lat'], (float)$office['lng'], $lat, $lng);
                $geoValid = $dist <= (int)$office['radius'];
            }
        }

        // Save photo
        $image = null;
        if (!empty($_POST['image_data'])) {
            $image = $this->model->saveImage($_POST['image_data'], $uid);
        }

        if ($action === 'checkout') {
            $done = $this->model->checkOut($uid, compact('lat', 'lng'));
            $this->json(['success' => $done, 'message' => $done ? 'Checked out.' : 'No active check-in.', '_csrf' => \Core\CSRF::token()]);
        }

        $today = $this->model->todayRecord($uid);
        if ($today) {
            $this->json(['success' => false, 'message' => 'Already checked in today.', '_csrf' => \Core\CSRF::token()]);
        }

        $this->model->checkIn($uid, compact('lat', 'lng', 'geo_valid', 'image') + [
            'method' => $image ? 'camera' : 'manual',
            'geo_valid' => $geoValid,
        ]);
        $msg = 'Checked in.' . ($geoValid ? '' : ' (outside office zone — flagged)');
        $this->json(['success' => true, 'message' => $msg, 'geo_valid' => $geoValid, '_csrf' => \Core\CSRF::token()]);
    }

    public function manualMark(): void {
        $this->verifyCsrf();
        if (!Session::can(['admin','super_admin','hr','manager'])) $this->abort(403);
        $userId = (int)($_POST['user_id'] ?? 0);
        $date   = $_POST['date'] ?? date('Y-m-d');
        $status = $_POST['action'] ?? 'checkin';
        if ($status === 'checkin') {
            $this->db->execute(
                "INSERT INTO attendance (user_id,date,check_in,method,marked_by) VALUES (?,?,NOW(),'manual',?)
                 ON DUPLICATE KEY UPDATE check_in=NOW()",
                [$userId, $date, Session::user()['id']]
            );
        } else {
            $this->db->execute(
                "UPDATE attendance SET check_out=NOW() WHERE user_id=? AND date=? AND check_out IS NULL",
                [$userId, $date]
            );
        }
        Session::flash('success', 'Attendance updated.');
        $this->redirect('attendance');
    }
}
