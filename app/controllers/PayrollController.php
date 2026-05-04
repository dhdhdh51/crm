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
        $year   = (int)($_POST['year']  ?? date('Y'));
        $base   = (float)($_POST['base_salary'] ?? 0);

        $salaryModel = new Salary();
        if ($salaryModel->existsForMonth($userId, $month, $year)) {
            Session::flash('error', 'Salary already processed for this employee for that month.');
            $this->redirect('payroll/create');
        }

        $deductions = (float)($_POST['deductions'] ?? 0);

        $data = [
            'user_id'        => $userId,
            'month'          => $month,
            'year'           => $year,
            'base_salary'    => $base,
            'incentives'     => (float)($_POST['incentives'] ?? 0),
            'bonus'          => (float)($_POST['bonus'] ?? 0),
            'deductions'     => $deductions,
            'payment_status' => in_array($_POST['payment_status'] ?? '', ['pending','paid']) ? $_POST['payment_status'] : 'pending',
            'payment_date'   => !empty($_POST['payment_date']) ? $_POST['payment_date'] : null,
            'remarks'        => $this->sanitize($_POST['notes'] ?? ''),
            'generated_by'   => Session::user()['id'],
        ];

        $id = $salaryModel->insert($data);
        logActivity('create', 'payroll', (int)$id, "Salary processed");
        Session::flash('success', 'Salary record saved.');
        $this->redirect('payroll');
    }

    public function slip(string $id): void {
        $slip = (new Salary())->findSlip((int)$id);
        if (!$slip) $this->abort(404);
        $uid = Session::user()['id'];
        if ((int)$slip['user_id'] !== $uid && !Session::can(['admin', 'super_admin', 'hr'])) {
            $this->abort(403);
        }
        $this->view('payroll.slip', ['title' => 'Salary Slip', 'slip' => $slip], 'auth');
    }

    public function mySlips(): void {
        $userId = Session::user()['id'];
        $slips  = $this->db->fetchAll(
            "SELECT * FROM salaries WHERE user_id=? ORDER BY year DESC, month DESC",
            [$userId]
        );
        $this->view('payroll.my_slips', ['title'=>'My Salary Slips','slips'=>$slips]);
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        (new Salary())->delete((int)$id);
        Session::flash('success', 'Record deleted.');
        $this->redirect('payroll');
    }
}
