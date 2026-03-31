<?php

declare(strict_types=1);

namespace Galaxon\Math\Tests\Matrix;

use Galaxon\Math\Matrix;
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
}
