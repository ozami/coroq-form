<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;

define("COROQ_INPUT_NUMERIC_MINUS_INF", -INF);

trait NumericRangeTrait {
  /** @var int|float */
  protected $min = COROQ_INPUT_NUMERIC_MINUS_INF;
  /** @var int|float */
  protected $max = INF;

  public function getMin(): int|float {
    return $this->min;
  }

  public function setMin(int|float $min): self {
    $this->min = $min;
    return $this;
  }

  public function getMax(): int|float {
    return $this->max;
  }

  public function setMax(int|float $max): self {
    $this->max = $max;
    return $this;
  }

  /**
   * Validate value is within min/max range
   *
   * @return Error|null TooSmallError or TooLargeError if out of range
   */
  protected function validateRange($value): ?Error {
    if ($value < $this->min) {
      return new TooSmallError($this);
    }
    if ($value > $this->max) {
      return new TooLargeError($this);
    }
    return null;
  }
}
