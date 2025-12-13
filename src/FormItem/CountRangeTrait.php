<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\TooFewSelectionsError;
use Coroq\Form\Error\TooManySelectionsError;

/**
 * Provides min/max selection count validation for multi-select inputs
 */
trait CountRangeTrait {
  /** @var int */
  protected int $minCount = 0;
  /** @var int */
  protected int $maxCount = PHP_INT_MAX;

  public function getMinCount(): int {
    return $this->minCount;
  }

  public function setMinCount(int $minCount): static {
    $this->minCount = $minCount;
    return $this;
  }

  public function getMaxCount(): int {
    return $this->maxCount;
  }

  public function setMaxCount(int $maxCount): static {
    $this->maxCount = $maxCount;
    return $this;
  }

  /**
   * Validate selection count is within min/max range
   *
   * @return Error|null TooFewSelectionsError or TooManySelectionsError if out of range
   */
  protected function validateCount(int $count): ?Error {
    if ($count < $this->minCount) {
      return new TooFewSelectionsError($this);
    }
    if ($count > $this->maxCount) {
      return new TooManySelectionsError($this);
    }
    return null;
  }
}
