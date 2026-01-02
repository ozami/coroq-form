<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;

/**
 * Multi-selection input with count constraints
 *
 * Empty value: [] (empty array)
 */
class MultiSelect extends Input implements HasOptionsInterface, HasCountRangeInterface {
  use OptionsValidationTrait;
  use CountRangeTrait;

  /**
   * @return array
   */
  public function getValue(): array {
    $value = parent::getValue();
    return is_array($value) ? $value : [];
  }

  public function isEmpty(): bool {
    return !$this->getValue();
  }

  /**
   * @param mixed $value
   * @return static
   */
  public function setValue($value): static {
    $value = array_values(array_diff((array)$value, ["", null]));
    return parent::setValue($value);
  }

  public function clear(): static {
    return $this->setValue([]);
  }

  /**
   * @return array
   */
  public function getSelectedLabel(): array {
    $options = $this->getOptions();
    $labels = [];
    foreach ($this->getValue() as $value) {
      if (isset($options[$value])) {
        $labels[] = $options[$value];
      }
    }
    return $labels;
  }

  /**
   * @param mixed $value
   * @return Error|null
   */
  public function doValidate($value): ?Error {
    foreach ($value as $v) {
      $optionError = $this->validateInOptions($v);
      if ($optionError !== null) {
        return $optionError;
      }
    }
    $countError = $this->validateCount(count($value));
    if ($countError !== null) {
      return $countError;
    }
    return parent::doValidate($value);
  }

  /**
   * @return array
   */
  public function getParsedValue(): array {
    return $this->getValue();
  }
}
