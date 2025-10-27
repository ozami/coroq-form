<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Interface for form items that have numeric range constraints
 *
 * Implemented by inputs using NumericRangeTrait (e.g., IntegerInput, NumberInput)
 * Useful for HTML generators to read/set min/max attributes on number inputs
 *
 * All values are strings for precise representation and to match web form reality.
 * Returns null when no limit is set (equivalent to -INF/INF).
 */
interface HasNumericRangeInterface {
  public function getMin(): string|null;
  public function getMax(): string|null;
  public function setMin(string $min): self;
  public function setMax(string $max): self;
}
