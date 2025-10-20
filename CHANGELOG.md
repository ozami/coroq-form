# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.0.0] - TBD

### Changed
- **BREAKING:** Minimum PHP version is now 8.0 (was 7.2)
- **BREAKING:** Namespace changed from `Coroq\Form\Input\*` to `Coroq\Form\FormItem\*`
- **BREAKING:** Input class names updated (e.g., `Input\Text` → `FormItem\TextInput`)
- **BREAKING:** Form API redesigned to use public properties instead of array-like methods
- **BREAKING:** Error handling uses error classes instead of error codes
- **BREAKING:** Removed methods: `setItem()`, `getItemValue()`, `getItemIn()`, `addItem()`, `unsetItem()`, `setItems()`, `getErrorString()`
- **BREAKING:** Removed `Input::setDefaultErrorStringifier()`
- **BREAKING:** Renamed `getFilled()` to `getFilledValue()` for clarity
- **BREAKING:** Renamed `disable()`/`enable()` to `setDisabled(bool)` with fluent interface
- **BREAKING:** Computed inputs now use `setComputation()` closure instead of extending class
- **BREAKING:** Removed form options (path separator)
- Form items are now stored as public properties on Form objects
- `getItem(mixed $name)` added to FormInterface for generic form tree traversal
- Error system redesigned with typed error classes extending abstract `Error` base class

### Added
- FormInterface for polymorphic handling of Form and RepeatingForm
- RepeatingForm class for dynamic lists of form items with factory pattern
- BooleanInput for checkbox/boolean values
- FileInput for file validation (path-based)
- Capability detection interfaces: HasLengthRange, HasNumericRange, HasOptions, HasCountRange
- `getParsedValue()` method for automatic type conversion across all inputs
- `getFilledParsedValue()` method combining filled values with type conversion
- ErrorMessageFormatter class for converting errors to human-readable strings
- BasicErrorMessages class providing default Japanese error messages
- FormItemInterface as common interface for all form items
- Fluent interface for all setter methods (return `$this`)
- Support for read-only form items with `setReadOnly(bool)`
- Specific error classes: EmptyError, InvalidError, TooShortError, TooLongError, TooSmallError, TooLargeError, NotIntegerError, NotNumericError, InvalidEmailError, InvalidUrlError, InvalidDateError, NotKatakanaError, NotInOptionsError, TooFewSelectionsError, TooManySelectionsError, PatternMismatchError, FileNotFoundError, FileTooLargeError, FileTooSmallError, InvalidMimeTypeError, InvalidExtensionError

### Migration
See [MIGRATION.md](MIGRATION.md) for detailed migration guide from 2.1.0 to 3.0.0.

## [2.1.0] - Previous Release

Legacy version with array-based Form API and error code system.
