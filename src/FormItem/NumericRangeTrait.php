<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;

/**
 * Provides min/max numeric range validation
 */
trait NumericRangeTrait {
  /** String representation for precise bcmath comparison */
  protected string|null $min = null;
  /** String representation for precise bcmath comparison */
  protected string|null $max = null;

  /**
   * Get minimum value as string
   * Returns null when no minimum is set (equivalent to -INF)
   */
  public function getMin(): string|null {
    return $this->min;
  }

  /**
   * Set minimum value
   * Override this method in concrete classes for additional validation,
   * then call setMinValue() to set the actual value
   */
  public function setMin(int|float|string $min): static {
    $this->min = (string)$min;
    return $this;
  }

  /**
   * Get maximum value as string
   * Returns null when no maximum is set (equivalent to INF)
   */
  public function getMax(): string|null {
    return $this->max;
  }

  /**
   * Set maximum value
   * Override this method in concrete classes for additional validation,
   * then call setMaxValue() to set the actual value
   */
  public function setMax(int|float|string $max): static {
    $this->max = (string)$max;
    return $this;
  }

  /**
   * Validate value is within min/max range
   * Uses bcmath with dynamic scale detection for precise comparison
   *
   * @param string $value Numeric value as string
   * @return Error|null TooSmallError or TooLargeError if out of range
   */
  protected function validateRange(string $value): ?Error {
    if ($this->min !== null) {
      $scale = $this->detectRequiredScale($value, $this->min);
      if (bccomp($value, $this->min, $scale) < 0) {
        return new TooSmallError($this);
      }
    }

    if ($this->max !== null) {
      $scale = $this->detectRequiredScale($value, $this->max);
      if (bccomp($value, $this->max, $scale) > 0) {
        return new TooLargeError($this);
      }
    }

    return null;
  }

  /**
   * Detect required decimal scale for bccomp from two values
   */
  private function detectRequiredScale(string $val1, string $val2): int {
    $scale1 = $this->getDecimalPlaces($val1);
    $scale2 = $this->getDecimalPlaces($val2);
    return max($scale1, $scale2);
  }

  /**
   * Get number of decimal places in a numeric string
   */
  private function getDecimalPlaces(string $value): int {
    if (strpos($value, '.') === false) {
      return 0;
    }
    $parts = explode('.', $value);
    return strlen($parts[1]);
  }
}
