<?php
/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace PHPinnacle\Buffer\Tests;

use PHPinnacle\Buffer\ByteBuffer as Buffer;
use PHPinnacle\Buffer\BufferOverflow;
use PHPUnit\Framework\TestCase;

class ByteBufferTest extends TestCase
{
    public function testSize()
    {
        $buffer = new Buffer();
        self::assertEquals(0, $buffer->size());

        $buffer->append('a');
        self::assertEquals(1, $buffer->size());

        $buffer->append('a');
        self::assertEquals(2, $buffer->size());

        $buffer->read(1);
        self::assertEquals(2, $buffer->size());

        $buffer->read(2);
        self::assertEquals(2, $buffer->size());

        $buffer->consume(1);
        self::assertEquals(1, $buffer->size());

        $buffer->consume(1);
        self::assertEquals(0, $buffer->size());
    }

    public function testEmpty()
    {
        $buffer = new Buffer;
        self::assertTrue($buffer->empty());

        $buffer->append('a');
        self::assertFalse($buffer->empty());
    }

    public function testFlush()
    {
        $buffer = new Buffer('abcd');

        self::assertEquals('abcd', $buffer->flush());
        self::assertTrue($buffer->empty());
    }

    public function testToString()
    {
        $buffer = new Buffer('abcd');

        self::assertEquals('abcd', (string) $buffer);
        self::assertEquals('abcd', $buffer->__toString());
    }

    public function testRead()
    {
        $buffer = new Buffer('abcd');

        self::assertEquals('a', $buffer->read(1));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('ab', $buffer->read(2));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('abc', $buffer->read(3));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('abcd', $buffer->read(4));
        self::assertEquals(4, $buffer->size());
    }

    public function testReadOffset()
    {
        $buffer = new Buffer('abcd');

        self::assertEquals('a', $buffer->read(1, 0));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('b', $buffer->read(1, 1));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('c', $buffer->read(1, 2));
        self::assertEquals(4, $buffer->size());

        self::assertEquals('d', $buffer->read(1, 3));
        self::assertEquals(4, $buffer->size());
    }

    public function testReadThrows()
    {
        self::expectException(BufferOverflow::class);

        $buffer = new Buffer;
        $buffer->read(1);
    }

    public function testConsume()
    {
        $buffer = new Buffer('abcd');

        self::assertEquals('a', $buffer->consume(1));
        self::assertEquals(3, $buffer->size());

        self::assertEquals('bc', $buffer->consume(2));
        self::assertEquals(1, $buffer->size());

        self::assertEquals('d', $buffer->consume(1));
        self::assertEquals(0, $buffer->size());
    }

    public function testConsumeThrows()
    {
        self::expectException(BufferOverflow::class);

        $buffer = new Buffer;
        $buffer->consume(1);
    }

    public function testDiscard()
    {
        $buffer = new Buffer('abcd');

        $buffer->discard(1);
        self::assertEquals('bcd', $buffer->read($buffer->size()));
        self::assertEquals(3, $buffer->size());

        $buffer->discard(2);
        self::assertEquals('d', $buffer->read($buffer->size()));
        self::assertEquals(1, $buffer->size());

        $buffer->discard(1);
        self::assertEquals(0, $buffer->size());
        self::assertTrue($buffer->empty());
    }

    public function testDiscardThrows()
    {
        self::expectException(BufferOverflow::class);

        $buffer = new Buffer;
        $buffer->discard(1);
    }

    public function testSlice()
    {
        $buffer = new Buffer('abcd');

        $slice1 = $buffer->slice(1);
        self::assertEquals('a', $slice1->read($slice1->size()));
        self::assertEquals(4, $buffer->size());

        $slice2 = $buffer->slice(2);
        self::assertEquals('ab', $slice2->read($slice2->size()));
        self::assertEquals(4, $buffer->size());

        $slice3 = $buffer->slice(3);
        self::assertEquals('abc', $slice3->read($slice3->size()));
        self::assertEquals(4, $buffer->size());

        $slice4 = $buffer->slice(4);
        self::assertEquals('abcd', $slice4->read($slice4->size()));
        self::assertEquals(4, $buffer->size());
    }

    public function testSliceThrows()
    {
        self::expectException(BufferOverflow::class);

        $buffer = new Buffer;
        $buffer->slice(1);
    }

    public function testShift()
    {
        $buffer = new Buffer('abcdef');

        $slice1 = $buffer->shift(1);
        self::assertEquals('a', $slice1->read($slice1->size()));
        self::assertEquals(5, $buffer->size());

        $slice2 = $buffer->shift(2);
        self::assertEquals('bc', $slice2->read($slice2->size()));
        self::assertEquals(3, $buffer->size());

        $slice3 = $buffer->shift(3);
        self::assertEquals('def', $slice3->read($slice3->size()));
        self::assertEquals(0, $buffer->size());
    }

    public function testShiftThrows()
    {
        self::expectException(BufferOverflow::class);

        $buffer = new Buffer;
        $buffer->shift(1);
    }

    public function testAppend()
    {
        $buffer = new Buffer;
        self::assertEquals(0, $buffer->size());

        $buffer->append('abcd');
        self::assertEquals(4, $buffer->size());
        self::assertEquals('abcd', $buffer->read(4));

        $buffer->append('efgh');
        self::assertEquals(8, $buffer->size());
        self::assertEquals('abcdefgh', $buffer->read(8));
    }

    public function testAppendBuffer()
    {
        $buffer = new Buffer;
        self::assertEquals(0, $buffer->size());

        $buffer->append(new Buffer('ab'));
        self::assertEquals(2, $buffer->size());
        self::assertEquals('ab', $buffer->read(2));

        $buffer->append('cd');
        self::assertEquals(4, $buffer->size());
        self::assertEquals('abcd', $buffer->read(4));

        $buffer->append(new Buffer('ef'));
        self::assertEquals(6, $buffer->size());
        self::assertEquals('abcdef', $buffer->read(6));
    }

    public function testAppendThrows()
    {
        self::expectException(\TypeError::class);

        $buffer = new Buffer;
        $buffer->append(new \stdClass);
    }

    // // 8-bit integer functions

    public function testReadUint8()
    {
        self::assertEquals(0xA9, (new Buffer("\xA9"))->readUint8());
    }

    public function testReadInt8()
    {
        self::assertEquals(0xA9 - 0x100, (new Buffer("\xA9"))->readInt8());
    }

    public function testConsumeUint8()
    {
        self::assertEquals(0xA9, (new Buffer("\xA9"))->consumeUint8());
    }

    public function testConsumeInt8()
    {
        self::assertEquals(0xA9 - 0x100, (new Buffer("\xA9"))->consumeInt8());
    }

    public function testAppendUint8()
    {
        self::assertEquals("\xA9", (new Buffer)->appendUint8(0xA9)->read(1));
    }

    public function testAppendInt8()
    {
        self::assertEquals("\xA9", (new Buffer)->appendInt8(0xA9 - 0x100)->read(1));
    }

    // 16-bit integer functions

    public function testReadUint16()
    {
        self::assertEquals(0xA978, (new Buffer("\xA9\x78"))->readUint16());
    }

    public function testReadInt16()
    {
        self::assertEquals(0xA978 - 0x10000, (new Buffer("\xA9\x78"))->readInt16());
    }

    public function testConsumeUint16()
    {
        self::assertEquals(0xA978, (new Buffer("\xA9\x78"))->consumeUint16());
    }

    public function testConsumeInt16()
    {
        self::assertEquals(0xA978 - 0x10000, (new Buffer("\xA9\x78"))->consumeInt16());
    }

    public function testAppendUint16()
    {
        self::assertEquals("\xA9\x78", (new Buffer)->appendUint16(0xA978)->read(2));
    }

    public function testAppendInt16()
    {
        self::assertEquals("\xA9\x78", (new Buffer)->appendInt16(0xA978)->read(2));
    }

    // 32-bit integer functions

    public function testReadUint32()
    {
        self::assertEquals(0xA9782361, (new Buffer("\xA9\x78\x23\x61"))->readUint32());
    }

    public function testReadUint32LE()
    {
        self::assertEquals(0xA9782361, (new Buffer("\x61\x23\x78\xA9"))->readUint32LE());
    }

    public function testReadInt32()
    {
        self::assertEquals(0xA9782361 - 0x100000000, (new Buffer("\xA9\x78\x23\x61"))->readInt32());
    }

    public function testConsumeUint32()
    {
        self::assertEquals(0xA9782361, (new Buffer("\xA9\x78\x23\x61"))->consumeUint32());
    }

    public function testConsumeUint32LE()
    {
        self::assertEquals(0xA9782361, (new Buffer("\x61\x23\x78\xA9"))->consumeUint32LE());
    }

    public function testConsumeInt32()
    {
        self::assertEquals(0xA9782361 - 0x100000000, (new Buffer("\xA9\x78\x23\x61"))->consumeInt32());
    }

    public function testAppendUint32()
    {
        self::assertEquals("\xA9\x78\x23\x61", (new Buffer)->appendUint32(0xA9782361)->read(4));
    }

    public function testAppendUint32LE()
    {
        self::assertEquals("\x61\x23\x78\xA9", (new Buffer)->appendUint32LE(0xA9782361)->read(4));
    }

    public function testAppendInt32()
    {
        self::assertEquals("\xA9\x78\x23\x61", (new Buffer)->appendInt32(0xA9782361)->read(4));
    }

    // 64-bit integer functions

    public function testReadUint64()
    {
        self::assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->readUint64());
    }

    public function testReadInt64()
    {
        self::assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->readInt64());
    }

    public function testConsumeUint64()
    {
        self::assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->consumeUint64());
    }

    public function testConsumeInt64()
    {
        self::assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->consumeInt64());
    }

    public function testAppendUint64()
    {
        self::assertEquals("\x19\x78\x23\x61\x34\x73\x85\x25", (new Buffer)->appendUint64(0x1978236134738525)->read(8));
    }

    public function testAppendInt64()
    {
        self::assertEquals("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE", (new Buffer)->appendInt64(-2)->read(8));
    }

    // Float

    public function testReadFloat()
    {
        $buffer = new Buffer("\x3F\xC0\x00\x00");

        self::assertEquals(4, $buffer->size());
        self::assertEquals(1.5, $buffer->readFloat());
    }

    public function testConsumeFloat()
    {
        self::assertEquals(1.5, (new Buffer("\x3F\xC0\x00\x00"))->consumeFloat());
    }

    public function testAppendFloat()
    {
        self::assertEquals("\x3F\xC0\x00\x00", (new Buffer)->appendFloat(1.5)->read(4));
    }

    // Double

    public function testReadDouble()
    {
        $buffer = new Buffer("\x3F\xF8\x00\x00\x00\x00\x00\x00");

        self::assertEquals(8, $buffer->size());
        self::assertEquals(1.5, $buffer->readDouble());
    }

    public function testConsumeDouble()
    {
        self::assertEquals(1.5, (new Buffer("\x3F\xF8\x00\x00\x00\x00\x00\x00"))->consumeDouble());
    }

    public function testAppendDouble()
    {
        self::assertEquals("\x3F\xF8\x00\x00\x00\x00\x00\x00", (new Buffer)->appendDouble(1.5)->read(8));
    }
}
