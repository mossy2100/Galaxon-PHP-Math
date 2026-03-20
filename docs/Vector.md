# Vector

Numeric vector with element-wise arithmetic, dot and cross products, and array-style access.

## Overview

The `Vector` class provides a general-purpose numeric vector with support for:
- Element-wise arithmetic (addition, subtraction, scalar multiplication, scalar division)
- Dot product and cross product operations
- Exact and approximate equality comparison
- Conversion to arrays and matrices
- Array-style element access via the `ArrayAccess` interface

Vectors are directionless (neither row nor column). When converted to a `Matrix`, a vector is treated as a column vector by default.

Size-0 vectors are allowed.

---

## Properties

### data

```php
private array $data
```

The vector data. A list of `int|float` values, not directly accessible from outside the class.

### magnitude

```php
public float $magnitude { get; }
```

The magnitude (Euclidean norm) of the vector, computed on every access.

For v = (x1, x2, ..., xn): ||v|| = sqrt(x1^2 + x2^2 + ... + xn^2)

### size

```php
public int $size { get; }
```

The number of elements in the vector.

---

## Constructor

### \_\_construct()

```php
public function __construct(int $size)
```

Create a new vector with the specified number of elements, all initialised to zero.

**Parameters:**
- `$size` (int) - Number of elements.

**Throws:**
- `DomainException` if size is negative.

**Examples:**
```php
$v1 = new Vector(3);    // [0, 0, 0]
$v2 = new Vector(0);    // [] (empty vector)
```

---

## Factory Methods

### fromArray()

```php
public static function fromArray(array $data): self
```

Create a vector from an array of numbers. Array keys are ignored; values are re-indexed from zero.

**Parameters:**
- `$data` (array<array-key, int|float>) - Array of numbers.

**Returns:**
- `self` - A new vector containing the array values.

**Throws:**
- `InvalidArgumentException` if any element is not a number.

**Examples:**
```php
$v1 = Vector::fromArray([1, 2, 3]);
$v2 = Vector::fromArray([3.14, -1, 0]);
$v3 = Vector::fromArray([]);  // Size-0 vector
```

---

## Vector Operations

### add()

```php
public function add(self $other): self
```

Add another vector to this one, element by element.

**Parameters:**
- `$other` (Vector) - Vector to add.

**Returns:**
- `self` - New vector representing the sum.

**Throws:**
- `LengthException` if vectors have different sizes.

**Examples:**
```php
$v1 = Vector::fromArray([1, 2, 3]);
$v2 = Vector::fromArray([4, 5, 6]);
$sum = $v1->add($v2);  // [5, 7, 9]
```

### sub()

```php
public function sub(self $other): self
```

Subtract another vector from this one, element by element.

**Parameters:**
- `$other` (Vector) - Vector to subtract.

**Returns:**
- `self` - New vector representing the difference.

**Throws:**
- `LengthException` if vectors have different sizes.

**Examples:**
```php
$v1 = Vector::fromArray([5, 7, 9]);
$v2 = Vector::fromArray([1, 2, 3]);
$diff = $v1->sub($v2);  // [4, 5, 6]
```

### mul()

```php
public function mul(int|float $scalar): self
```

Multiply this vector by a scalar.

**Parameters:**
- `$scalar` (int|float) - Number to multiply by.

**Returns:**
- `self` - New vector representing the product.

**Examples:**
```php
$v = Vector::fromArray([1, 2, 3]);
$result = $v->mul(3);  // [3, 6, 9]
```

### div()

```php
public function div(int|float $scalar): self
```

Divide this vector by a scalar.

**Parameters:**
- `$scalar` (int|float) - Number to divide by.

**Returns:**
- `self` - New vector representing the quotient.

**Throws:**
- `DivisionByZeroError` if scalar is zero.

**Examples:**
```php
$v = Vector::fromArray([6, 9, 12]);
$result = $v->div(3);  // [2, 3, 4]
```

### dot()

```php
public function dot(self $other): float
```

Calculate the dot product of this vector with another vector.

**Parameters:**
- `$other` (Vector) - Vector to calculate dot product with.

**Returns:**
- `float` - The dot product.

**Throws:**
- `LengthException` if vectors have different sizes.

**Examples:**
```php
$v1 = Vector::fromArray([1, 2, 3]);
$v2 = Vector::fromArray([4, 5, 6]);
$result = $v1->dot($v2);  // 32.0 (1*4 + 2*5 + 3*6)
```

### cross()

```php
public function cross(self $other): self
```

Calculate the cross product of this vector with another vector. Both vectors must be size 3.

**Parameters:**
- `$other` (Vector) - Vector to calculate cross product with.

**Returns:**
- `self` - New vector representing the cross product.

**Throws:**
- `DomainException` if either vector is not size 3.

**Examples:**
```php
$v1 = Vector::fromArray([1, 0, 0]);
$v2 = Vector::fromArray([0, 1, 0]);
$result = $v1->cross($v2);  // [0, 0, 1]
```

---

## Comparison Methods

### equal()

```php
public function equal(mixed $other): bool
```

Check if this vector exactly equals another value.

Two vectors are equal if they have the same size and all corresponding elements are exactly equal. Returns `false` for non-Vector values.

**Parameters:**
- `$other` (mixed) - The value to compare with.

**Returns:**
- `bool` - True if the vectors are the same size and all elements are exactly equal.

**Examples:**
```php
$v1 = Vector::fromArray([1, 2, 3]);
$v2 = Vector::fromArray([1, 2, 3]);
$v3 = Vector::fromArray([1.0000000001, 2, 3]);

var_dump($v1->equal($v2));  // true (exact match)
var_dump($v1->equal($v3));  // false (not exact)

// Invalid types return false
var_dump($v1->equal('string'));  // false
var_dump($v1->equal(null));      // false
```

### approxEqual()

```php
public function approxEqual(
    mixed $other,
    float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
    float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
): bool
```

Check if this vector approximately equals another value within specified tolerances.

Each pair of corresponding elements is compared using `Floats::approxEqual()`, which checks absolute tolerance first, then relative tolerance. Returns `false` for non-Vector values.

**Parameters:**
- `$other` (mixed) - The value to compare with.
- `$relTol` (float) - Relative tolerance (default: 1e-9).
- `$absTol` (float) - Absolute tolerance (default: PHP_FLOAT_EPSILON).

**Returns:**
- `bool` - True if the vectors are the same size and all elements are approximately equal.

**Throws:**
- `DomainException` if either tolerance is negative.

**Examples:**
```php
$v1 = Vector::fromArray([1, 2, 3]);
$v2 = Vector::fromArray([1.00000001, 2.00000001, 3.00000001]);

// Within default tolerance
var_dump($v1->approxEqual($v2));  // true

// With tight tolerance
var_dump($v1->approxEqual($v2, 1e-15, 1e-15));  // false

// Invalid types return false
var_dump($v1->approxEqual('string'));  // false
```

---

## Conversion Methods

### toArray()

```php
public function toArray(): array
```

Get a copy of the vector data as an array.

**Returns:**
- `list<int|float>` - Array of vector elements.

**Examples:**
```php
$v = Vector::fromArray([1, 2, 3]);
$array = $v->toArray();  // [1, 2, 3]
```

### toMatrix()

```php
public function toMatrix(bool $asRow = false): Matrix
```

Convert this vector to a Matrix.

By default, returns an n x 1 column matrix. If `$asRow` is true, returns a 1 x n row matrix.

**Parameters:**
- `$asRow` (bool) - If true, return a 1 x n row matrix; if false (default), return an n x 1 column matrix.

**Returns:**
- `Matrix` - The matrix representation.

**Examples:**
```php
$v = Vector::fromArray([1, 2, 3]);

// Column matrix (default)
$col = $v->toMatrix();
// [[1],
//  [2],
//  [3]]

// Row matrix
$row = $v->toMatrix(true);
// [[1, 2, 3]]
```

### \_\_toString()

```php
public function __toString(): string
```

Convert the vector to a string representation. Uses the matrix string format via `toMatrix()`.

**Examples:**
```php
$v = Vector::fromArray([1, 2, 3]);
echo $v;
```

---

## ArrayAccess Interface

Vectors can be accessed using bracket syntax:

```php
$v = Vector::fromArray([1, 2, 3]);

// Read access
echo $v[0];  // 1
echo $v[1];  // 2
echo $v[2];  // 3

// Write access
$v[0] = 10;
echo $v[0];  // 10

// Check existence
var_dump(isset($v[0]));  // true
var_dump(isset($v[5]));  // false

// Cannot unset elements
unset($v[0]);  // Throws LogicException
```

### offsetExists()

```php
public function offsetExists(mixed $offset): bool
```

Check if an offset exists. Returns true if the offset is an integer within the valid range.

**Parameters:**
- `$offset` (mixed) - Index to check.

**Returns:**
- `bool` - True if the offset is valid.

### offsetGet()

```php
public function offsetGet(mixed $offset): int|float
```

Get value at an offset.

**Parameters:**
- `$offset` (mixed) - Index to get.

**Returns:**
- `int|float` - The value at the given index.

**Throws:**
- `OutOfRangeException` if offset is out of bounds.

### offsetSet()

```php
public function offsetSet(mixed $offset, mixed $value): void
```

Set value at an offset.

**Parameters:**
- `$offset` (mixed) - Index to set.
- `$value` (mixed) - Value to set.

**Throws:**
- `OutOfRangeException` if offset is outside valid range.
- `InvalidArgumentException` if value is not a number.

### offsetUnset()

```php
public function offsetUnset(mixed $offset): void
```

Unsetting elements is not supported.

**Throws:**
- `LogicException` - Always throws.
