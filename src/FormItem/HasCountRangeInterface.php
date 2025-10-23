<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Interface for form items that have selection count constraints
 *
 * Implemented by inputs using CountRangeTrait (e.g., MultiSelect)
 * Useful for detecting min/max selection requirements
 */
interface HasCountRangeInterface {
  public function getMinCount(): int;
  public function setMinCount(int $minCount): self;
  public function getMaxCount(): int;
  public function setMaxCount(int $maxCount): self;
}
