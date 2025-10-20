<?php
declare(strict_types=1);
namespace Coroq\Form\FormItem;

/**
 * Interface for form items that have predefined options
 *
 * Implemented by inputs using OptionsValidationTrait (e.g., Select, MultiSelect)
 * Useful for HTML generators to build <select> or radio/checkbox lists
 */
interface HasOptions {
  /**
   * @return array<string|int, string> Array of value => label pairs
   */
  public function getOptions(): array;

  /**
   * @param array<string|int, string> $options Array of value => label pairs
   */
  public function setOptions(array $options): self;
}
