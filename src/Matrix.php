<?php

declare(strict_types=1);

namespace Galaxon\Math;

use ArrayAccess;
use DivisionByZeroError;
use DomainException;
use Galaxon\Core\Numbers;
use InvalidArgumentException;
use LengthException;
use LogicException;
use OutOfRangeException;
use Stringable;

/**
 * Encapsulates a 2-dimensional matrix and provides a number of useful methods.
 *
 * @implements ArrayAccess<int, list<float>>
 */
final class Matrix implements Stringable, ArrayAccess
{
    // region Properties

    /**
     * The matrix data.
     *
     * This must be private because even if it's private(set) if they can get $this->data they could add new elements
     * (inadvertently sizing the matrix without changing rowCount/colCount or making it non-rectangular) or they
     * could set elements to non-numbers.
     *
     * @var list<list<int|float>>
     */
    private array $data;

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
        $this->data = array_fill(0, $rowCount, array_fill(0, $columnCount, 0));
    }

    // endregion

    // region Factory methods

    /**
     * Create a matrix from a 2D array.
     *
     * @param array<array-key, array<array-key, int|float>> $data Rectangular array of numbers.
     * @return self
     * @throws InvalidArgumentException If any row is not an array, or contains non-numeric values.
     * @throws LengthException If rows have different numbers of items.
     */
    public static function fromArray(array $data): self
    {
        $rowCount = count($data);
        $columnCount = null;

        // Validate data and ensure rectangular matrix.
        foreach ($data as $row) {
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

            // Check each row contains only numbers.
            foreach ($row as $value) {
                if (!Numbers::isNumber($value)) {
                    throw new InvalidArgumentException('Matrix elements must be numbers (int or float).');
                }
            }
        }

        // Create the matrix.
        $matrix = new self($rowCount, $columnCount ?? 0);
        foreach (array_values($data) as $i => $row) {
            foreach (array_values($row) as $j => $value) {
                $matrix->data[$i][$j] = $value;
            }
        }

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
            $result->data[$i][$i] = 1;
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
     * @return int|float Value of the matrix element.
     * @throws OutOfRangeException If indexes are outside valid range.
     */
    public function get(int $row, int $col): int|float
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

        $this->data[$row][$col] = $value;
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
                $result->data[$i][$j] = $this->data[$i][$j] + $other->data[$i][$j];
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
                $result->data[$i][$j] = $this->data[$i][$j] - $other->data[$i][$j];
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
                $adjugate->data[$j][$i] = $cofactor / $det; // Note: transposed
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
            $result = $this->mul($other->toMatrix());
            if ($result->rowCount === 0) {
                return new Vector(0);
            }
            return $result->getColumn(0);
        }

        // Check if multiplying matrix by a number.
        if (Numbers::isNumber($other)) {
            $scaled = new self($this->rowCount, $this->columnCount);
            for ($i = 0; $i < $this->rowCount; $i++) {
                for ($j = 0; $j < $this->columnCount; $j++) {
                    $scaled->data[$i][$j] = $this->data[$i][$j] * $other;
                }
            }
            return $scaled;
        }

        // Multiplying a matrix by a matrix.
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
                $result->data[$i][$j] = $sum;
            }
        }

        return $result;
    }

    /**
     * Divide this matrix by a number or another matrix (A * B^-1).
     *
     * @param int|float|self $other Number or matrix to divide by.
     * @return self New matrix representing the quotient.
     * @throws DivisionByZeroError If dividing by zero.
     * @throws DomainException If dividing by a non-invertible matrix.
     */
    public function div(int|float|self $other): self
    {
        if (Numbers::isNumber($other)) {
            if (Numbers::equal($other, 0)) {
                throw new DivisionByZeroError('Division by zero is not allowed.');
            }
            $m = 1.0 / $other;
        } else {
            // Multiply by the inverse.
            $m = $other->inv();
        }

        return $this->mul($m);
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

        $result = $this->identity($this->rowCount);
        $base = clone $this;

        while ($power > 0) {
            if ($power % 2 === 1) {
                $result = $result->mul($base);
            }
            $base = $base->mul($base);
            $power = (int)($power / 2);
        }

        return $result;
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
                $result->data[$j][$i] = $this->data[$i][$j];
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
     * @param list<list<int|float>> $matrix Matrix data.
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
     * @return list<list<int|float>> Minor matrix.
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
     * @return list<list<int|float>> Rectangular array of matrix elements.
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Convert the matrix to a string representation using box-drawing characters.
     *
     * @return string String representation with box-drawing brackets and aligned columns.
     */
    public function __toString(): string
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

        // Inner width: 1 space + values separated by 2 spaces + 1 space.
        $innerWidth = 1 + $this->columnCount * $maxWidth + ($this->columnCount - 1) * 2 + 1;

        // Top border.
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
     * @throws OutOfRangeException If row index is outside valid range.
     */
    public function offsetGet(mixed $offset): Vector
    {
        return $this->getRow($offset);
    }

    /**
     * Set a row from a Vector or array.
     *
     * @param mixed $offset Row index to set.
     * @param mixed $value Vector or array of numbers.
     * @throws OutOfRangeException If row index is outside valid range.
     * @throws InvalidArgumentException If value is not a Vector or array, or contains non-numeric values.
     * @throws LengthException If value has wrong number of elements.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$this->offsetExists($offset)) {
            throw new OutOfRangeException('Matrix row index outside valid range.');
        }

        if ($value instanceof Vector) {
            $value = $value->toArray();
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('Row must be a Vector or array.');
        }

        if (count($value) !== $this->columnCount) {
            throw new LengthException("Row must have exactly {$this->columnCount} elements.");
        }

        foreach ($value as $v) {
            if (!Numbers::isNumber($v)) {
                throw new InvalidArgumentException('Row elements must be numbers (int or float).');
            }
        }

        $this->data[$offset] = array_values($value);
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
