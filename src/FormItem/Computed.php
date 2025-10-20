<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

use Closure;

class Computed extends Input {
  /** @var array */
  protected $sourceInputs = [];
  /** @var Closure|null */
  private ?Closure $computation = null;

  public function __construct() {
    parent::__construct();
    $this->setReadOnly(true);
  }

  /**
   * @param Closure $computation Function that receives array of source values and returns computed value
   * @return $this
   */
  public function setComputation(Closure $computation): self {
    $this->computation = $computation;
    return $this;
  }

  /**
   * @return $this
   */
  public function addSourceInput(Input $sourceInput): self {
    $this->sourceInputs[] = $sourceInput;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getValue(): mixed {
    if (!$this->computation) {
      throw new \LogicException("Computation not set");
    }
    foreach ($this->sourceInputs as $sourceInput) {
      if (!$sourceInput->validate()) {
        return null;
      }
    }
    $values = [];
    foreach ($this->sourceInputs as $sourceInput) {
      $values[] = $sourceInput->getValue();
    }
    return ($this->computation)($values);
  }

  /**
   * @return bool
   */
  public function validate(): bool {
    foreach ($this->sourceInputs as $sourceInput) {
      if (!$sourceInput->validate()) {
        return false;
      }
    }
    return parent::validate();
  }
}
