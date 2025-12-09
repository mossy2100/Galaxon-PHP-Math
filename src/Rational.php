<?php

declare(strict_types=1);

namespace Galaxon\Math;

use DomainException;
use Galaxon\Core\Floats;
use Galaxon\Core\Integers;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxComparable;
use OverflowException;
use Override;
use RangeException;
use Stringable;
use TypeError;

/**
 * A rational number, represented as a ratio of two PHP integers, signifying the numerator and denominator.
 *
 * These values are maintained in a canonical form:
 * - 0 is represented as 0/1.
 * - The denominator is always positive. Thus, the sign of the rational is stored as the sign of the numerator.
 * - The fraction is reduced to its simplest form (e.g. 9/12 will be automatically reduced to 3/4).
 *
 * NB: The valid range of the absolute value of a Rational is 1/PHP_INT_MAX to PHP_INT_MAX/1.
 * Therefore, neither the numerator nor the denominator can be PHP_INT_MIN.
 * The reason is that PHP_INT_MIN equals -(PHP_INT_MAX + 1), i.e. it can't be negated without overflowing.
 * Allowing the numerator or the denominator to equal PHP_INT_MIN therefore complicates negation, reciprocal,
 * subtraction, and simplification methods.
 * So, while it's technically possible, supporting this edge case inflates the code for little gain.
 */
final class Rational implements Stringable
{
    use ApproxComparable;

    // region Properties

    /**
     * The numerator.
     *
     * @var int
     */
    private(set) int $num;

    /**
     * The denominator.
     *
     * @var int
     */
    private(set) int $den;

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param int|float $num The numerator. Defaults to 0.
     * @param int|float $den The denominator. Defaults to 1.
     * @throws DomainException If the denominator is zero.
     * @throws DomainException If a float argument is infinite or NaN.
     * @throws RangeException If the value is outside the valid convertible range.
     */
    public function __construct(int|float $num = 0, int|float $den = 1)
    {
        // Check for zero denominator.
        if (Numbers::equal($den, 0)) {
            throw new DomainException('The denominator cannot be zero.');
        }

        // Check for infinite or NaN.
        if ((is_float($num) && !is_finite($num)) || (is_float($den) && !is_finite($den))) {
            throw new DomainException('Cannot convert an infinity or NaN to a rational number.');
        }

        // Initialize result variables.
        $num2 = 0;
        $den2 = 1;

        // Check to see if either argument was provided as a float, but could have been an int.
        // This might enable a call to simplify(), which is preferable to floatToRational().
        if (is_float($num)) {
            $iNum = Floats::tryConvertToInt($num);
            if ($iNum !== null) {
                $num = $iNum;
            }
        }
        if (is_float($den)) {
            $iDen = Floats::tryConvertToInt($den);
            if ($iDen !== null) {
                $den = $iDen;
            }
        }

        // Check if we got two valid integers.
        $convertFloat = false;
        if (is_int($num) && is_int($den)) {
            try {
                // Simplify the ratio.
                [$num2, $den2] = self::simplify($num, $den);
            } catch (RangeException) {
                // If either the resulting numerator or denominator is out of range, try converting from float.
                $convertFloat = true;
            }
        } else {
            $convertFloat = true;
        }

        // Convert from float if necessary.
        if ($convertFloat) {
            [$num2, $den2] = self::floatToRatio($num / $den);
        }
//        var_dump($num2, $den2);

        // Set the properties.
        $this->num = $num2;
        $this->den = $den2;
    }

    // endregion

    // region Factory methods

    /**
     * Parse a string into a rational number.
     *
     * It will accept string values of the following form:
     * - int, e.g. "123", "-456"
     * - float, e.g. "123.456", "-456.789"
     * - fraction, e.g. "1/2", "-3/4"
     *
     * If the string represents a float, it will be converted to the closest rational number if its within the valid
     * range.
     *
     * The input string is trimmed, including fraction parts. Therefore, the following examples are all allowed:
     * - " 123", "-456 ", etc.
     * - " 123.456", "-456.789 ", etc.
     * - " 1/2", "-3/4 ", " 5 / 6", etc.
     *
     * @param string $s The string to parse.
     * @return self The parsed rational number.
     * @throws DomainException If the string cannot be parsed into a rational number.
     * @throws RangeException If the string represents a number that is outside the valid convertible range.
     */
    public static function parse(string $s): self
    {
        // Check for a string that looks like an integer.
        $n = filter_var($s, FILTER_VALIDATE_INT);
        if (is_int($n)) {
            return new self($n);
        }

        // Check for a string that looks like a float.
        $n = filter_var($s, FILTER_VALIDATE_FLOAT);
        if (is_float($n)) {
            return new self($n);
        }

        // Check for a string that looks like a fraction (int/int).
        $parts = explode('/', $s);
        if (count($parts) === 2) {
            $n = filter_var($parts[0], FILTER_VALIDATE_INT);
            $d = filter_var($parts[1], FILTER_VALIDATE_INT);
            if (is_int($n) && is_int($d)) {
                return new self($n, $d);
            }
        }

        throw new DomainException("Invalid rational number: $s");
    }

    /**
     * Convert a number or string into a Rational if it isn't one already.
     *
     * This serves as a helper method used by many of the arithmetic methods in this class, but may have utility
     * as a general-purpose conversion method elsewhere.
     *
     * @param int|float|string|self $value The number to convert.
     * @return self The equivalent Rational.
     * @throws DomainException If the number is NaN or infinite, or the input string doesn't represent a valid rational.
     * @throws RangeException If the value is outside the valid convertible range.
     */
    public static function toRational(int|float|string|self $value): self
    {
        // Check for Rational.
        if ($value instanceof self) {
            return $value;
        }

        // Check for string.
        if (is_string($value)) {
            return self::parse($value);
        }

        // Must be int or float.
        return new self($value);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert the rational number to a float.
     *
     * @return float The equivalent float.
     */
    public function toFloat(): float
    {
        return $this->num / $this->den;
    }

    /**
     * Convert the rational number to an int.
     *
     * @return int The closest integer, rounding towards zero.
     */
    public function toInt(): int
    {
        return intdiv($this->num, $this->den);
    }

    /**
     * Convert the rational number to a string. (Stringable implementation.)
     *
     * @return string The string representation of the rational number.
     */
    #[Override]
    public function __toString(): string
    {
        return $this->num . ($this->den === 1 ? '' : '/' . $this->den);
    }

    // endregion

    // region Comparison methods

    /**
     * Compare a rational number with another number.
     *
     * @param mixed $other The number to compare with.
     * @return int Returns -1 if this < other, 0 if equal, 1 if this > other.
     * @throws TypeError If the value being compared has an invalid type.
     */
    #[Override]
    public function compare(mixed $other): int
    {
        // Check the type is comparable.
        if (!Numbers::isNumber($other) && !$other instanceof self) {
            throw new TypeError('Can only compare Rational numbers with values of type int, float, or Rational.');
        }

        // Convert int to Rational, if it can be done without calling floatToRational().
        if (is_int($other) && $other > PHP_INT_MIN) {
            $other = new self($other);
        }

        // Convert float to Rational if it can be done without calling floatToRational().
        if (is_float($other)) {
            $iOther = Floats::tryConvertToInt($other);
            if ($iOther !== null && $iOther > PHP_INT_MIN) {
                $other = new self($iOther);
            }
        }

        // If $other is still an int or float, it's quicker (and sufficiently precise) to compare $this and $other as
        // floats than it would be to call floatToRational() and compare two Rationals.
        if (!$other instanceof self) {
            $left = $this->toFloat();
            $right = (float)$other;
        } else {
            /** @var self $other */
            if ($this->den === $other->den) {
                // If the denominators are equal, just compare numerators.
                $left = $this->num;
                $right = $other->num;
            } else {
                try {
                    // Cross multiply: compare a*d with b*c for a/b vs c/d.
                    $left = Integers::mul($this->num, $other->den);
                    $right = Integers::mul($this->den, $other->num);
                } catch (OverflowException) {
                    // In case of overflow, compare equivalent floating point values.
                    // NB: This could produce a result of 0 (equal) if two different rationals convert to the same
                    // float, which is possible for values with a magnitude greater than or equal to 2^53 (64-bit
                    // platforms only).
                    $left = $this->toFloat();
                    $right = $other->toFloat();
                }
            }
        }

        // The spaceship operator only guarantees sign, not specific values. Normalize to -1, 0, or 1 for
        // predictable behavior used by other comparison methods.
        return Numbers::sign($left <=> $right);
    }

    /**
     * Check if this rational number equals another number.
     *
     * @param mixed $other The number to compare with.
     * @return bool True if equal, false otherwise.
     */
    public function equal(mixed $other): bool
    {
        try {
            // This will throw on invalid type.
            return $this->compare($other) === 0;
        } catch (TypeError) {
            return false;
        }
    }

    /**
     * Check if this Rational approximately equals another one, within specified tolerances.
     *
     * This method uses a combined absolute and relative tolerance approach, matching the algorithm in
     * Floats::approxEqual(). The absolute tolerance is checked first (useful for comparisons near zero), and if
     * that fails, the relative tolerance is checked (which scales with the magnitude of the values).
     *
     * To compare using only absolute difference, set $relTol to 0.0.
     * To compare using only relative difference, set $absTol to 0.0.
     *
     * Implementations should return false for incompatible types rather than throwing TypeError, to match the
     * behavior of equal().
     *
     * @param mixed $other The int, float, or Rational to compare with.
     * @param float $relTol The maximum allowed relative difference (default: 1e-9).
     * @param float $absTol The maximum allowed absolute difference (default: PHP_FLOAT_EPSILON).
     * @return bool True if the values are equal within the given tolerances, false otherwise.
     * @see Floats::approxEqual() For the tolerance algorithm details.
     */
    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        // Get the other value as a float.
        if (is_int($other)) {
            $other = (float)$other;
        } elseif ($other instanceof self) {
            $other = $other->toFloat();
        }

        // If the other value's type is not float at this point, the values are inequal.
        if (!is_float($other)) {
            return false;
        }

        // Compare as floats.
        return Floats::approxEqual($this->toFloat(), $other, $relTol, $absTol);
    }

    // endregion

    // region Arithmetic operations

    /**
     * Calculate the negative of this rational number.
     *
     * @return self A new rational number representing the negative.
     */
    public function neg(): self
    {
        return new self(-$this->num, $this->den);
    }

    /**
     * Add another value to this rational number.
     *
     * @param int|float|self $other The value to add.
     * @return self A new rational number representing the sum.
     * @throws OverflowException If the result overflows an integer.
     */
    public function add(int|float|self $other): self
    {
        $other = self::toRational($other);

        // (a/b) + (c/d) = (ad + bc) / (bd)
        $f = Integers::mul($this->num, $other->den);
        $g = Integers::mul($this->den, $other->num);
        $h = Integers::add($f, $g);
        $k = Integers::mul($this->den, $other->den);

        return new self($h, $k);
    }

    /**
     * Subtract another value from this rational number.
     *
     * @param int|float|self $other The value to subtract.
     * @return self A new rational number representing the difference.
     * @throws OverflowException If the result overflows an integer.
     */
    public function sub(int|float|self $other): self
    {
        $other = self::toRational($other);
        return $this->add($other->neg());
    }

    /**
     * Calculate the reciprocal of this rational number.
     *
     * @return self A new rational number representing the reciprocal.
     */
    public function inv(): self
    {
        // Guard.
        if ($this->num === 0) {
            throw new DomainException('Cannot take reciprocal of zero.');
        }

        // Preserve sign: if num is negative, swap and negate.
        return $this->num > 0
            ? new self($this->den, $this->num)
            : new self(-$this->den, -$this->num);
    }

    /**
     * Multiply this rational number by another value.
     *
     * @param int|float|self $other The value to multiply by.
     * @return self A new rational number representing the product.
     * @throws OverflowException If the result overflows an integer.
     */
    public function mul(int|float|self $other): self
    {
        $other = self::toRational($other);

        // Cross-cancel before multiplying: (a/b) * (c/d)
        // Cancel gcd(a,d) from a and d
        // Cancel gcd(b,c) from b and c
        $gcd1 = Integers::gcd($this->num, $other->den);
        $gcd2 = Integers::gcd($this->den, $other->num);

        $a = intdiv($this->num, $gcd1);
        $b = intdiv($this->den, $gcd2);
        $c = intdiv($other->num, $gcd2);
        $d = intdiv($other->den, $gcd1);

        // Now multiply the reduced terms: (a/b) * (c/d) = ac/bd
        $h = Integers::mul($a, $c);
        $k = Integers::mul($b, $d);

        return new self($h, $k);
    }

    /**
     * Divide this rational number by another value.
     *
     * @param int|float|self $other The value to divide by.
     * @return self A new rational number representing the quotient.
     * @throws DomainException If dividing by zero.
     * @throws OverflowException If the result overflows an integer.
     */
    public function div(int|float|self $other): self
    {
        // Guard.
        $other = self::toRational($other);
        if ($other->num === 0) {
            throw new DomainException('Cannot divide by zero.');
        }

        return $this->mul($other->inv());
    }

    /**
     * Raise this rational number to an integer power.
     *
     * @param int $exponent The integer exponent.
     * @return self A new rational number representing the result.
     * @throws DomainException If raising zero to a negative power.
     * @throws OverflowException If the result overflows an integer.
     */
    public function pow(int $exponent): self
    {
        // Any number to the power of 0 is 1, including 0.
        // 0^0 can be considered undefined, but many programming languages (including PHP) return 1.
        if ($exponent === 0) {
            return new self(1);
        }

        // Handle 0 base.
        if ($this->num === 0) {
            // 0 to the power of a negative exponent is invalid (effectively division by zero).
            if ($exponent < 0) {
                throw new DomainException('Cannot raise zero to a negative power.');
            }

            // 0 to the power of a positive exponent is 0.
            return new self(0);
        }

        // Handle negative exponents by taking reciprocal.
        if ($exponent < 0) {
            return $this->inv()->pow(-$exponent);
        }

        // Calculate the new numerator and denominator with overflow checks.
        $h = Integers::pow($this->num, $exponent);
        $k = Integers::pow($this->den, $exponent);

        // Return the result.
        return new self($h, $k);
    }

    /**
     * Calculate the absolute value of this rational number.
     *
     * @return self A new rational number representing the absolute value.
     */
    public function abs(): self
    {
        return new self(abs($this->num), $this->den);
    }

    /**
     * Find the closest integer less than or equal to the rational number.
     *
     * @return int The floored value.
     */
    public function floor(): int
    {
        if ($this->den === 1) {
            return $this->num;
        }
        $q = intdiv($this->num, $this->den);
        return $this->num < 0 ? $q - 1 : $q;
    }

    /**
     * Find the closest integer greater than or equal to the rational number.
     *
     * @return int The ceiling value.
     */
    public function ceil(): int
    {
        if ($this->den === 1) {
            return $this->num;
        }
        $q = intdiv($this->num, $this->den);
        return $this->num > 0 ? $q + 1 : $q;
    }

    /**
     * Find the integer closest to the rational number.
     *
     * The rounding method used here is "half away from zero", to match the default rounding mode used by PHP's
     * round() function. A future version of this method could include a RoundingMode parameter.
     *
     * @return int The closest integer.
     */
    public function round(): int
    {
        if ($this->den === 1) {
            return $this->num;
        }

        $q = intdiv($this->num, $this->den);
        $r = $this->num % $this->den;

        // Round away from zero if remainder ≥ half denominator.
        if (abs($r) * 2 >= $this->den) {
            $result = $this->num > 0 ? $q + 1 : $q - 1;
        } else {
            $result = $q;
        }

        return $result;
    }

    // endregion

    // region Helper methods (private static)

    /**
     * Convert a fraction to its canonical form.
     *
     * @param int $num The numerator.
     * @param int $den The denominator.
     * @return int[] The simplified numerator and denominator.
     * @throws RangeException If the numerator or denominator equals PHP_INT_MIN.
     */
    private static function simplify(int $num, int $den): array
    {
        // Check for a numerator of zero.
        if ($num === 0) {
            return [0, 1];
        }

        // Check if the numerator and denominator are equal to each other.
        if ($num === $den) {
            return [1, 1];
        }

        // Check for a numerator equal to the negative of the denominator.
        if ($num === -$den) {
            return [-1, 1];
        }

        // Calculate the GCD. This could throw RangeException.
        $gcd = Integers::gcd($num, $den);

        // Reduce the fraction if necessary.
        if ($gcd > 1) {
            // Neither of these calls to intdiv() will throw an exception because $gcd won't be 0 or -1.
            $num = intdiv($num, $gcd);
            $den = intdiv($den, $gcd);
        }

        // Return the simplified fraction, ensuring the denominator is positive.
        return $den < 0 ? [-$num, -$den] : [$num, $den];
    }

    /**
     * Convert a float into a pair of integers representing a numerator and denominator.
     *
     * The method uses continued fractions.
     * This finds the simplest rational that equals the provided number (or is as close as is practical).
     *
     * If an exact match is not found, the method will return the closest approximation with a denominator less than
     * or equal to PHP_INT_MAX. This is likely to be a more useful result than an exception and limits the time spent
     * in the method.
     *
     * The valid range for the absolute value of a Rational is 1/PHP_INT_MAX to PHP_INT_MAX/1.
     * This method will throw an exception if the given value is outside that range.
     *
     * Float representation limits can cause inexact round-trip conversions for values very close to integers.
     *
     * @param float $value The value to convert.
     * @return int[] Two integers representing the equivalent rational number.
     * @throws DomainException If the value is infinite or NaN.
     * @throws RangeException If the value is outside the valid convertible range.
     */
    public static function floatToRatio(float $value): array
    {
        // Check for infinite or NaN.
        if (!is_finite($value)) {
            throw new DomainException('Cannot convert ±∞ or NaN to a rational number.');
        }

        // Check if the float equals a valid integer.
        $iValue = Floats::tryConvertToInt($value);
        if ($iValue !== null && $iValue > PHP_INT_MIN) {
            return [$iValue, 1];
        }

        // Initialise variables.
        $sign = Numbers::sign($value, false);
        $absValue = abs($value);
        $rangeErr = 'The value is outside the valid range for representation as a rational number.';

        // Check for values outside the valid range.
        if ($absValue < 1 / PHP_INT_MAX || $absValue > PHP_INT_MAX) {
            throw new RangeException($rangeErr);
        }

        // Initialize convergents.
        $h0 = 1;
        $h1 = 0;
        $k0 = 0;
        $k1 = 1;

        // Track the best approximation found so far. Initialize to the nearest integer.
        $hBest = (int)round($absValue);
        $kBest = 1;
        $minErr = (float)abs($hBest - $absValue);

        // Get the initial approximation.
        $x = $absValue;

        // Loop until done.
        while (true) {
            // Extract integer part.
            $a = (int)$x;

            // Check for negative value, indicating integer overflow.
            if ($a < 0) {
                throw new RangeException($rangeErr);
            }

            // Calculate next convergent
            $hNew = $a * $h0 + $h1;
            $kNew = $a * $k0 + $k1;

            // If the numerator or the denominator overflows the range for integers, cease the loop and return the best
            // approximation found so far.
            // @phpstan-ignore-next-line
            if (is_float($hNew) || is_float($kNew)) {
                return [$sign * $hBest, $kBest];
            }

            // Check if we've found an exact representation.
            $err = (float)abs($hNew / $kNew - $absValue);
            if ($err === 0.0) {
                return [$sign * $hNew, $kNew];
            }

            // Check if this convergent is better than the best so far.
            if ($err < $minErr) {
                $hBest = $hNew;
                $kBest = $kNew;
                $minErr = $err;
            }

            // Update convergents.
            $h1 = $h0;
            $h0 = $hNew;
            $k1 = $k0;
            $k0 = $kNew;

            // Calculate remainder.
            $rem = $x - $a;

            // If the remainder is 0, we're done.
            if ($rem === 0.0) {
                return [$sign * $h0, $k0];
            }

            // Calculate next approximation.
            $x = 1.0 / $rem;
        }
    }

    // endregion
}
