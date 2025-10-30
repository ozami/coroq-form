<?php
declare(strict_types=1);
namespace Coroq\Form;

use Coroq\Form\FormItem\FormItemInterface;

/**
 * Form container for managing groups of form items
 *
 * Items are stored as public properties on the Form object.
 * Supports hierarchical structure - Forms can contain other Forms.
 * Items can be disabled/enabled, required/optional, readonly.
 *
 * Example:
 *   $form = new Form();
 *   $form->email = new EmailInput();
 *   $form->name = new TextInput();
 *   $form->address = new Form();
 *   $form->address->street = new TextInput();
 */
class Form implements FormInterface {
  use FormItemCollectionTrait;

  private bool $__disabled = false;
  private bool $__required = true;
  private bool $__readonly = false;
  private string $__label = "";

  /**
   * Set values from an array
   *
   * @param mixed $value Associative array of values
   * @return self
   */
  public function setValue(mixed $value): self {
    if ($this->__readonly) {
      return $this;
    }
    foreach ($this->getEnabledItems() as $name => $item) {
      $item->setValue($value[$name] ?? null);
    }
    return $this;
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

  public function getLabel(): string {
    return $this->__label;
  }

  public function setLabel(string $label): self {
    $this->__label = $label;
    return $this;
  }

  public function getItem(mixed $name): ?FormItemInterface {
    $items = $this->getItems();
    return $items[$name] ?? null;
  }

  /**
   * @return array<FormItemInterface>
   */
  public function getItems(): array {
    $vars = getPublicProperties($this);
    $items = [];
    foreach ($vars as $name => $var) {
      if ($var instanceof FormItemInterface) {
        $items[$name] = $var;
      }
    }
    return $items;
  }
}

function getPublicProperties(mixed $object): array {
  return get_object_vars($object);
}
