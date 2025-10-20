<?php
declare(strict_types=1);
namespace Coroq\Form;

use Coroq\Form\FormItem\FormItemInterface;

class Form implements FormInterface {
  private bool $__disabled = false;
  private bool $__required = true;
  private bool $__readonly = false;

  public function getValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      $values[$name] = $item->getValue();
    }
    return $values;
  }

  public function getParsedValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      $values[$name] = $item->getParsedValue();
    }
    return $values;
  }

  public function setValue(mixed $value): self {
    if ($this->__readonly) {
      return $this;
    }
    foreach ($this->getEnabledItems() as $name => $item) {
      $item->setValue($value[$name] ?? '');
    }
    return $this;
  }

  public function clear(): self {
    foreach ($this->getItems() as $item) {
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
      if ($item instanceof FormInterface) {
        $values[$name] = $item->getFilledValue();
      }
      else {
        $values[$name] = $item->getValue();
      }
    }
    return $values;
  }

  public function getFilledParsedValue(): array {
    $values = [];
    foreach ($this->getEnabledItems() as $name => $item) {
      if ($item->isEmpty()) {
        continue;
      }
      if ($item instanceof FormInterface) {
        $values[$name] = $item->getFilledParsedValue();
      }
      else {
        $values[$name] = $item->getParsedValue();
      }
    }
    return $values;
  }

  public function isDisabled(): bool {
    return $this->__disabled;
  }

  public function setDisabled(bool $disabled): self {
    $this->__disabled = boolval($disabled);
    return $this;
  }

  public function isRequired(): bool {
    return $this->__required;
  }

  public function setRequired(bool $required): self {
    $this->__required = $required;
    return $this;
  }

  public function isReadOnly(): bool {
    return $this->__readonly;
  }

  public function setReadOnly(bool $readOnly): self {
    $this->__readonly = $readOnly;
    return $this;
  }

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

  public function getItem(mixed $name): ?FormItemInterface {
    $items = $this->getItems();
    return $items[$name] ?? null;
  }

  /**
   * @return array<FormItemInterface>
   */
  protected function getItems(): array {
    $vars = getPublicProperties($this);
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

function getPublicProperties(mixed $object): array {
  return get_object_vars($object);
}
