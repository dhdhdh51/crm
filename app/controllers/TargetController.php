<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use App\Models\Target;
use App\Models\User;

class TargetController extends Controller {

    public function index(): void {
        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));
        $targets = (new Target())->allForMonth($month, $year);
        $this->view('targets.index', compact('targets','month','year') + ['title'=>'Targets']);
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
