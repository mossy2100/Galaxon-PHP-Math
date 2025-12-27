<?php

declare(strict_types=1);

namespace Galaxon\Math;

use DivisionByZeroError;
use Galaxon\Core\Floats;
use Stringable;

/**
 * Represents a floating-point number with tracked absolute error.
 *
 * This class propagates numerical errors through arithmetic operations,
 * providing a way to track precision loss in calculations.
 */
class FloatWithError implements Stringable
{
    // region Properties

    /**
     * The numeric value.
     *
     * @var float
     */
    private(set) float $value;

    /**
     * The absolute error (uncertainty) in the value.
     *
     * @var float
     */
    private(set) float $absoluteError;

    // endregion

    // region Property hooks

    // PHPCS doesn't know property hooks yet.
    // phpcs:disable PSR2.Classes.PropertyDeclaration
    // phpcs:disable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    /**
     * Get the relative error (absolute error divided by value).
     *
     * Returns INF if value is zero but error is non-zero.
     * Returns 0.0 if both value and error are zero.
     *
     * @var float
     */
    public float $relativeError {
        get {
            if ($this->value === 0.0) {
                return $this->absoluteError === 0.0 ? 0.0 : INF;
            }
            return abs($this->absoluteError / $this->value);
        }
    }

    // phpcs:enable PSR2.Classes.PropertyDeclaration
    // phpcs:enable Generic.WhiteSpace.ScopeIndent.IncorrectExact

    // endregion

    // region Constructor

    /**
     * Constructor.
     *
     * @param float $value The numeric value.
     * @param ?float $error The absolute error. If null, estimated from float precision.
     */
    public function __construct(float $value, ?float $error = null)
    {
        // Convert value to float.
        $value = (float)$value;

        // Set the value.
        $this->value = $value;

        // If the error isn't given, compute an source error estimate.
        if ($error === null) {
            $this->absoluteError = Floats::isExactInt($this->value) ? 0.0 : Floats::ulp($this->value) * 0.5;
        } else {
            $this->absoluteError = $error;
        }
    }

    // endregion

    // region Arithmetic methods

    /**
     * Add another FloatWithError to this one.
     *
     * Error propagation: absolute errors add.
     *
     * @param float|self $other The number to add.
     * @return self A new FloatWithError with the sum and propagated error.
     */
    public function add(float|self $other): self
    {
        // Convert other to FloatWithError.
        if (!$other instanceof self) {
            $other = new self($other);
        }

        // Add values.
        $newValue = $this->value + $other->value;

        // Absolute errors add.
        $newError = $this->absoluteError + $other->absoluteError;

        // If no error results from the operation (meaning both operands had zero error), and the result is an exact
        // integer, don't add any rounding error. Otherwise, add half the ULP of the result.
        if ($newError > 0 || !Floats::isExactInt($newValue)) {
            $newError += Floats::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Subtract another FloatWithError from this one.
     *
     * Error propagation: absolute errors add.
     *
     * @param float|self $other The number to subtract.
     * @return self A new FloatWithError with the difference and propagated error.
     */
    public function sub(float|self $other): self
    {
        // Convert other to FloatWithError.
        if (!$other instanceof self) {
            $other = new self($other);
        }

        // Subtract values.
        $newValue = $this->value - $other->value;

        // Absolute errors add.
        $newError = $this->absoluteError + $other->absoluteError;

        // If no error results from the operation (meaning both operands had zero error), and the result is an exact
        // integer, don't add any rounding error. Otherwise, add half the ULP of the result.
        if ($newError > 0 || !Floats::isExactInt($newValue)) {
            $newError += Floats::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Negate this number.
     *
     * Error propagation: error magnitude unchanged.
     *
     * @return self A new FloatWithError with negated value and same error.
     */
    public function neg(): self
    {
        return new self(-$this->value, $this->absoluteError);
    }

    /**
     * Multiply this FloatWithError by another.
     *
     * Error propagation: relative errors add.
     *
     * @param float|self $other The number to multiply by.
     * @return self A new FloatWithError with the product and propagated error.
     */
    public function mul(float|self $other): self
    {
        // Convert other to FloatWithError.
        if (!$other instanceof self) {
            $other = new self($other);
        }

        // Multiply values.
        $newValue = $this->value * $other->value;

        // Relative errors add in multiplication.
        $relError = $this->relativeError + $other->relativeError;
        $newError = abs($newValue) * $relError;

        // If no error results from the operation (meaning both operands had zero error), and the result is an exact
        // integer, don't add any rounding error. Otherwise, add half the ULP of the result.
        if ($newError > 0 || !Floats::isExactInt($newValue)) {
            $newError += Floats::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Divide this FloatWithError by another.
     *
     * Error propagation: relative errors add.
     *
     * @param float|self $other The divisor.
     * @return self A new FloatWithError with the quotient and propagated error.
     * @throws DivisionByZeroError If attempting to divide by zero.
     */
    public function div(float|self $other): self
    {
        // Convert other to FloatWithError.
        if (!$other instanceof self) {
            $other = new self($other);
        }

        // Check for division by zero.
        if ($other->value === 0.0) {
            throw new DivisionByZeroError('Cannot divide by zero.');
        }

        // Divide values.
        $newValue = $this->value / $other->value;

        // Relative errors add in division.
        $relError = $this->relativeError + $other->relativeError;
        $newError = abs($newValue) * $relError;

        // If no error results from the operation (meaning both operands had zero error), and the result is an exact
        // integer, don't add any rounding error. Otherwise, add half the ULP of the result.
        if ($newError > 0 || !Floats::isExactInt($newValue)) {
            $newError += Floats::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    /**
     * Calculate the multiplicative inverse (1/x).
     *
     * Error propagation: relative error unchanged.
     *
     * @return self A new FloatWithError with the inverse and propagated error.
     * @throws DivisionByZeroError If attempting to invert zero.
     */
    public function inv(): self
    {
        if ($this->value === 0.0) {
            throw new DivisionByZeroError();
        }

        $newValue = 1.0 / $this->value;

        // For 1/x, relative error is same as input.
        $relError = $this->relativeError;
        $newError = abs($newValue) * $relError;

        // If no error results from the operation (meaning the operand had zero error), and the result is an exact
        // integer, don't add any rounding error. Otherwise, add half the ULP of the result.
        if ($newError > 0 || !Floats::isExactInt($newValue)) {
            $newError += Floats::ulp($newValue) * 0.5;
        }

        return new self($newValue, $newError);
    }

    // endregion

    // region Conversion methods

    /**
     * Convert to string representation showing value and absolute error.
     *
     * @return string Formatted as "value ± absoluteError".
     */
    public function __toString(): string
    {
        return sprintf('%.15g ± %.2e', $this->value, $this->absoluteError);
    }

    // endregion
}
