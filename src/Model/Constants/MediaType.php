<?php

namespace App\Model\Constants;

class MediaType {
  public const AVATAR = "avatar";

  public static function getConstants(): array {
    $oClass = new \ReflectionClass(__CLASS__);

    return array_values($oClass->getConstants());
  }
}
