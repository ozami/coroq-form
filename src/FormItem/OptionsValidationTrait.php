<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotInOptionsError;

trait OptionsValidationTrait {
  /** @var array */
  protected array $options = [];

  public function getOptions(): array {
    return $this->options;
  }

  public function setOptions(array $options): self {
    $this->options = $options;
    return $this;
  }

  /**
   * Validate value exists in options
   *
   * @return Error|null NotInOptionsError if value not in options
   */
  protected function validateInOptions($value): ?Error {
    if (!isset($this->options[$value])) {
      return new NotInOptionsError($this);
    }
    return null;
  }
}
