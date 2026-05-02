<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Salary;
use App\Models\User;

class PayrollController extends Controller {

    public function index(): void {
        $salaries = (new Salary())->allWithUser();
        $this->view('payroll.index', ['title' => 'Payroll', 'salaries' => $salaries]);
    }

    public function create(): void {
        $employees = (new User())->allWithRole();
        $this->view('payroll.create', ['title' => 'Process Salary', 'employees' => $employees]);
    }

    public function store(): void {
        $this->verifyCsrf();

        $userId = (int)($_POST['user_id'] ?? 0);
        $month  = (int)($_POST['month'] ?? date('n'));
        $year   = (int)($_POST['year'] ?? date('Y'));

        $salaryModel = new Salary();
        if ($salaryModel->existsForMonth($userId, $month, $year)) {
            Session::flash('error', 'Salary already processed for this employee for that month.');
            $this->redirect('payroll/create');
        }

        $data = [
            'user_id'        => $userId,
            'month'          => $month,
            'year'           => $year,
            'base_salary'    => (float)($_POST['base_salary'] ?? 0),
            'incentives'     => (float)($_POST['incentives'] ?? 0),
            'bonus'          => (float)($_POST['bonus'] ?? 0),
            'deductions'     => (float)($_POST['deductions'] ?? 0),
            'payment_status' => $this->sanitize($_POST['payment_status'] ?? 'pending'),
            'notes'          => $this->sanitize($_POST['notes'] ?? ''),
        ];

        $id = $salaryModel->insert($data);
        logActivity('create', 'payroll', (int)$id, "Salary processed");
        Session::flash('success', 'Salary record saved.');
        $this->redirect('payroll');
    }

    public function slip(string $id): void {
        $slip = (new Salary())->findSlip((int)$id);
        if (!$slip) $this->abort(404);
        $this->view('payroll.slip', ['title' => 'Salary Slip', 'slip' => $slip], 'auth');
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        (new Salary())->delete((int)$id);
        Session::flash('success', 'Record deleted.');
        $this->redirect('payroll');
    }
}
