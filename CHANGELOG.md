# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.2.0] - 2025-12-09

### Changed (Breaking Changes)

- **Complex**: Renamed `equals()` → `equal()` for exact equality (no tolerance)
- **Complex**: Added `approxEqual()` method with configurable relative and absolute tolerances
- **Complex**: Now uses `ApproxEquatable` trait instead of implementing `Equatable` interface
- **Rational**: Renamed `equals()` → `equal()` for exact equality
- **Rational**: Added `approxEqual()` and `approxCompare()` methods with configurable tolerances
- **Rational**: Renamed comparison methods: `isLessThan()` → `lessThan()`, `isGreaterThan()` → `greaterThan()`, `isLessThanOrEqual()` → `lessThanOrEqual()`, `isGreaterThanOrEqual()` → `greaterThanOrEqual()`
- **Rational**: Now uses `ApproxComparable` trait instead of `Comparable` trait
- **Rational**: `compare()` method now performs exact comparison (no epsilon parameter)

### Improved

- Updated comprehensive documentation for comparison methods with detailed examples
- Added new comparison tests for both Complex and Rational classes
- Updated dependencies: PHPStan 2.1.33, PHPUnit 12.5.2, nikic/php-parser 5.7.0, theseer/tokenizer 2.0.1
- Added slevomat/coding-standard 8.25.1 to CodingStandard package
- Enhanced composer scripts with verbose output flags (`phpcbf -vp`)

## [0.1.0] - 2025-01-18

### Added

- **Complex** - Immutable class for complex numbers (a + bi)
  - Constructor and factory methods: `fromPolar()`, `parse()`, `i()`
  - Basic arithmetic: `add()`, `sub()`, `mul()`, `div()`, `neg()`, `conj()`, `inv()`
  - Transcendental functions: `exp()`, `ln()`, `log()`, `pow()`, `sqrt()`, `cbrt()`, `roots()`
  - Trigonometric functions: `sin()`, `cos()`, `tan()`, `sec()`, `csc()`, `cot()`
  - Inverse trigonometric: `asin()`, `acos()`, `atan()`, `asec()`, `acsc()`, `acot()`
  - Hyperbolic functions: `sinh()`, `cosh()`, `tanh()`, `sech()`, `csch()`, `coth()`
  - Inverse hyperbolic: `asinh()`, `acosh()`, `atanh()`, `asech()`, `acsch()`, `acoth()`
  - Properties: `real`, `imaginary`, `magnitude`, `phase` (cached)
  - Polar/rectangular form conversion
  - String parsing with flexible format support
  - Epsilon-based equality comparison
  - ArrayAccess interface for `[0]`/`[1]` access
  - Implements `Equatable` interface

- **Rational** - Immutable class for exact rational number arithmetic
  - Automatic reduction to simplest form (e.g., 6/8 → 3/4)
  - Canonical form (positive denominator, sign in numerator)
  - Basic arithmetic: `add()`, `sub()`, `mul()`, `div()`, `neg()`, `inv()`, `pow()`, `abs()`
  - Rounding methods: `floor()`, `ceil()`, `round()`
  - Comparison: `compare()`, `equals()`, `isLessThan()`, `isGreaterThan()`, etc.
  - Conversion: `toFloat()`, `toInt()`, `__toString()`
  - Factory methods: `parse()`, `toRational()`
  - Float-to-ratio conversion using continued fractions algorithm
  - Cross-cancellation in multiplication to prevent overflow
  - Overflow detection for safe integer arithmetic
  - Implements `Equatable` interface, uses `Comparable` trait

### Requirements
- PHP ^8.4
- galaxon/core package

### Development
- PSR-12 coding standards
- PHPStan level 9 static analysis
- PHPUnit test coverage
- Comprehensive test suite with 100% code coverage
