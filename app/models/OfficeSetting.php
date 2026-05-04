<?php
namespace App\Models;
use Core\Model;

class OfficeSetting extends Model {
    protected string $table = 'office_settings';

    public function get(): array {
        return $this->db->fetch("SELECT * FROM office_settings WHERE id=1 LIMIT 1")
            ?: ['id'=>1,'name'=>'Main Office','lat'=>null,'lng'=>null,'radius'=>100];
    }

    public function save(array $data): void {
        $this->db->execute(
            "INSERT INTO office_settings (id,name,lat,lng,radius) VALUES (1,?,?,?,?)
             ON DUPLICATE KEY UPDATE name=VALUES(name),lat=VALUES(lat),lng=VALUES(lng),radius=VALUES(radius)",
            [$data['name'], $data['lat'], $data['lng'], $data['radius']]
        );
    }
}
