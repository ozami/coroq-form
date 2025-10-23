<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Provides string filtering methods (trim, mb_convert_kana)
 */
trait StringFilterTrait {
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
}
