<?php
namespace Coroq\Input;

class Tel extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }
}
