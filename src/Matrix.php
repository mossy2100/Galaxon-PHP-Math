<?php

declare(strict_types=1);

namespace Galaxon\Math;

use ArrayAccess;
use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Floats;
use Galaxon\Core\Numbers;
use Galaxon\Core\Traits\ApproxEquatable;
use InvalidArgumentException;
use LengthException;
use LogicException;
use OutOfRangeException;
use Override;
use Stringable;

/**
 * Encapsulates a 2-dimensional matrix and provides a number of useful methods.
 *
 * @implements ArrayAccess<int, Vector>
 */
final class Matrix implements Stringable, ArrayAccess
{
    use ApproxEquatable;

    // region Private properties

    /**
     * The matrix data.
     *
     * This must be private because even if it's private(set) if they can get $this->data they could add new elements
     * (inadvertently sizing the matrix without changing rowCount/colCount or making it non-rectangular) or they
     * could set elements to non-numbers.
     *
     * @var list<list<float>>
     */
    private array $data;

    // endregion

    // region Public properties

    /**
     * The number of rows in the matrix.
     */
    public int $rowCount {
        get => count($this->data);
    }

    /**
     * The number of columns in the matrix.
     */
    private(set) int $columnCount;

    // endregion

    // region Constructor

    /**
     * Create a new matrix with the specified dimensions.
     *
     * @param int $rowCount Number of rows.
     * @param int $columnCount Number of columns.
     * @throws DomainException If dimensions are negative.
     */
    public function __construct(int $rowCount, int $columnCount)
    {
        // Check if dimensions are non-negative.
        if ($rowCount < 0 || $columnCount < 0) {
            throw new DomainException('Matrix dimensions must not be negative.');
        }

        // Initialize matrix properties.
        $this->columnCount = $columnCount;
        $this->data = array_fill(0, $rowCount, array_fill(0, $columnCount, 0.0));
    }

    // endregion

    // region Factory methods

    /**
     * Create a matrix from a 2D array.
     *
     * @param array<array-key, array<array-key, int|float>> $arr Rectangular array of numbers.
     * @return self
     * @throws InvalidArgumentException If any row is not an array, or contains non-numeric values.
     * @throws LengthException If rows have different numbers of items.
     */
    public static function fromArray(array $arr): self
    {
        $rowCount = count($arr);
        $columnCount = null;
        $data = [];

        // Validate data and ensure rectangular matrix.
        foreach ($arr as $row) {
            // Check if each row is an array.
            if (!is_array($row)) {
                throw new InvalidArgumentException('Each row must be an array.');
            }

            // Check all rows have the same number of columns.
            if ($columnCount === null) {
                $columnCount = count($row);
            } elseif (count($row) !== $columnCount) {
                throw new LengthException('All rows must have the same number of items.');
            }

            $dataRow = [];

            // Check each row contains only numbers.
            foreach ($row as $value) {
                // Check if each value is a number.
                if (!Numbers::isNumber($value)) {
                    throw new InvalidArgumentException('Matrix elements must be numbers (int or float).');
                }

                // Convert the value to a float and store it in the matrix.
                $dataRow[] = (float)$value;
            }

            $data[] = $dataRow;
        }

        // Create the matrix.
        $matrix = new self($rowCount, $columnCount ?? 0);
        $matrix->data = $data;

        return $matrix;
    }

    /**
     * Create an identity matrix of the specified size.
     *
     * @param int $size Size of the identity matrix.
     * @return self Identity matrix.
     */
    public static function identity(int $size): self
    {
        $result = new self($size, $size);
        for ($i = 0; $i < $size; $i++) {
            $result->set($i, $i, 1);
        }
        return $result;
    }

    // endregion

    // region Get/set matrix elements

    /**
     * Get a matrix element.
     *
     * @param int $row Row index (0-based).
     * @param int $col Column index (0-based).
     * @return float Value of the matrix element.
     * @throws OutOfRangeException If indexes are outside valid range.
     */
    public function get(int $row, int $col): float
    {
        // Check if indexes are within bounds.
        if ($row < 0 || $row >= $this->rowCount || $col < 0 || $col >= $this->columnCount) {
            throw new OutOfRangeException('Matrix indexes outside valid range.');
        }

        return $this->data[$row][$col];
    }

    /**
     * Set a matrix element.
     *
     * @param int $row Row index (0-based).
     * @param int $col Column index (0-based).
     * @param int|float $value Value to set.
     * @throws OutOfRangeException If indexes are outside valid range.
     */
    public function set(int $row, int $col, int|float $value): void
    {
        // Check if indexes are within bounds.
        if ($row < 0 || $row >= $this->rowCount || $col < 0 || $col >= $this->columnCount) {
            throw new OutOfRangeException('Matrix indexes outside valid range.');
        }

        assert($row < count($this->data) && $col < count($this->data[$row]));
        $this->data[$row][$col] = (float)$value;
    }

    /**
     * Get a row as a vector.
     *
     * @param int $row Row index (0-based).
     * @return Vector Row vector.
     * @throws OutOfRangeException If row index is outside valid range.
     */
    public function getRow(int $row): Vector
    {
        // Check if row index is within bounds.
        if ($row < 0 || $row >= $this->rowCount) {
            throw new OutOfRangeException('Row index outside valid range.');
        }

        return Vector::fromArray($this->data[$row]);
    }

    /**
     * Get a column as a vector.
     *
     * @param int $col Column index (0-based).
     * @return Vector Column vector.
     * @throws OutOfRangeException If column index is outside valid range.
     */
    public function getColumn(int $col): Vector
    {
        // Check if column index is within bounds.
        if ($col < 0 || $col >= $this->columnCount) {
            throw new OutOfRangeException('Column index outside valid range.');
        }

        $column = [];
        for ($i = 0; $i < $this->rowCount; $i++) {
            $column[] = $this->data[$i][$col];
        }

        return Vector::fromArray($column);
    }

    // endregion

    // region Inspection methods

    /**
     * Check if the matrix is square, optionally of a specific size.
     *
     * @param int|null $size If specified, check for exact size, otherwise any size.
     * @return bool True if square, false otherwise.
     */
    public function isSquare(?int $size = null): bool
    {
        return ($this->rowCount === $this->columnCount) && ($size === null || $this->rowCount === $size);
    }

    // endregion

    // region Comparison methods

    /**
     * Check if this matrix equals another.
     *
     * Two matrices are equal if they have the same dimensions and all corresponding elements are exactly equal.
     * Returns false for non-Matrix values.
     *
     * @param mixed $other The value to compare with.
     * @return bool True if the matrices have the same dimensions and all elements are equal.
     */
    #[Override]
    public function equal(mixed $other): bool
    {
        // Check both are Matrix objects.
        if (!$other instanceof self) {
            return false;
        }

        // Check sizes are equal.
        if ($this->rowCount !== $other->rowCount || $this->columnCount !== $other->columnCount) {
            return false;
        }

        // Check elements are equal.
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                if ($this->data[$i][$j] !== $other->data[$i][$j]) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if this matrix approximately equals another, within given tolerances.
     *
     * Each pair of corresponding elements is compared using Floats::approxEqual(), which checks
     * absolute tolerance first, then relative tolerance.
     *
     * @param mixed $other The value to compare with.
     * @param float $relTol The relative tolerance.
     * @param float $absTol The absolute tolerance.
     * @return bool True if the matrices have the same dimensions and all elements are approximately equal.
     * @throws DomainException If either tolerance is negative.
     * @see Floats::approxEqual()
     */
    #[Override]
    public function approxEqual(
        mixed $other,
        float $relTol = Floats::DEFAULT_RELATIVE_TOLERANCE,
        float $absTol = Floats::DEFAULT_ABSOLUTE_TOLERANCE
    ): bool {
        // Check both are Matrix objects.
        if (!$other instanceof self) {
            return false;
        }

        // Check sizes are equal.
        if ($this->rowCount !== $other->rowCount || $this->columnCount !== $other->columnCount) {
            return false;
        }

        // Check elements are approximately equal.
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                if (!Floats::approxEqual($this->data[$i][$j], $other->data[$i][$j], $relTol, $absTol)) {
                    return false;
                }
            }
        }

        return true;
    }

    // endregion

    // region Matrix operations

    /**
     * Add another matrix to this one.
     *
     * @param self $other Matrix to add.
     * @return self New matrix representing the sum.
     * @throws LengthException If matrices have different dimensions.
     */
    public function add(self $other): self
    {
        // Check if dimensions are the same.
        if ($this->rowCount !== $other->rowCount || $this->columnCount !== $other->columnCount) {
            throw new LengthException('Matrices must have the same dimensions for addition.');
        }

        // Add the matrices.
        $result = new self($this->rowCount, $this->columnCount);
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                $result->set($i, $j, $this->data[$i][$j] + $other->data[$i][$j]);
            }
        }
        return $result;
    }

    /**
     * Subtract another matrix from this one.
     *
     * @param self $other Matrix to subtract.
     * @return self New matrix representing the difference.
     * @throws LengthException If matrices have different dimensions.
     */
    public function sub(self $other): self
    {
        // Check if dimensions are the same.
        if ($this->rowCount !== $other->rowCount || $this->columnCount !== $other->columnCount) {
            throw new LengthException('Matrices must have the same dimensions for subtraction.');
        }

        // Subtract the matrices.
        $result = new self($this->rowCount, $this->columnCount);
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                $result->set($i, $j, $this->data[$i][$j] - $other->data[$i][$j]);
            }
        }
        return $result;
    }

    /**
     * Calculate the inverse of this matrix.
     *
     * @return self New matrix representing the inverse.
     * @throws DomainException If matrix is not square or not invertible.
     */
    public function inv(): self
    {
        // Check if matrix is square.
        if (!$this->isSquare()) {
            throw new DomainException('Inverse can only be calculated for square matrices.');
        }

        // Calculate the inverse using cofactor expansion and the adjugate matrix.
        $det = $this->det();
        if ($det === 0.0) {
            throw new DomainException('Matrix is not invertible (determinant is zero).');
        }

        $n = $this->rowCount;
        $adjugate = new self($n, $n);

        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $minor = $this->getMinor($i, $j);
                $cofactor = (($i + $j) % 2 === 0 ? 1 : -1) * $this->calcDet($minor);
                $adjugate->set($j, $i, $cofactor / $det); // Note: transposed
            }
        }

        return $adjugate;
    }

    /**
     * Multiply this matrix by a scalar, vector, or another matrix.
     *
     * When multiplying by a Vector, it is treated as a column vector (n×1 matrix) and the result
     * is returned as a Vector.
     *
     * @param int|float|Vector|self $other Number, vector, or matrix to multiply by.
     * @return self|Vector A Matrix for scalar/matrix operands, or a Vector for vector operands.
     * @throws LengthException If dimensions are incompatible for multiplication.
     */
    public function mul(int|float|Vector|self $other): self|Vector
    {
        // Multiplying matrix by a vector (treated as a column vector).
        if ($other instanceof Vector) {
            // Convert the Vector to a column matrix.
            $result = $this->mul($other->toMatrix());

            // Handle 0-row result where getColumn() would fail.
            assert($result instanceof self);
            if ($result->rowCount === 0) {
                return new Vector(0);
            }

            // If we were given a Vector, assume the desired result should be a Vector.
            return $result->getColumn(0);
        }

        // Multiplying matrix by a scalar.
        if (Numbers::isNumber($other)) {
            // Multiply each element of the matrix by the scalar.
            $scaled = new self($this->rowCount, $this->columnCount);
            for ($i = 0; $i < $this->rowCount; $i++) {
                for ($j = 0; $j < $this->columnCount; $j++) {
                    $scaled->set($i, $j, $this->data[$i][$j] * $other);
                }
            }
            return $scaled;
        }

        // Multiply a matrix by a matrix.
        // Check if dimensions are compatible for multiplication.
        if ($this->columnCount !== $other->rowCount) {
            throw new LengthException(
                'The number of columns in the first matrix must equal the number of rows in the second matrix.'
            );
        }

        // Multiply the matrices.
        $result = new self($this->rowCount, $other->columnCount);
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $other->columnCount; $j++) {
                $sum = 0.0;
                for ($k = 0; $k < $this->columnCount; $k++) {
                    $sum += $this->data[$i][$k] * $other->data[$k][$j];
                }
                $result->set($i, $j, $sum);
            }
        }

        return $result;
    }

    /**
     * Divide this matrix by a number or another matrix (A × B⁻¹).
     *
     * @param int|float|self $other Number or matrix to divide by.
     * @return self New matrix representing the quotient.
     * @throws DivisionByZeroError If dividing by zero.
     * @throws DomainException If dividing by a non-invertible matrix.
     */
    public function div(int|float|self $other): self
    {
        // Check if dividing by a scalar.
        if (Numbers::isNumber($other)) {
            // Guard against division by zero.
            if (Numbers::equal($other, 0)) {
                throw new DivisionByZeroError('Division by zero is not allowed.');
            }

            // Divide each element of the matrix by the scalar.
            $scaled = new self($this->rowCount, $this->columnCount);
            for ($i = 0; $i < $this->rowCount; $i++) {
                for ($j = 0; $j < $this->columnCount; $j++) {
                    $scaled->set($i, $j, $this->data[$i][$j] / $other);
                }
            }
            return $scaled;
        }

        // Multiply by the inverse.
        $result = $this->mul($other->inv());
        assert($result instanceof self);
        return $result;
    }

    /**
     * Raise this matrix to a power.
     *
     * @param int $power Power to raise to.
     * @return self New matrix representing the result.
     * @throws DomainException If matrix is not square, or not invertible for negative powers.
     */
    public function pow(int $power): self
    {
        // Check if matrix is square.
        if (!$this->isSquare()) {
            throw new DomainException('Power can only be calculated for square matrices.');
        }

        // Handle zero power.
        if ($power === 0) {
            return self::identity($this->rowCount);
        }

        // Handle negative powers.
        if ($power < 0) {
            return $this->inv()->pow(-$power);
        }

        $result = self::identity($this->rowCount);
        $base = clone $this;

        while ($power > 0) {
            if ($power % 2 === 1) {
                $result = $result->mul($base);
            }
            $base = $base->mul($base);
            $power = (int)($power / 2);
        }

        assert($result instanceof self);
        return $result;
    }

    /**
     * Square this matrix.
     *
     * Equivalent to pow(2), but more efficient and readable.
     *
     * @return self A new matrix representing the square of this matrix.
     * @throws DomainException If the matrix is not square.
     */
    public function sqr(): self
    {
        if (!$this->isSquare()) {
            throw new DomainException('Square can only be calculated for square matrices.');
        }
        return $this->mul($this);
    }

    /**
     * Get the transpose of this matrix.
     *
     * @return self New matrix representing the transpose.
     */
    public function transpose(): self
    {
        $result = new self($this->columnCount, $this->rowCount);
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                $result->set($j, $i, $this->data[$i][$j]);
            }
        }

        return $result;
    }

    /**
     * Calculate the determinant of this matrix.
     *
     * @return float The determinant.
     * @throws DomainException If matrix is not square.
     */
    public function det(): float
    {
        // Check if matrix is square.
        if (!$this->isSquare()) {
            throw new DomainException('Determinant can only be calculated for square matrices.');
        }

        return $this->calcDet($this->data);
    }

    // endregion

    // region Helper methods

    /**
     * Recursive helper method to calculate determinant.
     *
     * @param list<list<float>> $matrix Matrix data.
     * @return float Determinant of the matrix.
     */
    private function calcDet(array $matrix): float
    {
        $n = count($matrix);

        if ($n === 1) {
            return $matrix[0][0];
        }

        if ($n === 2) {
            return $matrix[0][0] * $matrix[1][1] - $matrix[0][1] * $matrix[1][0];
        }

        $det = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $submatrix = [];
            for ($i = 1; $i < $n; $i++) {
                $row = [];
                for ($k = 0; $k < $n; $k++) {
                    if ($k !== $j) {
                        $row[] = $matrix[$i][$k];
                    }
                }
                $submatrix[] = $row;
            }

            $cofactor = ($j % 2 === 0 ? 1 : -1) * $matrix[0][$j] * $this->calcDet($submatrix);
            $det += $cofactor;
        }

        return $det;
    }

    /**
     * Get the minor matrix by removing the specified row and column.
     *
     * @param int $excludeRow Row to exclude.
     * @param int $excludeColumn Column to exclude.
     * @return list<list<float>> Minor matrix.
     */
    private function getMinor(int $excludeRow, int $excludeColumn): array
    {
        $minor = [];
        for ($i = 0; $i < $this->rowCount; $i++) {
            if ($i !== $excludeRow) {
                $row = [];
                for ($j = 0; $j < $this->columnCount; $j++) {
                    if ($j !== $excludeColumn) {
                        $row[] = $this->data[$i][$j];
                    }
                }
                $minor[] = $row;
            }
        }
        return $minor;
    }

    // endregion

    // region Conversion methods

    /**
     * Get a copy of the matrix data as a rectangular array.
     *
     * @return list<list<float>> Rectangular array of matrix elements.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    // endregion

    // region String methods

    /**
     * Convert the matrix to a string representation using box-drawing characters.
     *
     * @return string String representation of the Matrix.
     */
    public function format(): string
    {
        if ($this->rowCount === 0 || $this->columnCount === 0) {
            return '┌ ┐' . "\n" . '└ ┘';
        }

        // Calculate the maximum width needed for formatting.
        $maxWidth = 0;
        for ($i = 0; $i < $this->rowCount; $i++) {
            for ($j = 0; $j < $this->columnCount; $j++) {
                $maxWidth = max($maxWidth, strlen((string)$this->data[$i][$j]));
            }
        }

        // Top border.
        $innerWidth = $this->columnCount * ($maxWidth + 2);
        $result = '┌' . str_repeat(' ', $innerWidth) . '┐' . "\n";

        // Data rows.
        for ($i = 0; $i < $this->rowCount; $i++) {
            $result .= '│ ';
            for ($j = 0; $j < $this->columnCount; $j++) {
                if ($j > 0) {
                    $result .= '  ';
                }
                $result .= str_pad((string)$this->data[$i][$j], $maxWidth, ' ', STR_PAD_LEFT);
            }
            $result .= ' │' . "\n";
        }

        // Bottom border.
        $result .= '└' . str_repeat(' ', $innerWidth) . '┘';

        return $result;
    }

    /**
     * Convert the matrix to a string representation using box-drawing characters.
     *
     * @return string String representation of the Matrix.
     */
    public function __toString(): string
    {
        return $this->format();
    }

    // endregion

    // region ArrayAccess methods

    /**
     * Check if a row index exists.
     *
     * @param mixed $offset Row index to check.
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return is_int($offset) && $offset >= 0 && $offset < $this->rowCount;
    }

    /**
     * Get a row as a Vector.
     *
     * @param mixed $offset Row index to get.
     * @return Vector The row vector.
     * @throws OutOfRangeException If the offset is invalid.
     */
    public function offsetGet(mixed $offset): Vector
    {
        // Check offset exists.
        if (!$this->offsetExists($offset)) {
            throw new OutOfRangeException('Row index must be an integer within the valid range.');
        }

        assert(is_int($offset));
        return $this->getRow($offset);
    }

    /**
     * Set a row from a Vector or array.
     *
     * @param mixed $offset Row index to set.
     * @param mixed $value Vector or array of numbers.
     * @throws OutOfRangeException If the offset is invalid.
     * @throws InvalidArgumentException If the value is not a Vector or array, or the value contains non-numeric values.
     * @throws LengthException If the value has the wrong number of elements.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Check offset exists.
        if (!$this->offsetExists($offset)) {
            throw new OutOfRangeException('Row index must be an integer within the valid range.');
        }
        assert(is_int($offset));

        // Convert Vector to array.
        if ($value instanceof Vector) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('Row must be a Vector or array.');
        }

        if (count($value) !== $this->columnCount) {
            throw new LengthException("Row must have exactly {$this->columnCount} elements.");
        }

        $data = [];
        foreach ($value as $v) {
            if (!Numbers::isNumber($v)) {
                throw new InvalidArgumentException('Row elements must be numbers (int or float).');
            }
            $data[] = (float)$v;
        }

        $this->data[$offset] = $data;
    }

    /**
     * Unset is not supported for matrices.
     *
     * @param mixed $offset Row index.
     * @throws LogicException Always throws.
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException('Cannot unset rows in a matrix.');
    }

    // endregion
}
