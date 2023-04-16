<?php
namespace Coroq\Form\Input;

class Postal extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }
}
