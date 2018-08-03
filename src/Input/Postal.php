<?php
namespace Coroq\Input;

class Postal extends String {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    $error = parent::doValidate($value);
    if ($error == "err_invalid") {
      return "err_invalid_postal";
    }
    return $error;
  }
}
