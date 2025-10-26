<?php
declare(strict_types=1);
namespace Coroq\Form;

use Closure;
use Coroq\Form\FormItem\AbstractFormItem;
use Coroq\Form\FormItem\FormItemInterface;

/**
 * Repeating form for dynamic arrays of form items
 *
 * Uses a factory pattern to create repeated items (e.g., multiple addresses, phone numbers).
 * Supports min/max item count constraints.
 * Items are indexed by integer keys (0, 1, 2, ...).
 *
 * Example:
 *   $emails = (new RepeatingForm())
 *     ->setFactory(fn($i) => new EmailInput())
 *     ->setMinItemCount(1)
 *     ->setMaxItemCount(5);
 *   $emails->setValue(['user@example.com', 'admin@example.com']);
 */
class RepeatingForm extends AbstractFormItem implements FormInterface {
  /** @var array<int, FormItemInterface> Array of form items */
  private array $items = [];

  /** @var Closure|null Factory function to create new items */
  private ?Closure $factory = null;

  private int $minItemCount = 0;
  private int $maxItemCount = PHP_INT_MAX;

  public function __construct() {
  }

  /**
   * Set the factory function for creating new items
   *
   * @param Closure(int): FormItemInterface $factory Function that receives index and returns FormItemInterface
   * @return $this
   */
  public function setFactory(Closure $factory): self {
    $this->factory = $factory;
    $this->recreateItems();
    return $this;
  }

  /**
   * Get all values as an indexed array
   *
   * @return array<int, mixed> Array of values
   */
  public function getValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $values[$index] = $item->getValue();
    }
    return $values;
  }

  public function getParsedValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $values[$index] = $item->getParsedValue();
    }
    return $values;
  }

  public function setValue(mixed $value): self {
    if ($this->isReadOnly()) {
      return $this;
    }

    if (!is_array($value)) {
      $value = [];
    }

    // Reindex to sequential keys
    $value = array_values($value);

    // Recreate items to minItemCount
    $this->recreateItems();

    // Set values on existing items and create additional items as needed
    foreach ($value as $i => $itemValue) {
      if ($i >= $this->maxItemCount) {
        break;
      }

      if (!isset($this->items[$i])) {
        $this->items[$i] = ($this->factory)($i);
      }

      $this->items[$i]->setValue($itemValue);
    }

    return $this;
  }

  public function clear(): self {
    foreach ($this->items as $item) {
      $item->clear();
    }
    return $this;
  }

  public function isEmpty(): bool {
    if (count($this->getEnabledItems()) === 0) {
      return true;
    }
    foreach ($this->getEnabledItems() as $item) {
      if (!$item->isEmpty()) {
        return false;
      }
    }
    return true;
  }

  public function getFilledValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      if ($item instanceof FormInterface) {
        $values[$index] = $item->getFilledValue();
      }
      else {
        $values[$index] = $item->getValue();
      }
    }
    return $values;
  }

  public function getFilledParsedValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      if ($item instanceof FormInterface) {
        $values[$index] = $item->getFilledParsedValue();
      }
      else {
        $values[$index] = $item->getParsedValue();
      }
    }
    return $values;
  }

  public function validate(): bool {
    // Skip validation if optional and empty
    if (!$this->isRequired() && $this->isEmpty()) {
      return true;
    }

    // Validate each item - factory controls required/optional per item
    foreach ($this->getEnabledItems() as $item) {
      $item->validate();
    }

    return !$this->hasError();
  }

  /**
   * Get all errors from child items
   *
   * @return array<int, mixed>
   */
  public function getError(): array {
    $errors = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $errors[$index] = $item->getError();
    }
    return $errors;
  }

  /**
   * Check if any child item has errors
   */
  public function hasError(): bool {
    foreach ($this->getEnabledItems() as $item) {
      if ($item->hasError()) {
        return true;
      }
    }
    return false;
  }

  public function setMinItemCount(int $count): self {
    $this->minItemCount = $count;
    if ($this->factory) {
      $this->recreateItems();
    }
    return $this;
  }

  public function getMinItemCount(): int {
    return $this->minItemCount;
  }

  public function setMaxItemCount(int $count): self {
    $this->maxItemCount = $count;
    if ($this->factory) {
      $this->recreateItems();
    }
    return $this;
  }

  public function getMaxItemCount(): int {
    return $this->maxItemCount;
  }

  public function getItem(mixed $index): ?FormItemInterface {
    if (!is_int($index)) {
      return null;
    }
    return $this->items[$index] ?? null;
  }

  /**
   * @return array<int, FormItemInterface>
   */
  public function getItems(): array {
    return $this->items;
  }

  public function addItem(?string $value = null): FormItemInterface {
    if (!$this->factory) {
      throw new \LogicException("Factory not set");
    }
    $index = count($this->items);
    $item = ($this->factory)($index);
    if ($value !== null) {
      $item->setValue($value);
    }
    $this->items[] = $item;
    return $item;
  }

  public function count(): int {
    return count($this->items);
  }

  /**
   * @return array<int, FormItemInterface>
   */
  protected function getEnabledItems(): array {
    $enabledItems = [];
    foreach ($this->items as $index => $item) {
      if (!$item->isDisabled()) {
        $enabledItems[$index] = $item;
      }
    }
    return $enabledItems;
  }

  /**
   * Recreate all child items from factory
   *
   * Destroys existing items and creates new ones based on minItemCount/maxItemCount.
   * All values will be lost.
   *
   * @return void
   */
  private function recreateItems(): void {
    if (!$this->factory) {
      throw new \LogicException("Factory not set");
    }

    // Recreate items to meet minItemCount
    $this->items = [];
    for ($i = 0; $i < $this->minItemCount; $i++) {
      $item = ($this->factory)($i);
      $this->items[$i] = $item;
    }
  }
}
