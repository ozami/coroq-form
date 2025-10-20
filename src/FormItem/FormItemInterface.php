<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

interface FormItemInterface {
  public function getValue(): mixed;
  public function getParsedValue(): mixed;
  public function setValue(mixed $value): self;
  public function clear(): self;
  public function isEmpty(): bool;
  public function isDisabled(): bool;
  public function setDisabled(bool $disabled): self;
  public function isRequired(): bool;
  public function setRequired(bool $required): self;
  public function isReadOnly(): bool;
  public function setReadOnly(bool $readOnly): self;
  public function validate(): bool;
  public function getError(): mixed;
  public function hasError(): bool;
}
