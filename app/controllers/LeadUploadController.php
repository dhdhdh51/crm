<?php
namespace App\Controllers;
use Core\Controller;
use Core\Session;
use App\Models\LeadUpload;
use App\Models\User;

class LeadUploadController extends Controller {

    public function index(): void {
        $logs      = (new LeadUpload())->logs();
        $employees = (new User())->salesExecutives();
        $this->view('leads.upload', compact('logs','employees') + ['title'=>'Import Leads']);
    }

    public function import(): void {
        $this->verifyCsrf();
        if (empty($_FILES['csv']['tmp_name'])) {
            Session::flash('error', 'No file uploaded.');
            $this->redirect('leads/upload');
        }
        $assignTo = (int)($_POST['assign_to'] ?? 0);
        $file = $_FILES['csv']['tmp_name'];
        $rows = [];
        if (($h = fopen($file, 'r')) !== false) {
            $header = fgetcsv($h);   // skip header row
            while (($row = fgetcsv($h)) !== false) {
                if (array_filter($row)) $rows[] = $row;
            }
            fclose($h);
        }
        if (empty($rows)) {
            Session::flash('error', 'File is empty or invalid.');
            $this->redirect('leads/upload');
        }
        $result = (new LeadUpload())->importRows($rows, $assignTo, Session::user()['id']);
        Session::flash('success', "Imported {$result['imported']} leads. Failed: {$result['failed']}.");
        $this->redirect('leads/upload');
    }
}
