<?php
namespace App\Models;
use Core\Model;

class Setting extends Model {
    protected string $table = 'settings';

    public function get(string $key, mixed $default = null): mixed {
        $row = $this->db->fetch("SELECT `value` FROM settings WHERE `key` = ?", [$key]);
        return ($row && $row['value'] !== null) ? $row['value'] : $default;
    }

    public function set(string $key, mixed $value): void {
        $this->db->execute(
            "INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)",
            [$key, $value]
        );
    }

    public function getOffice(): array {
        return [
            'lat'    => $this->get('office_lat'),
            'lng'    => $this->get('office_lng'),
            'radius' => (int)$this->get('office_radius', 100),
            'name'   => $this->get('office_name', 'Main Office'),
        ];
    }
}
