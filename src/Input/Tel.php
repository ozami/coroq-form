<?php
namespace Coroq\Input;

class Tel extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }

  /**
   * @param mixed $value
   * @return string
   */
  public function filter($value) {
    $value = parent::filter($value);
    return preg_replace('#[^0-9]#', "", $value);
  }
}
