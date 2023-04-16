<?php
namespace Coroq\Form\Input;

class Url extends Text {
  protected $schemes = ["http", "https"]; // lower case only

  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if (!filter_var($value, FILTER_VALIDATE_URL)) {
      return "err_invalid";
    }
    if ($this->schemes && !in_array(parse_url($value, PHP_URL_SCHEME), $this->schemes)) {
      return "err_invalid_url_scheme";
    }
    return parent::doValidate($value);
  }
}
