<?php

namespace App\Model\Constant;


class Status {
  const ACTIVE = "ACTIVE";
  const INACTIVE = "INACTIVE";

  /**
   * @return array
   */
  public static function getConstants(): array {
    $oClass = new \ReflectionClass(__CLASS__);

    return array_values($oClass->getConstants());
  }
}
