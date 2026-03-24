<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Matrix;

use Galaxon\Math\Matrix;
use Galaxon\Math\Vector;
use InvalidArgumentException;
use LengthException;
use LogicException;
use OutOfRangeException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Matrix::class)]
class MatrixConversionTest extends TestCase
{
    /**
     * Test toArray returns the correct 2D array.
     */
    public function testToArray(): void
    {
        $m = Matrix::fromArray([
            [1, 2, 3],
            [4, 5, 6],
        ]);
        $this->assertSame([
            [1.0, 2.0, 3.0],
            [4.0, 5.0, 6.0],
        ], $m->toArray());
    }

    /**
     * Test __toString uses box-drawing characters.
     */
    public function testToStringUsesBoxDrawingCharacters(): void
    {
        $m = Matrix::fromArray([
            [1, 2],
            [3, 4],
        ]);
        $str = (string)$m;
        $this->assertStringContainsString("\u{250C}", $str); // top-left corner
        $this->assertStringContainsString("\u{2510}", $str); // top-right corner
        $this->assertStringContainsString("\u{2514}", $str); // bottom-left corner
        $this->assertStringContainsString("\u{2518}", $str); // bottom-right corner
        $this->assertStringContainsString("\u{2502}", $str); // vertical bar
    }

    /**
     * Test __toString with an empty matrix.
     */
    public function testToStringWithEmptyMatrix(): void
    {
        $m = new Matrix(0, 0);
        $str = (string)$m;
        $this->assertStringContainsString("\u{250C}", $str);
        $this->assertStringContainsString("\u{2518}", $str);
    }

    /**
     * Test __toString alignment with mixed-width numbers.
     */
    public function testToStringAlignmentWithMixedWidthNumbers(): void
    {
        $m = Matrix::fromArray([
            [1, 200],
            [30, 4],
        ]);
        $str = (string)$m;
        // The wider number (200) should pad narrower numbers.
        $this->assertStringContainsString('200', $str);
        $this->assertStringContainsString('1', $str);

        // Each row should have the same visual width between the vertical bars.
        $lines = explode("\n", $str);
        // Lines 1 and 2 are data rows (index 1 and 2 of the array).
        $this->assertSame(strlen($lines[1]), strlen($lines[2]));
    }

    /**
     * Test offsetExists with a valid row index.
     */
    public function testOffsetExistsValid(): void
    {
        $m = new Matrix(3, 2);
        $this->assertTrue($m->offsetExists(0));
        $this->assertTrue($m->offsetExists(1));
        $this->assertTrue($m->offsetExists(2));
    }

    /**
     * Test offsetExists with an invalid row index.
     */
    public function testOffsetExistsInvalid(): void
    {
        $m = new Matrix(3, 2);
        $this->assertFalse($m->offsetExists(3));
        $this->assertFalse($m->offsetExists(-1));
    }

    /**
     * Test offsetGet returns a Vector for a valid row.
     */
    public function testOffsetGetReturnsVector(): void
    {
        $m = Matrix::fromArray([
            [1, 2, 3],
            [4, 5, 6],
        ]);
        $row = $m[0];
        $this->assertInstanceOf(Vector::class, $row);
        $this->assertSame([1.0, 2.0, 3.0], $row->toArray());
    }

    /**
     * Test offsetGet with an invalid index throws OutOfRangeException.
     */
    public function testOffsetGetInvalidIndexThrows(): void
    {
        $m = new Matrix(2, 2);
        $this->expectException(OutOfRangeException::class);
        $m[5];
    }

    /**
     * Test offsetSet with a Vector.
     */
    public function testOffsetSetWithVector(): void
    {
        $m = new Matrix(2, 3);
        $v = Vector::fromArray([7, 8, 9]);
        $m[0] = $v;
        $this->assertSame([7.0, 8.0, 9.0], $m->getRow(0)->toArray());
    }

    /**
     * Test offsetSet with an array.
     */
    public function testOffsetSetWithArray(): void
    {
        $m = new Matrix(2, 3);
        $m[1] = [10, 11, 12];
        $this->assertSame([10.0, 11.0, 12.0], $m->getRow(1)->toArray());
    }

    /**
     * Test offsetSet with wrong size throws LengthException.
     */
    public function testOffsetSetWrongSizeThrows(): void
    {
        $m = new Matrix(2, 3);
        $this->expectException(LengthException::class);
        $m[0] = [1, 2];
    }

    /**
     * Test offsetSet with a non-array and non-Vector throws InvalidArgumentException.
     */
    public function testOffsetSetWithNonArrayNonVectorThrows(): void
    {
        $m = new Matrix(2, 3);
        $this->expectException(InvalidArgumentException::class);
        $m[0] = 'not an array';
    }

    /**
     * Test offsetSet with non-numeric values in array throws InvalidArgumentException.
     */
    public function testOffsetSetWithNonNumericValuesThrows(): void
    {
        $m = new Matrix(2, 3);
        $this->expectException(InvalidArgumentException::class);
        $m[0] = [1, 'two', 3];
    }

    /**
     * Test offsetSet with invalid row index throws OutOfRangeException.
     */
    public function testOffsetSetInvalidIndexThrows(): void
    {
        $m = new Matrix(2, 3);
        $this->expectException(OutOfRangeException::class);
        $m[5] = [1, 2, 3];
    }

    /**
     * Test offsetUnset throws LogicException.
     */
    public function testOffsetUnsetThrows(): void
    {
        $m = new Matrix(2, 2);
        $this->expectException(LogicException::class);
        unset($m[0]);
    }
}
