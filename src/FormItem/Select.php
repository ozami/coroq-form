<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;

/**
 * Single-selection dropdown input
 */
class Select extends Input implements HasOptionsInterface {
  use OptionsValidationTrait;

  /**
   * @param array $options
   * @return static
   */
  public function setOptions(array $options): static {
    $this->options = [];
    foreach ($options as $value => $label) {
      $this->options["$value"] = $label;
    }
    return $this;
  }

  /**
   * @return string|null
   */
  public function getSelectedLabel(): ?string {
    $options = $this->getOptions();
    return $options[$this->getValue()] ?? null;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value): string {
    return "$value";
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    $optionError = $this->validateInOptions($value);
    if ($optionError !== null) {
      return $optionError;
    }
    return parent::doValidate($value);
  }
}
