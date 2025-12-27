# FloatWithError

Represents a floating-point number with tracked absolute error. This class propagates numerical errors through arithmetic operations, providing a way to track precision loss in calculations.

## Overview

When performing floating-point arithmetic, rounding errors accumulate with each operation. `FloatWithError` automatically tracks these errors using standard error propagation formulas, giving you insight into the reliability of computed values.

**Key features:**
- Automatic error estimation for inexact floats
- Error propagation through arithmetic operations
- Relative and absolute error tracking
- Immutable operations (returns new instances)

## Constructor

```php
public function __construct(float $value, ?float $error = null)
```

Creates a new FloatWithError instance.

**Parameters:**
- `$value` (float) - The numeric value
- `$error` (float|null) - The absolute error. If `null`, estimated from float precision using ULP

**Automatic Error Estimation:**

When `$error` is not provided:
- **Exact integers** (within ±2⁵³): error = 0.0
- **Other floats**: error = `ulp($value) * 0.5` (half the spacing to next representable float)

**Examples:**

```php
// Exact integer - zero error
$exact = new FloatWithError(42);
// value: 42.0, absoluteError: 0.0

// Non-exact float - automatic error estimation
$pi = new FloatWithError(3.14159);
// value: 3.14159, absoluteError: ~3.5e-16

// Explicit error for measurement uncertainty
$measurement = new FloatWithError(100.0, 0.5);
// value: 100.0, absoluteError: 0.5
```

## Properties

### value

```php
private(set) float $value
```

The numeric value. Read-only from outside the class.

### absoluteError

```php
private(set) float $absoluteError
```

The absolute error (uncertainty) in the value. Read-only from outside the class.

### relativeError

```php
public float $relativeError { get }
```

The relative error (absolute error divided by value). Computed property.

**Behavior:**
- Returns `absoluteError / abs(value)` for non-zero values
- Returns `0.0` if both value and error are zero
- Returns `INF` if value is zero but error is non-zero

**Examples:**

```php
$num = new FloatWithError(100.0, 1.0);
echo $num->relativeError;  // 0.01 (1%)

$zero = new FloatWithError(0.0, 0.0);
echo $zero->relativeError;  // 0.0

$uncertain = new FloatWithError(0.0, 1.0);
echo $uncertain->relativeError;  // INF
```

## Arithmetic Methods

All arithmetic methods return new `FloatWithError` instances without modifying the original.

### add()

```php
public function add(float|FloatWithError $other): self
```

Add another value to this one.

**Parameters:**
- `$other` (float|FloatWithError) - The value to add. If a float is provided, it will be automatically wrapped in a FloatWithError instance.

**Error propagation:** Absolute errors add (worst-case scenario where errors align).

**Formula:** `error_sum = error_a + error_b + rounding_error`

**Examples:**

```php
// Adding two FloatWithError instances
$a = new FloatWithError(10.0, 0.1);
$b = new FloatWithError(20.0, 0.2);

$sum = $a->add($b);
// value: 30.0
// absoluteError: ≥ 0.3 (0.1 + 0.2 + rounding)

// Adding an int directly (exact value)
$c = new FloatWithError(10.0, 0.1);
$sum2 = $c->add(5);
// value: 15.0
// absoluteError: ≥ 0.1 (only from $c)

// Adding a float directly (automatic error estimation)
$d = new FloatWithError(10.0, 0.1);
$sum3 = $d->add(3.14);
// value: 13.14
// absoluteError: > 0.1 (includes automatic error from 3.14)
```

### sub()

```php
public function sub(float|FloatWithError $other): self
```

Subtract another value from this one.

**Parameters:**
- `$other` (float|FloatWithError) - The value to subtract. If a float is provided, it will be automatically wrapped in a FloatWithError instance.

**Error propagation:** Absolute errors add (errors don't cancel in subtraction).

**Formula:** `error_diff = error_a + error_b + rounding_error`

**Examples:**

```php
// Subtracting two FloatWithError instances
$a = new FloatWithError(50.0, 0.5);
$b = new FloatWithError(20.0, 0.2);

$diff = $a->sub($b);
// value: 30.0
// absoluteError: ≥ 0.7 (0.5 + 0.2 + rounding)

// Subtracting an int directly (exact value)
$c = new FloatWithError(50.0, 0.5);
$diff2 = $c->sub(20);
// value: 30.0
// absoluteError: ≥ 0.5 (only from $c)

// Subtracting a float directly (automatic error estimation)
$d = new FloatWithError(50.0, 0.5);
$diff3 = $d->sub(20.14);
// value: 29.86
// absoluteError: > 0.5 (includes automatic error from 20.14)
```

**Note:** Subtracting nearly equal values (catastrophic cancellation) can lead to very high relative error:

```php
$a = new FloatWithError(1.0000, 0.0001);
$b = new FloatWithError(0.9999, 0.0001);

$diff = $a->sub($b);
// value: 0.0001
// absoluteError: 0.0002
// relativeError: 2.0 (200%!) - most precision lost
```

### neg()

```php
public function neg(): self
```

Negate this number.

**Error propagation:** Error magnitude unchanged (sign of error doesn't affect magnitude).

**Example:**

```php
$num = new FloatWithError(42.0, 1.0);
$neg = $num->neg();
// value: -42.0
// absoluteError: 1.0 (unchanged)
```

### mul()

```php
public function mul(float|FloatWithError $other): self
```

Multiply this value by another.

**Parameters:**
- `$other` (float|FloatWithError) - The value to multiply by. If a float is provided, it will be automatically wrapped in a FloatWithError instance.

**Error propagation:** Relative errors add.

**Formula:** `rel_error_product = rel_error_a + rel_error_b`

**Examples:**

```php
// Multiplying two FloatWithError instances
$a = new FloatWithError(10.0, 0.1);  // 1% relative error
$b = new FloatWithError(20.0, 0.2);  // 1% relative error

$product = $a->mul($b);
// value: 200.0
// relativeError: ~2% (1% + 1%)
// absoluteError: ~4.0 (2% of 200)

// Multiplying by an int directly (exact value)
$c = new FloatWithError(10.0, 0.1);
$product2 = $c->mul(5);
// value: 50.0
// relativeError: ~1% (only from $c)
// absoluteError: ≥ 0.5

// Multiplying by a float directly (automatic error estimation)
$d = new FloatWithError(10.0, 0.1);
$product3 = $d->mul(2.5);
// value: 25.0
// absoluteError: > 0.25 (includes automatic error from 2.5)
```

### div()

```php
public function div(float|FloatWithError $other): self
```

Divide this value by another.

**Parameters:**
- `$other` (float|FloatWithError) - The value to divide by. If a float is provided, it will be automatically wrapped in a FloatWithError instance.

**Error propagation:** Relative errors add.

**Formula:** `rel_error_quotient = rel_error_a + rel_error_b`

**Throws:** `DivisionByZeroError` if dividing by zero

**Examples:**

```php
// Dividing two FloatWithError instances
$a = new FloatWithError(100.0, 1.0);  // 1% relative error
$b = new FloatWithError(10.0, 0.1);   // 1% relative error

$quotient = $a->div($b);
// value: 10.0
// relativeError: ~2% (1% + 1%)
// absoluteError: ~0.2 (2% of 10)

// Dividing by an int directly (exact value)
$c = new FloatWithError(100.0, 1.0);
$quotient2 = $c->div(10);
// value: 10.0
// relativeError: ~1% (only from $c)
// absoluteError: ≥ 0.1

// Dividing by a float directly (automatic error estimation)
$d = new FloatWithError(100.0, 1.0);
$quotient3 = $d->div(2.5);
// value: 40.0
// absoluteError: > 0.4 (includes automatic error from 2.5)
```

### inv()

```php
public function inv(): self
```

Calculate the multiplicative inverse (1/x).

**Error propagation:** Relative error unchanged.

**Formula:** `rel_error_inverse = rel_error_x`

**Throws:** `DivisionByZeroError` if inverting zero

**Example:**

```php
$num = new FloatWithError(4.0, 0.04);  // 1% relative error

$inverse = $num->inv();
// value: 0.25
// relativeError: ~1% (unchanged)
// absoluteError: ~0.0025 (1% of 0.25)
```

## Conversion Methods

### __toString()

```php
public function __toString(): string
```

Convert to string representation showing value, error, and precision.

**Format:** `"value ± absoluteError (relativeError%)"`

**Example:**

```php
$num = new FloatWithError(100.0, 1.0);
echo $num;
// "100 ± 1.00e+00 (1.00%)"

$exact = new FloatWithError(42);
echo $exact;
// "42 ± 0.00e+00 (0.00%)"
```

## Error Propagation Formulas

Understanding how errors propagate is key to using this class effectively:

### Addition and Subtraction

**Absolute errors add:**
- `δ(a + b) = δa + δb`
- `δ(a - b) = δa + δb`

Errors don't cancel in subtraction because they represent uncertainty ranges.

### Multiplication and Division

**Relative errors add:**
- `δ(a × b) / (a × b) = δa/a + δb/b`
- `δ(a / b) / (a / b) = δa/a + δb/b`

This means multiplication and division preserve relative precision.

### Special Case: Powers

For repeated multiplication:
- `δ(x^n) / x^n = n × (δx / x)`

Relative error multiplies by the exponent!

## Usage Examples

### Basic Calculation with Error Tracking

```php
// Measurement with uncertainty
$length = new FloatWithError(10.0, 0.1);  // 10.0 ± 0.1 cm
$width = new FloatWithError(5.0, 0.05);   // 5.0 ± 0.05 cm

// Calculate area
$area = $length->mul($width);
echo $area;
// "50 ± 7.50e-01 (1.50%)"

// Relative errors: 1% + 1% = 2%
// Absolute error: 2% of 50 = 1.0 (approximately)
```

### Chain of Operations

```php
$a = new FloatWithError(10.0, 0.1);
$b = new FloatWithError(5.0, 0.05);
$c = new FloatWithError(2.0, 0.02);

// (a + b) / c
$result = $a->add($b)->div($c);

echo $result;
// Shows accumulated error through operations
```

### Exact Integer Arithmetic

```php
// Exact integers have zero error
$a = new FloatWithError(10);
$b = new FloatWithError(3);

$sum = $a->add($b);      // 13, exact
$product = $a->mul($b);  // 30, exact
$exact = $a->div($b);    // 3.333..., not exact (rounding error added)

echo "Sum: " . $sum->absoluteError;      // 0.0
echo "Product: " . $product->absoluteError;  // 0.0
echo "Quotient: " . $exact->absoluteError;   // > 0 (rounding occurred)
```

## Best Practices

1. **Start with accurate measurements**: Garbage in, garbage out. Initial errors determine final precision.

2. **Monitor relative error**: Check `relativeError` to know when results become unreliable. Values approaching or exceeding 100% indicate severe precision loss.

3. **Prefer multiplication over division when possible**: Both propagate errors the same way, but multiplication is often more numerically stable, and more likely to produce exact results.

4. **Use exact integers when appropriate**: They maintain zero error through addition, subtraction, and multiplication.

5. **Document measurement uncertainties**: When creating from measurements, always provide explicit error estimates.

## See Also

- `Floats::ulp()` - Calculate Unit in Last Place for error estimation
- `Floats::isExactInt()` - Check if a float is exactly representable
- Error analysis theory and numerical methods texts
