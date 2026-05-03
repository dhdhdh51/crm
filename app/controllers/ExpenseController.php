<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use App\Models\Expense;

class ExpenseController extends Controller {

    private const CATEGORIES = ['Marketing','Operations','Travel','Utilities','Salaries','Office','Events','Other'];

    public function index(): void {
        $filters   = ['category'=>$_GET['category']??'','from'=>$_GET['from']??'','to'=>$_GET['to']??''];
        $expenses  = (new Expense())->all($filters);
        $byCategory= (new Expense())->totalByCategory();
        $monthTotal= (new Expense())->monthTotal((int)date('n'), (int)date('Y'));
        $categories= self::CATEGORIES;
        $this->view('expenses.index', compact('expenses','byCategory','monthTotal','categories','filters') + ['title'=>'Expenses']);
    }

    public function store(): void {
        $this->verifyCsrf();
        $receipt = null;
        if (!empty($_FILES['receipt']['tmp_name'])) {
            $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','pdf'])) {
                $receipt = 'receipt_'.uniqid().'.'.$ext;
                move_uploaded_file($_FILES['receipt']['tmp_name'], ROOT.'/storage/uploads/'.$receipt);
            }
        }
        (new Expense())->insert([
            'category'     => $this->sanitize($_POST['category'] ?? 'Other'),
            'description'  => $this->sanitize($_POST['description'] ?? ''),
            'amount'       => (float)($_POST['amount'] ?? 0),
            'expense_date' => $_POST['expense_date'] ?? date('Y-m-d'),
            'receipt'      => $receipt,
            'added_by'     => Session::user()['id'],
        ]);
        Session::flash('success', 'Expense recorded.');
        $this->redirect('expenses');
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        (new Expense())->delete((int)$id);
        Session::flash('success', 'Deleted.');
        $this->redirect('expenses');
    }
}
