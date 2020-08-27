<?php
namespace Coroq\Input;

class Select extends \Coroq\Input {
  /** @var array */
  protected $options = [];

  /**
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * @param array $options
   * @return Select
   */
  public function setOptions(array $options) {
    $this->options = [];
    foreach ($options as $value => $label) {
      $this->options["$value"] = $label;
    }
    return $this;
  }

  /**
   * @param string $value
   * @return string|null
   */
  public function getLabel($value) {
    $options = $this->getOptions();
    return @$options["$value"];
  }

  /**
   * @return string|null
   */
  public function getSelectedLabel() {
    $options = $this->getOptions();
    return @$options[$this->getValue()];
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function filter($value) {
    return "$value";
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    $options = $this->getOptions();
    if (!isset($options[$value])) {
      return "err_invalid";
    }
  }
}
