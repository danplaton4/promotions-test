<?php

namespace App\Model\Helpers;

use App\Model\Utility\StringUtility;

class SearchQueryHelper {

  /**
   * @param $q
   * @return array|string[]
   */
  public static function splitSearchQueryString($q): array {
    $q = trim($q ?? '');

    if (!$q) {
      return [''];
    }

    if (strpos($q, ' ') === false) {
      return [$q];
    }

    return explode(' ', preg_replace('!\s+!', ' ', $q));
  }

  /**
   * @param $attributes
   * @param $q
   * @param $qb
   * @return void
   */
  public static function addQueryStringConditionMultiple($attributes, $q, &$qb): void {
    $qHash = (new StringUtility())->generateUuid(true);

    if (!is_array($q)) {
      $q = self::splitSearchQueryString($q);
    }

    foreach ($q as $qNo => $qPart) {
      $conditionsL = [];
      foreach ($attributes as $attribute) {
        $conditionsL[] = "$attribute LIKE :q$qNo$qHash";;
      }
      $qb->andWhere('(' . implode(' OR ', $conditionsL) . ')');
      $qb->setParameter("q$qNo$qHash", "%$qPart%");
    }
  }
}
