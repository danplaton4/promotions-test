<?php

namespace App\Model\Utility;

class StringUtility {
  /**
   * @param int $length
   * @param bool $includeSpecial
   * @return string
   */
  public function generateRandomString(int $length = 8, bool $includeSpecial = false): string {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    if ($includeSpecial) {
      $characters .= './?!';
    }

    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
      $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
  }

  /**
   * @param bool $removeDashes
   * @return string
   */
  public function generateUuid(bool $removeDashes = false): string {
    $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff),

      // 16 bits for "time_mid"
      mt_rand(0, 0xffff),

      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand(0, 0x0fff) | 0x4000,

      // 16 bits, 8 bits for "clk_seq_hi_res",
      // 8 bits for "clk_seq_low",
      // two most significant bits holds zero and one for variant DCE1.1
      mt_rand(0, 0x3fff) | 0x8000,

      // 48 bits for "node"
      mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
    );

    return $removeDashes ? str_replace('-', '', $uuid) : $uuid;
  }

  /**
   * @param $string
   * @return array
   */
  public function stringToWords($string): array {
    $string = trim($string ?? '');

    if (!$string) {
      return [];
    }

    if (!str_contains($string, ' ')) {
      return [$string];
    }

    $string = preg_replace("/[^A-Za-z0-9 ]/", '', $string);
    $string = preg_replace('!\s+!', ' ', $string);

    return explode(' ', $string);
  }

  /**
   * @param $folderName
   * @param string $replaceSpecChar
   * @return string
   */
  public function repairZipFolderName($folderName, string $replaceSpecChar = '_'): string {
    $newFolderName = preg_replace(
      '~
        [<>:"/\\|?*]|            # File system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # Control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # Non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=%]|    # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
      $replaceSpecChar,
      $folderName
    );

    return trim(preg_replace('!\s+!', ' ', $newFolderName));
  }

  /**
   * @param string $fileName
   * @return string
   */
  public function sanitizeFileName(string $fileName): string {
    // Sanitize filename
    $newFileName = preg_replace(
      '~
        [<>:"/\\|?*]|            # File system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # Control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # Non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=%]|    # URI reserved https://tools.ietf.org/html/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
      '', $fileName);

    // Avoids ".", ".." or ".hiddenFiles"
    $newFileName = ltrim($newFileName, '.-');

    // Avoids multiple spaces
    return preg_replace('!\s+!', ' ', $newFileName);
  }

  /**
   * @param $projectCurrency
   * @param bool $forCSV
   * @return string
   */
  public function currencyMapping($projectCurrency, bool $forCSV = false): string {
    $onlyPdfDisplay = !$forCSV && in_array($projectCurrency, ['GBP', 'EGP', 'CNY', 'JPY', 'EUR']);
    $onlyCsvDisplay = $forCSV && in_array($projectCurrency, ['PLN', 'INR', 'KRW', 'NGN', 'RUB', 'TRY']);

    return match ($projectCurrency) {
      'USD' => '$',
      'AUD' => 'A$',
      'CAD' => 'CA$',
      'HKD' => 'HK$',
      'ARS' => 'ARS$',
      'BRL' => 'R$',
      'CLP' => 'CLP$',
      'COP' => 'COL$',
      'MXN' => 'MX$',
      'NZD' => 'NZ$',
      'SGD' => 'S$',
      'TWD' => 'NT$',
      'HUF' => 'Ft',
      'DKK' => 'Kr.',
      'IDR' => 'Rp',
      'ISK' => 'IKr.',
      'KWD' => 'K.D.',
      'MYR' => 'RM',
      'NOK' => 'NKr',
      'SEK' => 'SKr.',
      'ZAR' => 'R',
      default => match (true) {
        // £, ¥, €, ₣ cannot be displayed correctly in some CSV editors, but in PDF reports they are displayed correctly
        $onlyPdfDisplay => match ($projectCurrency) {
          'GBP' => '£',
          'EGP' => 'E£',
          'CNY', 'JPY' => '¥',
          'EUR' => '€'
        },
        // zł, ₹, ₩, ₦, ₽, ₺ cannot be displayed correctly in PDF reports, but in some CSV editors they are displayed correctly
        $onlyCsvDisplay => match ($projectCurrency) {
          'PLN' => 'zł',
          'INR' => '₹',
          'KRW' => '₩',
          'NGN' => '₦',
          'RUB' => '₽',
          'TRY' => '₺',
        },
        // ₣ (CHF) cannot be displayed correctly in both CSV and PDF
        // #1131 new added values (THB, Other) will be treated here as well
        // https://app.activecollab.com/126650/projects/940/tasks/484111
        default => $projectCurrency
      }
    };
  }

  /**
   * @param $html
   * @return string
   */
  public function prepareHtmlToPdf($html): string {
    return $this->escapeNonExistentTags($html);
  }

  /**
   * @param $html
   * @return string
   */
  public function escapeNonExistentTags($html): string {
    preg_match_all("|<[^>]+>|U", $html, $matches);

    if (isset($matches[0]) && count($matches[0])) {
      foreach ($matches[0] as $match) {
        $tagName = trim(str_replace(['</', '<', '/>', '>'], '', $match));

        if (preg_match('/\s/', $tagName)) {
          $parts = preg_split('/\s+/', $tagName);
          $tagName = array_shift($parts);
        }

        if (!in_array($tagName, self::$allowedHtmlTags, true)) {
          $html = str_replace($match, str_replace(['<', '>'], ['&lt;', '&gt;'], $match), $html);
        }
      }
    }

    return $html;
  }

  /**
   * @param string|float $firstValue
   * @param string|float $secondValue
   * @return bool
   */
  public function firstFloatValueIsGreater(string|float $firstValue, string|float $secondValue): bool {
    $decimals = 4;

    $firstStringValue = number_format($firstValue, $decimals, '.', '');
    $secondStringValue = number_format($secondValue, $decimals, '.', '');

    return (float)$firstStringValue > (float)$secondStringValue;
  }

  private static array $allowedHtmlTags = [
    'a',
    'abbr',
    'acronym',
    'address',
    'applet',
    'area',
    'article',
    'aside',
    'audio',
    'b',
    'base',
    'basefont',
    'bdi',
    'bdo',
    'big',
    'blockquote',
    'body',
    'br',
    'button',
    'canvas',
    'caption',
    'center',
    'cite',
    'code',
    'col',
    'colgroup',
    'data',
    'datalist',
    'dd',
    'del',
    'details',
    'dfn',
    'dialog',
    'dir',
    'div',
    'dl',
    'dt',
    'em',
    'embed',
    'fieldset',
    'figcaption',
    'figure',
    'font',
    'footer',
    'form',
    'frame',
    'frameset',
    'h1',
    'h2',
    'h3',
    'h4',
    'h5',
    'h6',
    'head',
    'header',
    'hr',
    'html',
    'i',
    'iframe',
    'img',
    'input',
    'ins',
    'kbd',
    'label',
    'legend',
    'li',
    'link',
    'main',
    'map',
    'mark',
    'meta',
    'meter',
    'nav',
    'noframes',
    'noscript',
    'object',
    'ol',
    'optgroup',
    'option',
    'output',
    'p',
    'param',
    'picture',
    'pre',
    'progress',
    'q',
    'rp',
    'rt',
    'ruby',
    's',
    'samp',
    'script',
    'section',
    'select',
    'small',
    'source',
    'span',
    'strike',
    'strong',
    'style',
    'sub',
    'summary',
    'sup',
    'svg',
    'table',
    'tbody',
    'td',
    'template',
    'textarea',
    'tfoot',
    'th',
    'thead',
    'time',
    'title',
    'tr',
    'track',
    'tt',
    'u',
    'ul',
    'var',
    'video',
    'wbr',
  ];
}
