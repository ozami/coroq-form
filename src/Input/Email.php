<?php
namespace Coroq\Input;

class Email extends String {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      return "err_invalid_email";
    }
    return parent::doValidate($value);
  }
}
