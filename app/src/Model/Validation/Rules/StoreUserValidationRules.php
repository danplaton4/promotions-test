<?php

namespace App\Model\Validation\Rules;

use App\Model\Constant\ValidationType;

class StoreUserValidationRules {
  // Validation rules
  public static array $validationRules = [
    'email' => [
      'label' => 'Email',
      'rules' => [ValidationType::REQUIRED, ValidationType::EMAIL]
    ],
    'firstName' => [
      'label' => 'First Name',
      'rules' => [ValidationType::REQUIRED]
    ],
    'lastName' => [
      'label' => 'Last Name',
      'rules' => [ValidationType::REQUIRED]
    ],
    'password' => [
      'label' => 'Password',
      'rules' => [ValidationType::REQUIRED, ValidationType::PASSWORD]
    ]
  ];

  // Get validation rules
  public static function getValidationRules(): array {
    return self::$validationRules;
  }
}
