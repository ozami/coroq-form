<?php
namespace Coroq\Input;

class Float extends Number {
  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if ((string)(float)$value != (string)$value) {
      return "err_not_float";
    }
    return parent::doValidate($value);
  }
}
