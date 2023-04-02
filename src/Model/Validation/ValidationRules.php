<?php

namespace App\Model\Validation;

use Exception;

abstract class ValidationRules {
  // Get the validation rule from a specific entity
  abstract public static function getValidationRules();

  // Get validation rules

  /**
   * @param string $resource
   * @param string|null $userRole
   * @return mixed
   * @throws Exception
   */
  public static function get(string $resource, string $userRole = null): mixed {
    $class = __NAMESPACE__ . '\\Rules\\' . ucfirst($resource) . 'ValidationRules';

    if (!class_exists($class)) {
      throw new Exception('Class "' . $class . '" not found.');
    }

    return $class::getValidationRules($userRole);
  }
}
