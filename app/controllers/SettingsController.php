<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Setting;

class SettingsController extends Controller {

    public function index(): void {
        if (!Session::can(['admin','super_admin'])) $this->abort(403);
        $office = (new Setting())->getOffice();
        $this->view('settings.index', ['title' => 'Settings', 'office' => $office]);
    }

    public function saveOffice(): void {
        if (!Session::can(['admin','super_admin'])) $this->abort(403);
        $this->verifyCsrf();
        $model = new Setting();
        $lat  = $_POST['office_lat']  !== '' ? (float)$_POST['office_lat']  : null;
        $lng  = $_POST['office_lng']  !== '' ? (float)$_POST['office_lng']  : null;
        $model->set('office_lat',    $lat);
        $model->set('office_lng',    $lng);
        $model->set('office_radius', max(50, (int)($_POST['office_radius'] ?? 100)));
        $model->set('office_name',   trim($_POST['office_name'] ?? 'Main Office'));
        logActivity('update', 'settings', 0, 'Office geo-fence updated');
        Session::flash('success', 'Office location saved.');
        $this->redirect('settings');
    }
}
