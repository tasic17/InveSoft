<?php
namespace app\models;

use app\core\BaseModel;

class PromenaZalihaModel extends BaseModel {
    public int $promenaID;
    public int $proizvodID;
    public int $korisnikID;
    public string $datum_promene;
    public string $tip_promene;
    public int $kolicina;

    public function tableName(): string {
        return 'promene_zaliha';
    }

    public function readColumns(): array {
        return ['promenaID', 'proizvodID', 'korisnikID', 'datum_promene', 'tip_promene', 'kolicina'];
    }

    public function editColumns(): array {
        return ['proizvodID', 'korisnikID', 'datum_promene', 'tip_promene', 'kolicina'];
    }

    public function validationRules(): array {
        return [
            'proizvodID' => [self::RULE_REQUIRED],
            'korisnikID' => [self::RULE_REQUIRED],
            'datum_promene' => [self::RULE_REQUIRED],
            'tip_promene' => [self::RULE_REQUIRED],
            'kolicina' => [self::RULE_REQUIRED]
        ];
    }
}