<?php
namespace Coroq\Form\Input;

class Katakana extends Text {
  public function __construct() {
    parent::__construct();
    $this->setMb("CKV");
  }

  public function doValidate($value) {
    if (preg_match("/[^ァ-ヴー]/u", $value)) {
      return "err_not_katakana";
    }
    return parent::doValidate($value);
  }
}
