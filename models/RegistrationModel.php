<?php
namespace app\models;

use app\core\BaseModel;

class RegistrationModel extends BaseModel {
    public string $email = '';
    public string $password = '';
    public string $ime = '';
    public string $prezime = '';

    public function tableName(): string {
        return 'korisnici';
    }

    public function readColumns(): array {
        return ['email', 'password', 'ime', 'prezime'];
    }

    public function editColumns(): array {
        return ['email', 'password', 'ime', 'prezime'];
    }

    public function validationRules(): array {
        return [
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL, self::RULE_UNIQUE_EMAIL],
            'password' => [self::RULE_REQUIRED],
            'ime' => [self::RULE_REQUIRED],
            'prezime' => [self::RULE_REQUIRED]
        ];
    }
}