<?php
namespace Coroq\Input;

class MultiSelect extends \Coroq\Input {
  protected $options = [];
  protected $min_count = 0;
  protected $max_count = PHP_INT_MAX;

  public function getOptions() {
    return $this->options;
  }

  public function setOptions(array $options) {
    $this->options = $options;
    return $this;
  }

  public function setMinCount($min_count) {
    $this->min_count = $min_count;
    return $this;
  }

  public function setMaxCount($max_count) {
    $this->max_count = $max_count;
    return $this;
  }
  
  public function isEmpty() {
    return !$this->getValue();
  }

  public function setValue($value) {
    $value = array_diff((array)$value, ["", null]);
    return parent::setValue($value);
  }

  public function clear() {
    return $this->setValue([]);
  }

  public function getSelectedLabel() {
    $options = $this->getOptions();
    $labels = [];
    foreach ($this->getValue() as $value) {
      $labels[] = @$options[$value];
    }
    return array_diff($labels, [null]);
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    $options = $this->getOptions();
    foreach ($value as $v) {
      if (!isset($options[$v])) {
        return "err_invalid";
      }
    }
    $count = count($value);
    if ($count < $this->min_count) {
      return "err_too_few";
    }
    if ($count > $this->max_count) {
      return "err_too_many";
    }
  }
}
