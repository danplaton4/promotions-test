<?php

namespace App\Model\Constant;

class UserRole {

  const ROLE_ADMIN = 'ROLE_ADMIN';
  const ROLE_USER = 'ROLE_USER';

  /**
   * @param bool $includeAdmin
   * @return array
   */
  public static function getConstants(bool $includeAdmin = false): array {
    $oClass = new \ReflectionClass(__CLASS__);

    // Get all constants
    $allConstants = array_values($oClass->getConstants());

    // In case that we don't need an admin one, just filter it
    if ($includeAdmin === false) {
      return array_filter($allConstants, function ($constant) {
        return $constant !== self::ROLE_ADMIN;
      });
    }

    // Otherwise just return all of them
    return $allConstants;
  }

  /**
   * @param string $role
   * @return bool
   */
  public static function isAdmin(string $role): bool {
    return $role === self::ROLE_ADMIN;
  }
}
