<?php
namespace Coroq\Input;

class Tel extends String {
  public function __construct() {
    parent::__construct();
    $this->setMb("as");
  }
}
