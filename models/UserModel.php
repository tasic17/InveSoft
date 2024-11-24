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
        return ['ime', 'prezime', 'email'];
    }

    public function validationRules(): array {
        // For update, we only need these fields
        return [
            'first_name' => [self::RULE_REQUIRED],
            'last_name' => [self::RULE_REQUIRED],
            'email' => [self::RULE_REQUIRED, self::RULE_EMAIL]
        ];
    }

    public function validate() {
        $this->errors = []; // Reset errors

        foreach ($this->validationRules() as $attribute => $rules) {
            $value = $this->{$attribute};

            foreach ($rules as $rule) {
                switch ($rule) {
                    case self::RULE_REQUIRED:
                        if (empty($value)) {
                            $this->errors[$attribute][] = "{$attribute} je obavezno polje.";
                        }
                        break;

                    case self::RULE_EMAIL:
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $this->errors[$attribute][] = "Email adresa nije ispravna.";
                        }
                        break;
                }
            }
        }

        return empty($this->errors);
    }
}