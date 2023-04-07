<?php

namespace App\Model\Constant;

class DateTimeFormats {

  public const DATE_FORMAT = 'Y-m-d';
  public const TIME_FORMAT = 'H:i:s';

  public const FULL_FORMAT = self::DATE_FORMAT . ' ' . self::TIME_FORMAT;

  public static function getConstants(): array {
    $oClass = new \ReflectionClass(__CLASS__);

    return array_values($oClass->getConstants());
  }

}
