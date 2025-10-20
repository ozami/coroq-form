<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Interface for form items that have length constraints
 *
 * Implemented by inputs using LengthRangeTrait (e.g., TextInput)
 * Useful for HTML generators to set maxlength attribute, show character counters, etc.
 */
interface HasLengthRange {
  public function getMinLength(): int;
  public function setMinLength(int $minLength): self;
  public function getMaxLength(): int;
  public function setMaxLength(int $maxLength): self;
}
