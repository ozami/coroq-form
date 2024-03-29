<?php
namespace Coroq\Form\Input;

use Coroq\Form\Input;

abstract class Computed extends Input {
  /** @var array */
  protected $source_inputs = [];

  public function __construct() {
    parent::__construct();
    $this->setReadOnly(true);
  }

  /**
   * @return $this
   */
  public function addSourceInput(Input $source_input) {
    $this->source_inputs[] = $source_input;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getValue(): mixed {
    foreach ($this->source_inputs as $source_input) {
      if (!$source_input->validate()) {
        return null;
      }
    }
    $values = [];
    foreach ($this->source_inputs as $source_input) {
      $values[] = $source_input->getValue();
    }
    return $this->computeValue($values);
  }

  /**
   * @return bool
   */
  public function validate(): bool {
    foreach ($this->source_inputs as $source_input) {
      if (!$source_input->validate()) {
        return false;
      }
    }
    return parent::validate();
  }

  /**
   * @param array $source_values values of source inputs in order of the source inputs added.
   * @return mixed
   */
  abstract protected function computeValue(array $source_values);
}
