<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Lead;
use App\Models\User;
use App\Models\Project;
use App\Models\Followup;
use App\Models\Notification;

class LeadController extends Controller {

    private Lead    $leadModel;
    private User    $userModel;
    private Project $projectModel;

    public function __construct() {
        parent::__construct();
        $this->leadModel    = new Lead();
        $this->userModel    = new User();
        $this->projectModel = new Project();
    }

    public function index(): void {
        $filters = [
            'search'      => trim($_GET['search']     ?? ''),
            'status'      => $_GET['status']           ?? '',
            'source'      => $_GET['source']           ?? '',
            'assigned_to' => $_GET['assigned_to']      ?? '',
            'project_id'  => $_GET['project_id']       ?? '',
            'from_date'   => $_GET['from_date']        ?? '',
            'to_date'     => $_GET['to_date']          ?? '',
        ];
        $page    = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 15;

        $result   = $this->leadModel->filter($filters, $page, $perPage);
        $employees = $this->userModel->salesExecutives();
        $projects  = $this->projectModel->allActive();

        // Build query string for pagination
        $qStr = http_build_query(array_filter($filters));

        $this->view('leads.index', [
            'title'     => 'Leads',
            'result'    => $result,
            'filters'   => $filters,
            'employees' => $employees,
            'projects'  => $projects,
            'qStr'      => $qStr ? '&' . $qStr : '',
        ]);
    }

    public function create(): void {
        $this->view('leads.create', [
            'title'     => 'Add Lead',
            'employees' => $this->userModel->salesExecutives(),
            'projects'  => $this->projectModel->allActive(),
        ]);
    }

    public function store(): void {
        $this->verifyCsrf();
        $data = $this->buildData($_POST);
        $data['created_by'] = Session::user()['id'];

        if (!$data['name'] || !$data['phone']) {
            Session::flash('error', 'Name and phone are required.');
            $this->withInput($_POST);
            $this->redirect('leads/create');
        }

        $id = $this->leadModel->insert($data);
        logActivity('create', 'leads', (int)$id, "Lead: {$data['name']}");

        // Notify assigned employee
        if (!empty($data['assigned_to'])) {
            Notification::create(
                (int)$data['assigned_to'],
                'lead',
                "New lead assigned: {$data['name']}",
                url("leads/{$id}")
            );
        }

        Session::flash('success', 'Lead added successfully.');
        $this->redirect('leads');
    }

    public function show(string $id): void {
        $lead = $this->leadModel->findWithDetails((int)$id);
        if (!$lead) $this->abort(404, 'Lead not found.');

        $followups = (new Followup())->forLead((int)$id);
        $this->view('leads.view', [
            'title'     => 'Lead: ' . $lead['name'],
            'lead'      => $lead,
            'followups' => $followups,
            'employees' => $this->userModel->salesExecutives(),
            'projects'  => $this->projectModel->allActive(),
        ]);
    }

    public function edit(string $id): void {
        $lead = $this->leadModel->findWithDetails((int)$id);
        if (!$lead) $this->abort(404, 'Lead not found.');
        $this->enforceAccess($lead);

        $this->view('leads.edit', [
            'title'     => 'Edit Lead',
            'lead'      => $lead,
            'employees' => $this->userModel->salesExecutives(),
            'projects'  => $this->projectModel->allActive(),
        ]);
    }

    public function update(string $id): void {
        $this->verifyCsrf();
        $lead = $this->leadModel->find((int)$id);
        if (!$lead) $this->abort(404);
        $this->enforceAccess($lead);

        $data = $this->buildData($_POST);
        $this->leadModel->update((int)$id, $data);
        logActivity('update', 'leads', (int)$id, "Lead updated: {$data['name']}");

        Session::flash('success', 'Lead updated successfully.');
        $this->redirect("leads/{$id}");
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) {
            $this->abort(403, 'Insufficient permissions.');
        }
        $this->leadModel->delete((int)$id);
        logActivity('delete', 'leads', (int)$id, 'Lead deleted');

        Session::flash('success', 'Lead deleted.');
        $this->redirect('leads');
    }

    public function addFollowup(string $id): void {
        $this->verifyCsrf();
        $lead = $this->leadModel->find((int)$id);
        if (!$lead) $this->abort(404);

        $followupData = [
            'lead_id'            => (int)$id,
            'user_id'            => Session::user()['id'],
            'followup_date'      => date('Y-m-d'),
            'type'               => $this->sanitize($_POST['type'] ?? 'call'),
            'notes'              => $this->sanitize($_POST['notes'] ?? ''),
            'outcome'            => $this->sanitize($_POST['outcome'] ?? ''),
            'next_followup_date' => $_POST['next_followup_date'] ?: null,
            'status'             => 'completed',
        ];

        (new Followup())->insert($followupData);

        // Update lead's next followup date and optionally status
        $updateLead = ['next_followup_date' => $followupData['next_followup_date']];
        if (!empty($_POST['lead_status'])) {
            $updateLead['status'] = $this->sanitize($_POST['lead_status']);
        }
        $this->leadModel->update((int)$id, $updateLead);
        logActivity('followup', 'leads', (int)$id, 'Followup logged');

        Session::flash('success', 'Follow-up recorded.');
        $this->redirect("leads/{$id}");
    }

    private function buildData(array $post): array {
        return [
            'name'                 => $this->sanitize($post['name'] ?? ''),
            'phone'                => $this->sanitize($post['phone'] ?? ''),
            'email'                => $this->sanitize($post['email'] ?? ''),
            'source'               => $this->sanitize($post['source'] ?? 'direct'),
            'status'               => $this->sanitize($post['status'] ?? 'new'),
            'interested_project_id'=> !empty($post['interested_project_id']) ? (int)$post['interested_project_id'] : null,
            'budget_min'           => !empty($post['budget_min']) ? (float)$post['budget_min'] : null,
            'budget_max'           => !empty($post['budget_max']) ? (float)$post['budget_max'] : null,
            'assigned_to'          => !empty($post['assigned_to']) ? (int)$post['assigned_to'] : null,
            'next_followup_date'   => !empty($post['next_followup_date']) ? $post['next_followup_date'] : null,
            'notes'                => $this->sanitize($post['notes'] ?? ''),
            'closed_value'         => !empty($post['closed_value']) ? (float)$post['closed_value'] : null,
        ];
    }

    private function enforceAccess(array $lead): void {
        $user = Session::user();
        if ($user['role_slug'] === 'sales_executive' && $lead['assigned_to'] != $user['id']) {
            $this->abort(403, 'Access denied.');
        }
    }
}
