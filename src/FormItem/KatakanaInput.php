<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Coroq\Form\Error\Error;
use Coroq\Form\Error\NotKatakanaError;

class KatakanaInput extends TextInput {
  public function __construct() {
    parent::__construct();
    $this->setMb("CKV");
  }

  /**
   * @param mixed $value
   * @return bool
   */
  private function isKatakana($value): bool {
    return !preg_match("/[^ァ-ヴー]/u", $value);
  }

  public function doValidate($value): ?Error {
    if (!$this->isKatakana($value)) {
      return new NotKatakanaError($this);
    }
    return parent::doValidate($value);
  }

  /**
   * @return string|null
   */
  public function getKatakana(): ?string {
    if ($this->isEmpty()) {
      return null;
    }
    $value = $this->getValue();
    if (!$this->isKatakana($value)) {
      return null;
    }
    return $value;
  }

  /**
   * @return string|null
   */
  public function getParsedValue(): ?string {
    return $this->getKatakana();
  }
}
