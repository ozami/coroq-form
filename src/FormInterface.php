<?php
declare(strict_types=1);
namespace Coroq\Form;

use Coroq\Form\FormItem\FormItemInterface;

interface FormInterface extends FormItemInterface {
  public function getValue(): array;

  public function getParsedValue(): array;

  public function getFilledValue(): array;

  public function getFilledParsedValue(): array;

  public function getError(): array;

  public function getItem(mixed $name): ?FormItemInterface;
}

