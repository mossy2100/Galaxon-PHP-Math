# Galaxon PHP Math

Provides classes for Complex and Rational numbers.

**[License](LICENSE)** | **[Changelog](CHANGELOG.md)** | **[Documentation](docs/)** | **[Coverage Report](https://html-preview.github.io/?url=https://github.com/mossy2100/PHP-Math/blob/main/build/coverage/index.html)**

![PHP 8.4](docs/logo_php8_4.png)

## Description

This package provides immutable classes for working with complex and rational numbers in PHP. Both classes offer exact arithmetic operations (Rational) or high-precision floating-point operations (Complex) with comprehensive mathematical functions.

**Key Features:**
- **Complex numbers** - Full support for complex arithmetic, trigonometry, transcendental functions, and polar/rectangular conversions
- **Rational numbers** - Exact fraction arithmetic using integer ratios, automatic simplification, and overflow detection
- **Immutability** - All operations return new instances
- **Type flexibility** - Methods accept int, float, string, or the respective class type
- **Comprehensive testing** - 100% code coverage with extensive test suites

## Development and Quality Assurance / AI Disclosure

[Claude Chat](https://claude.ai) and [Claude Code](https://www.claude.com/product/claude-code) were used in the development of this package. The core classes were designed, coded, and commented primarily by the author, with Claude providing substantial assistance with code review, suggesting improvements, debugging, and generating tests and documentation. All code was thoroughly reviewed by the author, and validated using industry-standard tools including [PHP_Codesniffer](https://github.com/PHPCSStandards/PHP_CodeSniffer/), [PHPStan](https://phpstan.org/) (to level 9), and [PHPUnit](https://phpunit.de/index.html) to ensure full compliance with [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards and comprehensive unit testing with 100% code coverage. This collaborative approach resulted in a high-quality, thoroughly-tested, and well-documented package delivered in significantly less time than traditional development methods.

![Code Coverage](https://img.shields.io/badge/coverage-100%25-brightgreen)

## Requirements

- PHP ^8.4

## Installation

```bash
composer require galaxon/math
```

## Classes

### [Complex](docs/Complex.md)

Immutable class for complex numbers (a + bi) with support for:
- Basic arithmetic operations (add, subtract, multiply, divide)
- Transcendental functions (exp, ln, log, pow, roots)
- Trigonometric and hyperbolic functions (sin, cos, tan, asin, acos, atan)
- Polar and rectangular form conversions
- Epsilon-based equality comparison
- String parsing and formatting

### [Rational](docs/Rational.md)

Immutable class for rational numbers (p/q) with support for:
- Exact arithmetic using integer ratios (no floating-point errors)
- Automatic reduction to simplest form (e.g., 6/8 → 3/4)
- Conversion to/from floats using continued fractions
- Overflow-safe integer operations
- Comparison operations with mixed types
- String parsing and formatting

## Testing

The library includes comprehensive test coverage:

```bash
# Run all tests
vendor/bin/phpunit

# Run tests for specific class
vendor/bin/phpunit tests/Complex
vendor/bin/phpunit tests/Rational

# Run with coverage (generates HTML report and clover.xml)
composer test
```

## License

MIT License - see [LICENSE](LICENSE) for details

## Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for new functionality
4. Ensure all tests pass
5. Submit a pull request

For questions or suggestions, please [open an issue](https://github.com/mossy2100/PHP-Math/issues).

## Support

- **Issues**: https://github.com/mossy2100/PHP-Math/issues
- **Documentation**: See [docs/](docs/) directory for detailed class documentation
- **Examples**: See test files for comprehensive usage examples

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history and changes.
