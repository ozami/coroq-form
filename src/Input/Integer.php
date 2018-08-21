<?php
namespace Coroq\Input;

class Integer extends Number {
  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if ((string)(int)$value != (string)$value) {
      return "err_not_int";
    }
    return parent::doValidate($value);
  }
}
