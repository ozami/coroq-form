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
- **BREAKING:** Renamed `TextInput::setNoSpace()` to `setNoWhitespace()` for clarity
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
- `getParsedValue()` method for automatic type conversion across all inputs (returns `null` if value is empty or invalid)
- `getFilledParsedValue()` method combining filled values with type conversion
- ErrorMessageFormatter class for converting errors to human-readable strings
- BasicErrorMessages class providing default Japanese error messages
- FormItemInterface as common interface for all form items
- Fluent interface for all setter methods (return `$this`)
- Support for read-only form items with `setReadOnly(bool)`
- Specific error classes: EmptyError, InvalidError, TooShortError, TooLongError, TooSmallError, TooLargeError, NotIntegerError, NotNumericError, InvalidEmailError, InvalidUrlError, InvalidDateError, NotKatakanaError, NotInOptionsError, TooFewSelectionsError, TooManySelectionsError, PatternMismatchError, FileNotFoundError, FileTooLargeError, FileTooSmallError, InvalidMimeTypeError, InvalidExtensionError
- `setValidator()` method on Input for custom validation closures
- `setErrorCustomizer()` method on AbstractFormItem for transforming error objects
- Derived inputs with `setValueCalculator()` and `setValidator()` for cross-field validation and calculated values

### Fixed
- All string-based inputs (TextInput, EmailInput, UrlInput, TelInput, NumberInput, IntegerInput) now use `mb_scrub()` via StringFilterTrait to replace invalid UTF-8 byte sequences, preventing fatal TypeError from `preg_replace()` returning NULL
- DateInput timezone bug - dates no longer shift by ±1 day due to UTC forcing
- IntegerInput precision bug - now correctly validates integers beyond PHP_INT_MAX/MIN using bcmath, preventing silent rejection of valid large integers
- NumericRangeTrait now uses bcmath with dynamic scale detection for precise comparison of both integers and floats

### Removed
- **BREAKING:** NotKatakanaError and Japanese-specific validation will be moved to `coroq/form-lang-ja` package
- **BREAKING:** BasicErrorMessages (Japanese error messages) will be moved to `coroq/form-lang-ja` package
- **BREAKING:** Japan country-specific form items will be moved to `coroq/form-country-jp` package

### Migration
See [MIGRATION.md](MIGRATION.md) for detailed migration guide from 2.1.0 to 3.0.0.

**Note:** Language-specific and country-specific features are being separated into dedicated packages:
- `coroq/form-lang-ja` - Japanese language support (error messages, Katakana validation)
- `coroq/form-country-jp` - Japan country-specific form items (postal code, prefecture, etc.)

## [2.1.0] - Previous Release

Legacy version with array-based Form API and error code system.
