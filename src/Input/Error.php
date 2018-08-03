<?php
namespace Coroq\Input;

class Error {
  public $code;
  public $input;
  protected static $converter;

  public function __construct($code, $input) {
    $this->code = $code;
    $this->input = $input;
  }
  
  public function __toString() {
    if (static::$converter) {
      return call_user_func(static::$converter, $this);
    }
    return $this->code;
  }

  public static function setStringConverter(callable $converter) {
    static::$converter = $converter;
  }
}
