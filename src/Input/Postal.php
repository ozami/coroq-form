<?php
namespace Coroq\Input;

class Postal extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }
}
