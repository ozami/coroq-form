<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Interface for form items that have numeric range constraints
 *
 * Implemented by inputs using NumericRangeTrait (e.g., IntegerInput, NumberInput)
 * Useful for HTML generators to set min/max attributes on number inputs
 */
interface HasNumericRange {
  public function getMin(): int|float;
  public function setMin(int|float $min): self;
  public function getMax(): int|float;
  public function setMax(int|float $max): self;
}
