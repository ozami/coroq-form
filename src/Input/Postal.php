<?php
namespace Coroq\Input;

class Postal extends String {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }
}
