<?php
// models/KategorijaModel.php
namespace app\models;

use app\core\BaseModel;

class KategorijaModel extends BaseModel {
    public int $kategorijaID;
    public string $naziv = '';

    public function tableName(): string {
        return 'kategorije';
    }

    public function readColumns(): array {
        return ['kategorijaID', 'naziv'];
    }

    public function editColumns(): array {
        return ['naziv'];
    }

    public function validationRules(): array {
        return [
            'naziv' => [self::RULE_REQUIRED]
        ];
    }
}