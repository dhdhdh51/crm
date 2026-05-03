<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use App\Models\Target;
use App\Models\User;

class TargetController extends Controller {

    public function index(): void {
        $month  = (int)($_GET['month'] ?? date('n'));
        $year   = (int)($_GET['year']  ?? date('Y'));
        $model  = new Target();
        $isAdmin = Session::can(['admin','super_admin','hr','manager']);
        if ($isAdmin) {
            $targets = $model->allForMonth($month, $year);
        } else {
            $own = $model->forUser(Session::user()['id'], $month, $year);
            $targets = $own ? [$own + ['name' => Session::user()['name'], 'employee_id' => Session::user()['employee_id'], 'actual_leads' => 0, 'actual_visits' => 0]] : [];
            // Get actual counts for own target
            if ($own) {
                $uid = Session::user()['id'];
                $from = sprintf('%04d-%02d-01', $year, $month);
                $to   = date('Y-m-t', strtotime($from));
                $targets[0]['actual_leads']  = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM leads WHERE assigned_to=? AND DATE(created_at) BETWEEN ? AND ?", [$uid,$from,$to]);
                $targets[0]['actual_visits'] = (int)$this->db->fetchColumn(
                    "SELECT COUNT(*) FROM site_visits WHERE assigned_to=? AND visit_date BETWEEN ? AND ?", [$uid,$from,$to]);
            }
        }
        $this->view('targets.index', compact('targets','month','year','isAdmin') + ['title'=>'Targets']);
    }

    public function create(): void {
        $employees = (new User())->salesExecutives();
        $this->view('targets.create', ['title'=>'Set Target','employees'=>$employees]);
    }

    public function store(): void {
        $this->verifyCsrf();
        $data = [
            'user_id'        => (int)$_POST['user_id'],
            'month'          => (int)$_POST['month'],
            'year'           => (int)$_POST['year'],
            'target_leads'   => (int)($_POST['target_leads'] ?? 0),
            'target_visits'  => (int)($_POST['target_visits'] ?? 0),
            'target_sales'   => (int)($_POST['target_sales'] ?? 0),
            'target_revenue' => (float)($_POST['target_revenue'] ?? 0),
            'created_by'     => Session::user()['id'],
        ];
        (new Target())->upsert($data);
        Session::flash('success', 'Target saved.');
        $this->redirect('targets');
    }
}
