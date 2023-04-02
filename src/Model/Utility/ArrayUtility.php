<?php

namespace App\Model\Utility;

class ArrayUtility {
  /**
   * Format dates
   *
   * @param array $array
   * @param array $fields
   * @param string $format
   * @return array
   */
  public function formatDates(
    array  $array,
    array  $fields = ['dateUpdated', 'dateCreated'],
    string $format = 'd-m-Y H:i:s'
  ): array {
    $countFields = count($fields);

    for ($i = 0; $i < $countFields; $i++) {
      if (isset($array[$fields[$i]])) {
        $array[$fields[$i]] = is_object($array[$fields[$i]]) ? $array[$fields[$i]]->format($format) : $array[$fields[$i]];
      }
    }

    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $array[$key] = $this->formatDates($value, $fields, $format);
      }
    }

    return $array;
  }

  /**
   * Unset array values without specified keys
   *
   * @param array $array
   * @param string|null $fields
   * @param bool $multi
   * @return array
   */
  public function filterArrayByKeys(array $array, string $fields = null, bool $multi = true): array {
    if (!$fields) {
      return $array;
    }

    $keys = array_flip(explode(',', $fields));

    if (!$multi) {
      $array = array_intersect_key($array, $keys);

      return $array;
    }

    foreach ($array as $key => $value) {
      if (is_array($value)) {
        $array[$key] = array_intersect_key($value, $keys);
      }
    }

    return $array;
  }

  /**
   * Search in array
   *
   * @param array $array
   * @param string $fieldToSearch
   * @param string $valueToSearch
   * @param string $fieldToReturn
   * @return false|mixed
   */
  public static function searchInArray(
    array $array,
    string $fieldToSearch,
    string $valueToSearch,
    string $fieldToReturn
  ): mixed {
    foreach ($array as $data) {
      if ($data[$fieldToSearch] == $valueToSearch) {
        return $data[$fieldToReturn];
      }
    }

    return false;
  }

  /**
   * @param string|null $commaSeparatedIds
   * @return array
   */
  public function splitCommaSeparatedAsInt(string $commaSeparatedIds = null): array {
    if (!$commaSeparatedIds || !trim($commaSeparatedIds)) {
      return [];
    }

    $ids = preg_split('/,\s*/', $commaSeparatedIds);
    $ids = array_map('intval', array_map('trim', $ids));

    return array_unique($ids);
  }
}
