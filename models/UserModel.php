<?php
namespace app\models;

use app\core\BaseModel;

class UserModel extends BaseModel {
    public ?int $id = null;
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $confirm_password = '';
    public string $role = '';

    public function tableName(): string {
        return 'korisnici';
    }

    public function readColumns(): array {
        return ['korisnikID as id', 'ime as first_name', 'prezime as last_name', 'email'];
    }

    public function editColumns(): array {
        return ['ime', 'prezime', 'email', 'password'];
    }

    public function validationRules(): array {
        return [
            'first_name' => [self::RULE_REQUIRED],
            'last_name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL],
            'password' => [self::RULE_REQUIRED],
            'confirm_password' => [self::RULE_REQUIRED]
        ];
    }

    public function validate() {
        parent::validate();

        // Additional password validation
        if (!empty($this->password) && !empty($this->confirm_password)) {
            if ($this->password !== $this->confirm_password) {
                $this->errors['confirm_password'][] = "Passwords do not match";
            }

            // Add password strength validation if needed
            if (strlen($this->password) < 6) {
                $this->errors['password'][] = "Password must be at least 6 characters long";
            }
        }
    }
}