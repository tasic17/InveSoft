<?php
namespace app\models;

use app\core\BaseModel;

class ZalihaModel extends BaseModel {
    public int $zalihaID;
    public int $proizvodID;
    public int $kolicina;

    public function tableName(): string {
        return 'zalihe';
    }

    public function readColumns(): array {
        return ['zalihaID', 'proizvodID', 'kolicina'];
    }

    public function editColumns(): array {
        return ['proizvodID', 'kolicina'];
    }

    public function validationRules(): array {
        return [
            'proizvodID' => [self::RULE_REQUIRED],
            'kolicina' => [self::RULE_REQUIRED]
        ];
    }
}
