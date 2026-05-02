<?php
namespace App\Controllers;

use Core\Controller;
use Core\Session;
use App\Models\Project;

class ProjectController extends Controller {

    private Project $model;

    public function __construct() {
        parent::__construct();
        $this->model = new Project();
    }

    public function index(): void {
        $projects = $this->model->withUnitStats();
        $this->view('projects.index', ['title' => 'Projects', 'projects' => $projects]);
    }

    public function create(): void {
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $this->view('projects.create', ['title' => 'Add Project']);
    }

    public function store(): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);

        $data = $this->buildData($_POST);

        // Handle image upload
        if (!empty($_FILES['image']['name'])) {
            $uploadedPath = $this->handleUpload($_FILES['image'], 'projects');
            if ($uploadedPath) $data['image'] = $uploadedPath;
        }

        $id = $this->model->insert($data);
        logActivity('create', 'projects', (int)$id, "Project: {$data['name']}");
        Session::flash('success', 'Project created successfully.');
        $this->redirect('projects');
    }

    public function show(string $id): void {
        $project = $this->model->find((int)$id);
        if (!$project) $this->abort(404);
        $units = $this->model->getUnits((int)$id);
        $this->view('projects.view', [
            'title'   => $project['name'],
            'project' => $project,
            'units'   => $units,
        ]);
    }

    public function edit(string $id): void {
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $project = $this->model->find((int)$id);
        if (!$project) $this->abort(404);
        $this->view('projects.edit', ['title' => 'Edit Project', 'project' => $project]);
    }

    public function update(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);

        $data = $this->buildData($_POST);

        if (!empty($_FILES['image']['name'])) {
            $uploadedPath = $this->handleUpload($_FILES['image'], 'projects');
            if ($uploadedPath) $data['image'] = $uploadedPath;
        }

        $this->model->update((int)$id, $data);
        logActivity('update', 'projects', (int)$id, "Project updated");
        Session::flash('success', 'Project updated.');
        $this->redirect("projects/{$id}");
    }

    public function delete(string $id): void {
        $this->verifyCsrf();
        if (!Session::can('admin')) $this->abort(403);
        $this->model->delete((int)$id);
        logActivity('delete', 'projects', (int)$id, 'Project deleted');
        Session::flash('success', 'Project deleted.');
        $this->redirect('projects');
    }

    public function units(string $id): void {
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $project = $this->model->find((int)$id);
        if (!$project) $this->abort(404);
        $units   = $this->model->getUnits((int)$id);
        $this->view('projects.units', [
            'title'   => 'Units – ' . $project['name'],
            'project' => $project,
            'units'   => $units,
        ]);
    }

    public function storeUnit(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $data = [
            'project_id'  => (int)$id,
            'unit_number' => $this->sanitize($_POST['unit_number'] ?? ''),
            'type'        => $this->sanitize($_POST['type'] ?? 'apartment'),
            'floor'       => (int)($_POST['floor'] ?? 0),
            'area_sqft'   => (float)($_POST['area_sqft'] ?? 0),
            'price'       => (float)($_POST['price'] ?? 0),
            'status'      => $this->sanitize($_POST['status'] ?? 'available'),
            'facing'      => $this->sanitize($_POST['facing'] ?? ''),
        ];
        $this->db->execute(
            "INSERT INTO units (project_id, unit_number, type, floor, area_sqft, price, status, facing)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
            array_values($data)
        );
        Session::flash('success', 'Unit added.');
        $this->redirect("projects/{$id}/units");
    }

    public function updateUnit(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $projectId = (int)$this->db->fetchColumn("SELECT project_id FROM units WHERE id = ?", [(int)$id]);
        $this->db->execute(
            "UPDATE units SET unit_number=?, type=?, floor=?, area_sqft=?, price=?, status=?, facing=? WHERE id=?",
            [
                $this->sanitize($_POST['unit_number'] ?? ''),
                $this->sanitize($_POST['type'] ?? 'apartment'),
                (int)($_POST['floor'] ?? 0),
                (float)($_POST['area_sqft'] ?? 0),
                (float)($_POST['price'] ?? 0),
                $this->sanitize($_POST['status'] ?? 'available'),
                $this->sanitize($_POST['facing'] ?? ''),
                (int)$id,
            ]
        );
        Session::flash('success', 'Unit updated.');
        $this->redirect("projects/{$projectId}/units");
    }

    public function deleteUnit(string $id): void {
        $this->verifyCsrf();
        if (!Session::can(['admin', 'manager'])) $this->abort(403);
        $projectId = (int)$this->db->fetchColumn("SELECT project_id FROM units WHERE id = ?", [(int)$id]);
        $this->db->execute("DELETE FROM units WHERE id = ?", [(int)$id]);
        Session::flash('success', 'Unit deleted.');
        $this->redirect("projects/{$projectId}/units");
    }

    private function buildData(array $p): array {
        return [
            'name'            => $this->sanitize($p['name'] ?? ''),
            'location'        => $this->sanitize($p['location'] ?? ''),
            'description'     => $this->sanitize($p['description'] ?? ''),
            'total_units'     => (int)($p['total_units'] ?? 0),
            'available_units' => (int)($p['available_units'] ?? 0),
            'price_per_sqft'  => (float)($p['price_per_sqft'] ?? 0),
            'status'          => $this->sanitize($p['status'] ?? 'upcoming'),
        ];
    }

    private function handleUpload(array $file, string $folder): ?string {
        $cfg = require ROOT . '/config/app.php';
        if ($file['error'] !== UPLOAD_ERR_OK) return null;
        if ($file['size'] > $cfg['upload']['max_size']) return null;
        if (!in_array($file['type'], $cfg['upload']['allowed_images'], true)) return null;

        $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('img_', true) . '.' . strtolower($ext);
        $dest     = ROOT . "/storage/uploads/{$folder}/{$filename}";

        if (!move_uploaded_file($file['tmp_name'], $dest)) return null;
        return "storage/uploads/{$folder}/{$filename}";
    }
}
