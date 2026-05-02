<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\SiteVisit;
use App\Models\Lead;
use App\Models\Project;
use App\Models\User;

class SiteVisitController extends Controller {

    public function index(): void {
        $model = new SiteVisit();
        $visits = Session::role() === 'sales_executive'
            ? $model->filterByEmployee(Session::user()['id'])
            : $model->allWithDetails();

        $this->view('site-visits.index', ['title' => 'Site Visits', 'visits' => $visits]);
    }

    public function create(): void {
        $leads    = (new Lead())->findAll("status NOT IN ('closed','lost')", [], 'name');
        $projects = (new Project())->allActive();
        $users    = (new User())->salesExecutives();
        $this->view('site-visits.create', [
            'title'    => 'Schedule Visit',
            'leads'    => $leads,
            'projects' => $projects,
            'users'    => $users,
        ]);
    }

    public function store(): void {
        $this->verifyCsrf();
        $data = [
            'lead_id'     => (int)($_POST['lead_id'] ?? 0),
            'project_id'  => !empty($_POST['project_id']) ? (int)$_POST['project_id'] : null,
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'visit_date'  => $_POST['visit_date'] ?? date('Y-m-d H:i:s'),
            'status'      => 'scheduled',
            'notes'       => $this->sanitize($_POST['notes'] ?? ''),
            'created_by'  => Session::user()['id'],
        ];
        $id = (new SiteVisit())->insert($data);
        logActivity('create', 'site_visits', (int)$id, 'Visit scheduled');
        Session::flash('success', 'Site visit scheduled.');
        $this->redirect('site-visits');
    }

    public function edit(string $id): void {
        $visit = (new SiteVisit())->findWithDetails((int)$id);
        if (!$visit) $this->abort(404);
        $leads    = (new Lead())->findAll('', [], 'name');
        $projects = (new Project())->allActive();
        $users    = (new User())->salesExecutives();
        $this->view('site-visits.edit', [
            'title'    => 'Edit Visit',
            'visit'    => $visit,
            'leads'    => $leads,
            'projects' => $projects,
            'users'    => $users,
        ]);
    }

    public function update(string $id): void {
        $this->verifyCsrf();
        $data = [
            'lead_id'     => (int)($_POST['lead_id'] ?? 0),
            'project_id'  => (int)($_POST['project_id'] ?? 0),
            'assigned_to' => !empty($_POST['assigned_to']) ? (int)$_POST['assigned_to'] : null,
            'visit_date'  => $_POST['visit_date'] ?? date('Y-m-d H:i:s'),
            'status'      => $this->sanitize($_POST['status'] ?? 'scheduled'),
            'feedback'    => $this->sanitize($_POST['feedback'] ?? ''),
            'notes'       => $this->sanitize($_POST['notes'] ?? ''),
        ];
        (new SiteVisit())->update((int)$id, $data);
        logActivity('update', 'site_visits', (int)$id, 'Visit updated');
        Session::flash('success', 'Visit updated.');
        $this->redirect('site-visits');
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        (new SiteVisit())->delete((int)$id);
        Session::flash('success', 'Visit deleted.');
        $this->redirect('site-visits');
    }
}
