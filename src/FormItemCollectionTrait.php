<?php
declare(strict_types=1);
namespace Coroq\Form;

/**
 * Shared implementation for form item collection operations
 *
 * Used by both Form and RepeatingForm to avoid code duplication.
 * Requires the using class to implement getItems() from FormInterface.
 */
trait FormItemCollectionTrait {
  /**
   * Get all values as an array
   *
   * @return array<mixed> Array of values
   */
  public function getValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $values[$index] = $item->getValue();
    }
    return $values;
  }

  /**
   * Get all parsed values with type conversion
   *
   * @return array<mixed> Array of parsed values
   */
  public function getParsedValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $values[$index] = $item->getParsedValue();
    }
    return $values;
  }

  /**
   * Get only non-empty values recursively
   *
   * @return array<mixed> Array of values (excluding empty)
   */
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

  /**
   * Get only non-empty parsed values recursively
   *
   * @return array<mixed> Array of parsed values (excluding empty)
   */
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

  /**
   * Clear all items (set to empty)
   *
   * @return static
   */
  public function clear(): static {
    foreach ($this->getItems() as $item) {
      $item->clear();
    }
    return $this;
  }

  /**
   * Check if all items are empty
   *
   * @return bool
   */
  public function isEmpty(): bool {
    foreach ($this->getEnabledItems() as $item) {
      if (!$item->isEmpty()) {
        return false;
      }
    }
    return true;
  }

  /**
   * Validate all enabled items
   *
   * @return bool
   */
  public function validate(): bool {
    // Skip validation if optional and empty
    if (!$this->isRequired() && $this->isEmpty()) {
      return true;
    }

    foreach ($this->getEnabledItems() as $item) {
      $item->validate();
    }
    return !$this->hasError();
  }

  /**
   * Get all errors from items
   *
   * @return array<mixed>
   */
  public function getError(): array {
    $errors = [];
    foreach ($this->getEnabledItems() as $index => $item) {
      $errors[$index] = $item->getError();
    }
    return $errors;
  }

  /**
   * Check if any item has errors
   *
   * @return bool
   */
  public function hasError(): bool {
    foreach ($this->getEnabledItems() as $item) {
      if ($item->hasError()) {
        return true;
      }
    }
    return false;
  }

  /**
   * Get enabled items (non-disabled)
   *
   * @return array<FormItemInterface>
   */
  protected function getEnabledItems(): array {
    $enabledItems = [];
    foreach ($this->getItems() as $index => $item) {
      if (!$item->isDisabled()) {
        $enabledItems[$index] = $item;
      }
    }
    return $enabledItems;
  }
}
