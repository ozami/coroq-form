<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Provides string filtering methods (trim, mb_convert_kana, UTF-8 scrubbing)
 */
trait StringFilterTrait {
  /**
   * Replace invalid UTF-8 bytes with substitute character
   * Configure substitute character with mb_substitute_character() in your application bootstrap
   */
  protected function scrubUtf8(string $value): string {
    return mb_scrub($value, 'UTF-8');
  }

  /**
   * Trim whitespace from start
   * Removes ASCII spaces, control chars, NBSP, and full-width space
   */
  protected function trimStart(string $value): string {
    return preg_replace("/^[[:space:]\\00\\xa0　]+/u", "", $value);
  }

  /**
   * Trim whitespace from end
   * Removes ASCII spaces, control chars, NBSP, and full-width space
   */
  protected function trimEnd(string $value): string {
    return preg_replace("/[[:space:]\\00\\xa0　]+$/u", "", $value);
  }

  /**
   * Trim whitespace from both ends
   */
  protected function trim(string $value): string {
    return $this->trimEnd($this->trimStart($value));
  }

  /**
   * Convert full-width ASCII to half-width
   * For inputs with official ASCII specifications (email, URL, numbers, dates)
   */
  protected function toHalfwidthAscii(string $value): string {
    return mb_convert_kana($value, "as", "UTF-8");
  }

  /**
   * Remove all whitespace characters
   * Removes ASCII spaces, control chars, NBSP, and full-width space
   */
  protected function removeWhitespace(string $value): string {
    return preg_replace("/[[:space:]\\00\\xa0　]/u", "", $value);
  }

  /**
   * Normalize Unicode string
   *
   * @param string $value Input string
   * @param string $form Normalization form ('NFC'|'NFD'|'NFKC'|'NFKD')
   * @param bool $strict If true, throw exception when normalization unavailable
   * @return string Normalized string, or original if unavailable and not strict
   * @throws \LogicException If normalization unavailable and strict=true
   */
  protected function normalizeUnicode(string $value, string $form, bool $strict = false): string {
    if (!extension_loaded('intl')) {
      if ($strict) {
        throw new \LogicException('Unicode normalization unavailable (intl extension not loaded)');
      }
      return $value;
    }

    // Map form string to Normalizer constant
    $normalizerForm = constant('Normalizer::' . $form);
    return \Normalizer::normalize($value, $normalizerForm);
  }
}
