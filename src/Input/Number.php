<?php
namespace Coroq\Input;

define("COROQ_INPUT_NUMERIC_MINUS_INF", -INF);

class Number extends \Coroq\Input {
  /** @var int|float */
  protected $min = COROQ_INPUT_NUMERIC_MINUS_INF;
  /** @var int|float */
  protected $max = INF;

  /**
   * @param int|float $min
   * @return Number
   */
  public function setMin($min) {
    $this->min = $min;
    return $this;
  }
  
  /**
   * @param int|float $max
   * @return Number
   */
  public function setMax($max) {
    $this->max = $max;
    return $this;
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function filter($value) {
    $value = parent::filter($value);
    $value = mb_convert_kana($value, "as", "UTF-8");
    $value = preg_replace("/[[:space:]]/u", "", $value);
    return $value;
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if ($value < $this->min) {
      return "err_too_small";
    }
    if ($value > $this->max) {
      return "err_too_large";
    }
    return parent::doValidate($value);
  }
}
