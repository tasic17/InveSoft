<?php
namespace app\models;

use app\core\BaseModel;

class LoginModel extends BaseModel {
    public string $email = '';
    public string $password = '';
    public ?int $korisnikID = null;
    public ?string $ime = null;
    public ?string $prezime = null;

    public function tableName(): string {
        return 'korisnici';
    }

    public function readColumns(): array {
        return ['korisnikID', 'email', 'password', 'ime', 'prezime'];
    }

    public function editColumns(): array {
        return ['email', 'password', 'ime', 'prezime'];
    }

    public function validationRules(): array {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'password' => [self::RULE_REQUIRED]
        ];
    }
}