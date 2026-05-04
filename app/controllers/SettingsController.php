<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\OfficeSetting;
use App\Models\Attendance;
use App\Models\User;

class SettingsController extends Controller {

    public function geo(): void {
        if (!Session::can(['admin','super_admin','hr'])) $this->abort(403);
        $this->view('settings.geo', [
            'title'  => 'Office Geo-fence',
            'office' => (new OfficeSetting())->get(),
        ]);
    }

    public function saveGeo(): void {
        $this->verifyCsrf();
        if (!Session::can(['admin','super_admin','hr'])) $this->abort(403);
        $lat = $_POST['lat'] !== '' ? (float)$_POST['lat'] : null;
        $lng = $_POST['lng'] !== '' ? (float)$_POST['lng'] : null;
        (new OfficeSetting())->save([
            'name'   => trim($_POST['name'] ?? 'Main Office'),
            'lat'    => $lat,
            'lng'    => $lng,
            'radius' => max(50, (int)($_POST['radius'] ?? 100)),
        ]);
        logActivity('update', 'settings', 0, 'Office geo-fence updated');
        Session::flash('success', 'Office location saved.');
        $this->redirect('settings/geo');
    }

    public function attendanceLogs(): void {
        if (!Session::can(['admin','super_admin','hr','manager'])) $this->abort(403);
        $f = [
            'user_id' => (int)($_GET['user_id'] ?? 0),
            'month'   => (int)($_GET['month'] ?? date('m')),
            'year'    => (int)($_GET['year']  ?? date('Y')),
        ];
        $this->view('settings.attendance_logs', [
            'title'     => 'Attendance Logs',
            'records'   => (new Attendance())->allWithUsers($f),
            'employees' => (new User())->allWithRole(),
            'filters'   => $f,
        ]);
    }
}
