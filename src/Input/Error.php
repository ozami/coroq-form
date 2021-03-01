<?php
namespace Coroq\Input;

class Error {
  public $code;
  public $input;
  public $data;

  public function __construct($code, $input, $data = null) {
    $this->code = $code;
    $this->input = $input;
    $this->data = $data;
  }
}
