<?php
namespace app\models;

use app\core\BaseModel;

class ProizvodModel extends BaseModel {
    public int $proizvodID;
    public string $naziv = '';
    public string $opis = '';
    public float $cena;
    public int $kategorijaID;

    public function tableName(): string {
        return 'proizvodi';
    }

    public function readColumns(): array {
        return ['proizvodID', 'naziv', 'opis', 'cena', 'kategorijaID'];
    }

    public function editColumns(): array {
        return ['naziv', 'opis', 'cena', 'kategorijaID'];
    }

    public function validationRules(): array {
        return [
            'naziv' => [self::RULE_REQUIRED],
            'opis' => [self::RULE_REQUIRED],
            'cena' => [self::RULE_REQUIRED],
            'kategorijaID' => [self::RULE_REQUIRED]
        ];
    }
}