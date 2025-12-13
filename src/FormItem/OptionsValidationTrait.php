<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotInOptionsError;

/**
 * Provides options storage and validation for select inputs
 */
trait OptionsValidationTrait {
  /** @var array */
  protected array $options = [];

  public function getOptions(): array {
    return $this->options;
  }

  public function setOptions(array $options): static {
    $this->options = $options;
    return $this;
  }

  /**
   * Validate value exists in options
   *
   * @return Error|null NotInOptionsError if value not in options
   */
  protected function validateInOptions($value): ?Error {
    if (!array_key_exists($value, $this->options)) {
      return new NotInOptionsError($this);
    }
    return null;
  }
}
