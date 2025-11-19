# Complex

Immutable class representing complex numbers with comprehensive mathematical operations.

## Overview

The `Complex` class provides a complete implementation of complex number arithmetic with support for:
- Basic arithmetic (addition, subtraction, multiplication, division)
- Transcendental functions (exponential, logarithm, power, roots)
- Trigonometric and hyperbolic functions
- Conversion between rectangular (a + bi) and polar (r∠θ) forms
- Epsilon-based equality comparison for floating-point precision

All operations return new instances, maintaining immutability.

## Properties

### real

```php
private(set) float $real
```

The real part of the complex number. Read-only from outside the class.

### imaginary

```php
private(set) float $imaginary
```

The imaginary part of the complex number. Read-only from outside the class.

### magnitude

```php
public ?float $magnitude
```

The magnitude (absolute value or modulus) of the complex number. Automatically computed and cached on first access.

For z = a + bi: |z| = √(a² + b²)

### phase

```php
public ?float $phase
```

The phase (argument) of the complex number in radians. Automatically computed and cached on first access.

For z = a + bi: arg(z) = atan2(b, a)

## Constructor

```php
public function __construct(int|float $real = 0, int|float $imag = 0)
```

Create a new complex number from real and imaginary parts.

**Examples:**
```php
$z1 = new Complex(3, 4);        // 3 + 4i
$z2 = new Complex(5);           // 5 + 0i (real number)
$z3 = new Complex(0, 2);        // 0 + 2i (pure imaginary)
$z4 = new Complex();            // 0 + 0i (zero)
```

## Factory Methods

### i()

```php
public static function i(): self
```

Get the imaginary unit (0 + 1i). Returns a cached instance.

**Example:**
```php
$i = Complex::i();
echo $i;  // "i"
```

### toComplex()

```php
public static function toComplex(int|float|self $value): self
```

Convert a value to a Complex number if it isn't one already.

**Examples:**
```php
$z1 = Complex::toComplex(5);              // 5 + 0i
$z2 = Complex::toComplex(3.14);           // 3.14 + 0i
$z3 = Complex::toComplex(new Complex(2, 3)); // Returns same instance
```

### fromPolar()

```php
public static function fromPolar(float $magnitude, float|Angle $phase): self
```

Create a complex number from polar coordinates (magnitude and phase).

**Parameters:**
- `$magnitude` (float) - The magnitude (r)
- `$phase` (float|Angle) - The phase angle in radians, or an Angle object

**Examples:**
```php
// Create from magnitude and phase (radians)
$z1 = Complex::fromPolar(5, M_PI / 4);

// Create from magnitude and Angle
$z2 = Complex::fromPolar(3, Angle::fromDegrees(60));
```

NB: The `Angle` class is provided by the `Galaxon\Core` namespace (i.e. the `galaxon/core` package, which is a dependency of `galaxon/math`). See: [Angle](https://github.com/mossy2100/PHP-Core/blob/main/docs/Angle.md)

### parse()

```php
public static function parse(string $str): self
```

Parse a complex number from a string. Supports various formats.

**Supported formats:**
- Real numbers: `"5"`, `"-3.14"`
- Pure imaginary: `"i"`, `"j"`, `"3i"`, `"-2.5j"`
- Complex (real first): `"3+4i"`, `"5-2j"`, `"-1+i"`
- Complex (imaginary first): `"4i+3"`, `"-2j+5"`, `"i-1"`
- Whitespace tolerant: `" 3 + 4i "`, `"5 - 2j"`
- Case insensitive: `"I"`, `"J"`

**Examples:**
```php
$z1 = Complex::parse("3+4i");
$z2 = Complex::parse("-2.5j");
$z3 = Complex::parse("i");
$z4 = Complex::parse("4i+3");
```

**Throws:** `DomainException` if the string is invalid.

## Arithmetic Operations

### add()

```php
public function add(int|float|self $other): self
```

Add another value to this complex number.

**Example:**
```php
$z1 = new Complex(3, 4);
$z2 = new Complex(1, 2);
$sum = $z1->add($z2);  // 4 + 6i
```

### sub()

```php
public function sub(int|float|self $other): self
```

Subtract another value from this complex number.

**Example:**
```php
$z1 = new Complex(5, 7);
$z2 = new Complex(2, 3);
$diff = $z1->sub($z2);  // 3 + 4i
```

### mul()

```php
public function mul(int|float|self $other): self
```

Multiply this complex number by another value.

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->mul(2);  // 6 + 8i

$z1 = new Complex(1, 2);
$z2 = new Complex(3, 4);
$product = $z1->mul($z2);  // -5 + 10i
```

### div()

```php
public function div(int|float|self $other): self
```

Divide this complex number by another value.

**Example:**
```php
$z = new Complex(6, 8);
$result = $z->div(2);  // 3 + 4i

$z1 = new Complex(1, 2);
$z2 = new Complex(3, 4);
$quotient = $z1->div($z2);
```

**Throws:** `DomainException` if dividing by zero.

### neg()

```php
public function neg(): self
```

Get the negative of this complex number.

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->neg();  // -3 - 4i
```

### conj()

```php
public function conj(): self
```

Get the complex conjugate (negate the imaginary part).

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->conj();  // 3 - 4i
```

### inv()

```php
public function inv(): self
```

Get the multiplicative inverse (reciprocal).

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->inv();  // 0.12 - 0.16i
```

**Throws:** `DomainException` if the number is zero.

## Transcendental Functions

### exp()

```php
public function exp(): self
```

Calculate e raised to the power of this complex number.

**Example:**
```php
$z = new Complex(0, M_PI);
$result = $z->exp();  // -1 + 0i (Euler's identity)
```

### ln()

```php
public function ln(): self
```

Calculate the natural logarithm.

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->ln();
```

**Throws:** `ValueError` if the number is zero.

### log()

```php
public function log(int|float|self $base): self
```

Calculate logarithm with specified base using change of base formula: log_b(z) = ln(z) / ln(b).

**Example:**
```php
$z = new Complex(8);
$result = $z->log(2);  // 3 + 0i (log₂(8) = 3)
```

**Throws:**
- `DomainException` if base is 0 or 1
- `ValueError` if the number is zero

### pow()

```php
public function pow(int|float|self $exponent): self
```

Raise this complex number to a power.

**Examples:**
```php
$z = new Complex(3, 4);
$result = $z->pow(2);  // -7 + 24i

$i = Complex::i();
$result = $i->pow(2);  // -1 + 0i
```

**Special cases:**
- z^0 = 1 for any z (including 0 by convention)
- 0^(positive) = 0
- 0^(negative or complex) throws `DomainException`

### sqr()

```php
public function sqr(): self
```

Calculate the square of this complex number.

**Example:**
```php
$z = new Complex(3, 4);
$result = $z->sqr();  // -7 + 24i
```

### sqrt()

```php
public function sqrt(): self
```

Calculate the principal square root.

**Example:**
```php
$z = new Complex(-1);
$result = $z->sqrt();  // 0 + 1i
```

### cube()

```php
public function cube(): self
```

Calculate the cube of this complex number.

**Example:**
```php
$z = new Complex(2);
$result = $z->cube();  // 8 + 0i
```

### cbrt()

```php
public function cbrt(): self
```

Calculate the principal cube root.

**Example:**
```php
$z = new Complex(8);
$result = $z->cbrt();  // 2 + 0i
```

### roots()

```php
public function roots(int $n): array
```

Calculate all nth roots of this complex number.

**Parameters:**
- `$n` (int) - The degree of the root (must be positive)

**Returns:**
- `self[]` - Array of n complex roots

**Examples:**
```php
// Cube roots of 1
$z = new Complex(1);
$roots = $z->roots(3);  // Returns 3 roots

// Square roots of -1
$z = new Complex(-1);
$roots = $z->roots(2);  // Returns [i, -i]
```

**Throws:** `DomainException` if n ≤ 0.

## Trigonometric Functions

### sin(), cos(), tan()

```php
public function sin(): self
public function cos(): self
public function tan(): self
```

Calculate trigonometric functions.

**Examples:**
```php
$z = new Complex(1, 1);
$sin = $z->sin();
$cos = $z->cos();
$tan = $z->tan();
```

### sec(), csc(), cot()

```php
public function sec(): self
public function csc(): self
public function cot(): self
```

Calculate secant, cosecant, and cotangent functions.

**Examples:**
```php
$z = new Complex(1, 1);
$sec = $z->sec();  // 1/cos(z)
$csc = $z->csc();  // 1/sin(z)
$cot = $z->cot();  // 1/tan(z) = cos(z)/sin(z)
```

### asin(), acos(), atan()

```php
public function asin(): self
public function acos(): self
public function atan(): self
```

Calculate inverse trigonometric functions.

**Examples:**
```php
$z = new Complex(0.5);
$asin = $z->asin();
$acos = $z->acos();
$atan = $z->atan();
```

### asec(), acsc(), acot()

```php
public function asec(): self
public function acsc(): self
public function acot(): self
```

Calculate inverse secant, cosecant, and cotangent functions.

**Examples:**
```php
$z = new Complex(2);
$asec = $z->asec();  // acos(1/z)
$acsc = $z->acsc();  // asin(1/z)
$acot = $z->acot();  // atan(1/z)
```

## Comparison Methods

### isReal()

```php
public function isReal(): bool
```

Check if the complex number is real (imaginary part is zero).

**Example:**
```php
$z1 = new Complex(5, 0);
var_dump($z1->isReal());  // true

$z2 = new Complex(3, 4);
var_dump($z2->isReal());  // false
```

### equals()

```php
public function equals(mixed $other, float $epsilon = 1E-10): bool
```

Check if this complex number equals another within epsilon tolerance.

**Parameters:**
- `$other` (mixed) - The value to compare with
- `$epsilon` (float) - Tolerance for floating-point comparison (default: 1E-10)

**Returns:**
- `bool` - True if equal within epsilon, false otherwise

**Example:**
```php
$z1 = new Complex(3, 4);
$z2 = new Complex(3, 4);
$z3 = new Complex(3.0000000001, 4);

var_dump($z1->equals($z2));  // true
var_dump($z1->equals($z3));  // true (within default epsilon)
var_dump($z1->equals(5));     // false
```

## Conversion Methods

### toArray()

```php
public function toArray(): array
```

Convert to array [real, imaginary].

**Example:**
```php
$z = new Complex(3, 4);
$array = $z->toArray();  // [3.0, 4.0]
```

### __toString()

```php
public function __toString(): string
```

Convert to string representation.

**Format:**
- Real numbers: `"5"`
- Pure imaginary: `"i"`, `"3i"`, `"-2i"`
- Complex: `"3 + 4i"`, `"3 - 4i"`, `"-3 + 4i"`, `"-3 - 4i"`

**Examples:**
```php
echo new Complex(5);        // "5"
echo new Complex(0, 1);     // "i"
echo new Complex(0, -3);    // "-3i"
echo new Complex(3, 4);     // "3 + 4i"
echo new Complex(3, -4);    // "3 - 4i"
```

## ArrayAccess Interface

Complex numbers can be accessed as arrays:

```php
$z = new Complex(3, 4);

// Read access
echo $z[0];  // 3.0 (real part)
echo $z[1];  // 4.0 (imaginary part)

// Check existence
var_dump(isset($z[0]));  // true
var_dump(isset($z[2]));  // false

// Cannot modify (immutable)
$z[0] = 5;  // Throws LogicException
unset($z[0]);  // Throws LogicException
```

## Usage Examples

### Basic Complex Arithmetic

```php
$z1 = new Complex(3, 4);
$z2 = new Complex(1, 2);

$sum = $z1->add($z2);         // 4 + 6i
$diff = $z1->sub($z2);        // 2 + 2i
$product = $z1->mul($z2);     // -5 + 10i
$quotient = $z1->div($z2);    // 2.2 - 0.4i
```

### Polar Form Conversion

```php
// Create from polar coordinates
$z = Complex::fromPolar(5, M_PI / 4);

// Access polar properties
echo $z->magnitude;  // 5.0
echo $z->phase;      // 0.785... (π/4)
```

### Euler's Identity

```php
// e^(iπ) = -1
$z = new Complex(0, M_PI);
$result = $z->exp();  // -1 + 0i (approximately)
```

### Finding Roots

```php
// Find all cube roots of 1
$one = new Complex(1);
$roots = $one->roots(3);
// Returns 3 complex numbers evenly spaced around unit circle
```

### Complex Trigonometry

```php
$z = new Complex(1, 1);
$sin = $z->sin();
$cos = $z->cos();

// Verify Pythagorean identity: sin²(z) + cos²(z) = 1
$sin2 = $sin->sqr();
$cos2 = $cos->sqr();
$sum = $sin2->add($cos2);  // 1 + 0i
```
