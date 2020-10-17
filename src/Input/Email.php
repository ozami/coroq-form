<?php
namespace Coroq\Input;

class Email extends Text {
  private $lowercase_domain;

  public function __construct() {
    parent::__construct();
    $this->setMb("as");
    $this->setLowerCaseDomain(true);
  }

  /**
   * @param bool $lowercase_domain
   * @return $this
   */
  public function setLowerCaseDomain($lowercase_domain = true) {
    $this->lowercase_domain = $lowercase_domain;
    return $this;
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value) {
    $value = parent::filter($value);
    if ($this->lowercase_domain) {
      $value = preg_replace_callback('#(.*@)(.*)#', function(array $matches) {
        return $matches[1] . strtolower($matches[2]);
      }, $value);
    }
    return $value;
  }

  /**
   * @param mixed $value
   * @return string|null
   */
  public function doValidate($value) {
    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
      return "err_invalid";
    }
    return parent::doValidate($value);
  }
}
