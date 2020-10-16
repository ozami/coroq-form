<?php
namespace Coroq\Input;

abstract class Computed extends \Coroq\Input {
  /** @var array */
  protected $source_inputs = [];

  public function __construct() {
    parent::__construct();
    $this->setReadOnly(true);
  }

  /**
   * @return $this
   */
  public function addSourceInput(\Coroq\Input $source_input) {
    $this->source_inputs[] = $source_input;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getValue() {
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
   * @param array $source_values values of source inputs in order of the source inputs added.
   * @return mixed
   */
  abstract protected function computeValue(array $source_values);
}
