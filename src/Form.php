<?php
declare(strict_types=1);
namespace Coroq\Form;

class Form implements FormItemInterface {
  private bool $__disabled = false;

  public function getItem(string $name): FormItemInterface {
    $item = $this->$name ?? null;
    if (!($item instanceof FormItemInterface)) {
      throw new \LogicException("Item '$name' not found.");
    }
    return $item;
  }

  public function getItemIn(string $path): FormItemInterface {
    if (!is_array($path)) {
      $path = explode('/', $path);
    }
    $item = $this;
    foreach ($path as $node) {
      $item = $item->getItem($node);
    }
    return $item;
  }

  public function getValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      $values[$name] = $item->getValue();
    }
    return $values;
  }

  public function setValue(mixed $value): self {
    foreach ($this->getEnabledItems() as $name => $item) {
      $item->setValue($value[$name] ?? '');
    }
    return $this;
  }

  public function clear(): self {
    foreach ($this->getEnabledItems() as $item) {
      $item->clear();
    }
    return $this;
  }

  public function isEmpty(): bool {
    foreach ($this->getEnabledItems() as $item) {
      if (!$item->isEmpty()) {
        return false;
      }
    }
    return true;
  }

  public function getFilledValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      if ($item instanceof Form) {
        $values[$name] = $item->getFilledValue();
      }
      else {
        $values[$name] = $item->getValue();
      }
    }
    return $values;
  }

  public function isDisabled(): bool {
    return $this->__disabled;
  }

  public function setDisabled(bool $disabled): self {
    $this->__disabled = (bool)$disabled;
    return $this;
  }

  public function validate(): bool {
    foreach ($this->getEnabledItems() as $item) {
      $item->validate();
    }
    return !$this->hasError();
  }

  public function getError(): array {
    $errors = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      $errors[$name] = $item->getError();
    }
    return $errors;
  }

  public function hasError(): bool {
    foreach ($this->getEnabledItems() as $item) {
      if ($item->hasError()) {
        return true;
      }
    }
    return false;
  }

  /**
   * @return array<FormItemInterface>
   */
  protected function getItems(): array {
    $vars = get_object_vars($this);
    $items = [];
    foreach ($vars as $name => $var) {
      if ($var instanceof FormItemInterface) {
        $items[$name] = $var;
      }
    }
    return $items;
  }

  /**
   * @return array<FormItemInterface>
   */
  protected function getEnabledItems(): array {
    $enabledItems = [];
    foreach ($this->getItems() as $name => $item) {
      if (!$item->isDisabled()) {
        $enabledItems[$name] = $item;
      }
    }
    return $enabledItems;
  }
}
