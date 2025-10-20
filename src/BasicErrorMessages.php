<?php
declare(strict_types=1);
namespace Coroq\Form;

use Coroq\Form\Error\EmptyError;
use Coroq\Form\Error\InvalidError;
use Coroq\Form\Error\InvalidEmailError;
use Coroq\Form\Error\InvalidUrlError;
use Coroq\Form\Error\InvalidDateError;
use Coroq\Form\Error\NotKatakanaError;
use Coroq\Form\Error\NotIntegerError;
use Coroq\Form\Error\NotNumericError;
use Coroq\Form\Error\NotInOptionsError;
use Coroq\Form\Error\TooShortError;
use Coroq\Form\Error\TooLongError;
use Coroq\Form\Error\TooSmallError;
use Coroq\Form\Error\TooLargeError;
use Coroq\Form\Error\TooFewSelectionsError;
use Coroq\Form\Error\TooManySelectionsError;
use Coroq\Form\Error\PatternMismatchError;
use Coroq\Form\Error\FileNotFoundError;
use Coroq\Form\Error\FileTooLargeError;
use Coroq\Form\Error\FileTooSmallError;
use Coroq\Form\Error\InvalidMimeTypeError;
use Coroq\Form\Error\InvalidExtensionError;
use Coroq\Form\FormItem\Select;
use Coroq\Form\FormItem\MultiSelect;

/**
 * Provides basic Japanese error messages for common validation errors.
 *
 * Users can use this as a base and customize messages as needed:
 *
 * ```php
 * $formatter = new ErrorMessageFormatter();
 * $messages = BasicErrorMessages::get();
 * // Customize specific messages
 * $messages[EmptyError::class] = "Required field";
 * $formatter->setMessages($messages);
 * ```
 */
class BasicErrorMessages {
  /**
   * @return array<string, string|\Closure>
   */
  public static function get(): array {
    return [
      EmptyError::class => function(EmptyError $error) {
        if ($error->formItem instanceof Select || $error->formItem instanceof MultiSelect) {
          return "選択してください";
        }
        return "入力してください";
      },

      InvalidError::class => "正しく入力してください",

      InvalidEmailError::class => "正しいメールアドレスを入力してください",

      InvalidUrlError::class => "正しい URL を入力してください",

      InvalidDateError::class => "正しい日付を入力してください",

      NotKatakanaError::class => "カタカナで入力してください",

      NotIntegerError::class => "整数を入力してください",

      NotNumericError::class => "数値を入力してください",

      NotInOptionsError::class => "選択肢から選んでください",

      TooShortError::class => function(TooShortError $error) {
        return $error->formItem->getMinLength() . " 文字以上で入力してください";
      },

      TooLongError::class => function(TooLongError $error) {
        return $error->formItem->getMaxLength() . " 文字以内で入力してください";
      },

      TooSmallError::class => function(TooSmallError $error) {
        return $error->formItem->getMin() . " 以上の値を入力してください";
      },

      TooLargeError::class => function(TooLargeError $error) {
        return $error->formItem->getMax() . " 以下の値を入力してください";
      },

      TooFewSelectionsError::class => function(TooFewSelectionsError $error) {
        return $error->formItem->getMinCount() . " 個以上選択してください";
      },

      TooManySelectionsError::class => function(TooManySelectionsError $error) {
        return $error->formItem->getMaxCount() . " 個以内で選択してください";
      },

      PatternMismatchError::class => "正しい形式で入力してください",

      FileNotFoundError::class => "ファイルが見つかりません",

      FileTooLargeError::class => function(FileTooLargeError $error) {
        $maxSizeMB = round($error->formItem->getMaxSize() / 1024 / 1024, 1);
        return $maxSizeMB . " MB 以下のファイルを選択してください";
      },

      FileTooSmallError::class => function(FileTooSmallError $error) {
        $minSizeMB = round($error->formItem->getMinSize() / 1024 / 1024, 1);
        return $minSizeMB . " MB 以上のファイルを選択してください";
      },

      InvalidMimeTypeError::class => "許可されていないファイル形式です",

      InvalidExtensionError::class => function(InvalidExtensionError $error) {
        $extensions = implode(', ', $error->formItem->getAllowedExtensions());
        return "許可されている拡張子: " . $extensions;
      },
    ];
  }
}
