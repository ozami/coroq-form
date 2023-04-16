<?php
namespace Coroq\Form\Input;

class Date extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }

  /**
   * @param mixed $value
   * @return mixed
   */
  public function filter($value) {
    $value = parent::filter($value);
    $time = strtotime($value);
    if ($time !== false) {
      $value = date("Y-m-d", $time);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if (strtotime($value) === false) {
      return "err_invalid";
    }
    return parent::doValidate($value);
  }
}
