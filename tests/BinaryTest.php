<?php
/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPinnacle\Buffer\Tests;

use PHPinnacle\Buffer\Binary as Buffer;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    public function testSize()
    {
        $buffer = new Buffer;
        $this->assertEquals(0, $buffer->size());

        $buffer->append('a');
        $this->assertEquals(1, $buffer->size());

        $buffer->append('a');
        $this->assertEquals(2, $buffer->size());

        $buffer->read(1);
        $this->assertEquals(2, $buffer->size());

        $buffer->read(2);
        $this->assertEquals(2, $buffer->size());

        $buffer->consume(1);
        $this->assertEquals(1, $buffer->size());

        $buffer->consume(1);
        $this->assertEquals(0, $buffer->size());
    }

    public function testEmpty()
    {
        $buffer = new Buffer;
        $this->assertTrue($buffer->empty());

        $buffer->append('a');
        $this->assertFalse($buffer->empty());
    }

    public function testFlush()
    {
        $buffer = new Buffer('abcd');

        $this->assertEquals('abcd', $buffer->flush());
        $this->assertTrue($buffer->empty());
    }

    public function testToString()
    {
        $buffer = new Buffer('abcd');

        $this->assertEquals('abcd', (string) $buffer);
        $this->assertEquals('abcd', $buffer->__toString());
    }

    public function testRead()
    {
        $buffer = new Buffer('abcd');

        $this->assertEquals('a', $buffer->read(1));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('ab', $buffer->read(2));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('abc', $buffer->read(3));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('abcd', $buffer->read(4));
        $this->assertEquals(4, $buffer->size());
    }

    public function testReadOffset()
    {
        $buffer = new Buffer('abcd');

        $this->assertEquals('a', $buffer->read(1, 0));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('b', $buffer->read(1, 1));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('c', $buffer->read(1, 2));
        $this->assertEquals(4, $buffer->size());

        $this->assertEquals('d', $buffer->read(1, 3));
        $this->assertEquals(4, $buffer->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testReadThrows()
    {
        $buffer = new Buffer;
        $buffer->read(1);
    }

    public function testConsume()
    {
        $buffer = new Buffer('abcd');

        $this->assertEquals('a', $buffer->consume(1));
        $this->assertEquals(3, $buffer->size());

        $this->assertEquals('bc', $buffer->consume(2));
        $this->assertEquals(1, $buffer->size());

        $this->assertEquals('d', $buffer->consume(1));
        $this->assertEquals(0, $buffer->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testConsumeThrows()
    {
        $buffer = new Buffer;
        $buffer->consume(1);
    }

    public function testDiscard()
    {
        $buffer = new Buffer('abcd');

        $buffer->discard(1);
        $this->assertEquals('bcd', $buffer->read($buffer->size()));
        $this->assertEquals(3, $buffer->size());

        $buffer->discard(2);
        $this->assertEquals('d', $buffer->read($buffer->size()));
        $this->assertEquals(1, $buffer->size());

        $buffer->discard(1);
        $this->assertEquals(0, $buffer->size());
        $this->assertTrue($buffer->empty());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testDiscardThrows()
    {
        $buffer = new Buffer;
        $buffer->discard(1);
    }

    public function testSlice()
    {
        $buffer = new Buffer('abcd');

        $slice1 = $buffer->slice(1);
        $this->assertEquals('a', $slice1->read($slice1->size()));
        $this->assertEquals(4, $buffer->size());

        $slice2 = $buffer->slice(2);
        $this->assertEquals('ab', $slice2->read($slice2->size()));
        $this->assertEquals(4, $buffer->size());

        $slice3 = $buffer->slice(3);
        $this->assertEquals('abc', $slice3->read($slice3->size()));
        $this->assertEquals(4, $buffer->size());

        $slice4 = $buffer->slice(4);
        $this->assertEquals('abcd', $slice4->read($slice4->size()));
        $this->assertEquals(4, $buffer->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testSliceThrows()
    {
        $buffer = new Buffer;
        $buffer->slice(1);
    }

    public function testShift()
    {
        $buffer = new Buffer('abcdef');

        $slice1 = $buffer->shift(1);
        $this->assertEquals('a', $slice1->read($slice1->size()));
        $this->assertEquals(5, $buffer->size());

        $slice2 = $buffer->shift(2);
        $this->assertEquals('bc', $slice2->read($slice2->size()));
        $this->assertEquals(3, $buffer->size());

        $slice3 = $buffer->shift(3);
        $this->assertEquals('def', $slice3->read($slice3->size()));
        $this->assertEquals(0, $buffer->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testShiftThrows()
    {
        $buffer = new Buffer;
        $buffer->shift(1);
    }

    public function testAppend()
    {
        $buffer = new Buffer;
        $this->assertEquals(0, $buffer->size());

        $buffer->append('abcd');
        $this->assertEquals(4, $buffer->size());
        $this->assertEquals('abcd', $buffer->read(4));

        $buffer->append('efgh');
        $this->assertEquals(8, $buffer->size());
        $this->assertEquals('abcdefgh', $buffer->read(8));
    }

    public function testAppendBuffer()
    {
        $buffer = new Buffer;
        $this->assertEquals(0, $buffer->size());

        $buffer->append(new Buffer('ab'));
        $this->assertEquals(2, $buffer->size());
        $this->assertEquals('ab', $buffer->read(2));

        $buffer->append('cd');
        $this->assertEquals(4, $buffer->size());
        $this->assertEquals('abcd', $buffer->read(4));

        $buffer->append(new Buffer('ef'));
        $this->assertEquals(6, $buffer->size());
        $this->assertEquals('abcdef', $buffer->read(6));
    }

    // 8-bit integer functions

    public function testReadUint8()
    {
        $this->assertEquals(0xA9, (new Buffer("\xA9"))->readUint8());
    }

    public function testReadInt8()
    {
        $this->assertEquals(0xA9 - 0x100, (new Buffer("\xA9"))->readInt8());
    }

    public function testConsumeUint8()
    {
        $this->assertEquals(0xA9, (new Buffer("\xA9"))->consumeUint8());
    }

    public function testConsumeInt8()
    {
        $this->assertEquals(0xA9 - 0x100, (new Buffer("\xA9"))->consumeInt8());
    }

    public function testAppendUint8()
    {
        $this->assertEquals("\xA9", (new Buffer)->appendUint8(0xA9)->read(1));
    }

    public function testAppendInt8()
    {
        $this->assertEquals("\xA9", (new Buffer)->appendInt8(0xA9 - 0x100)->read(1));
    }

    // 16-bit integer functions

    public function testReadUint16()
    {
        $this->assertEquals(0xA978, (new Buffer("\xA9\x78"))->readUint16());
    }

    public function testReadInt16()
    {
        $this->assertEquals(0xA978 - 0x10000, (new Buffer("\xA9\x78"))->readInt16());
    }

    public function testConsumeUint16()
    {
        $this->assertEquals(0xA978, (new Buffer("\xA9\x78"))->consumeUint16());
    }

    public function testConsumeInt16()
    {
        $this->assertEquals(0xA978 - 0x10000, (new Buffer("\xA9\x78"))->consumeInt16());
    }

    public function testAppendUint16()
    {
        $this->assertEquals("\xA9\x78", (new Buffer)->appendUint16(0xA978)->read(2));
    }

    public function testAppendInt16()
    {
        $this->assertEquals("\xA9\x78", (new Buffer)->appendInt16(0xA978)->read(2));
    }

    // 32-bit integer functions

    public function testReadUint32()
    {
        $this->assertEquals(0xA9782361, (new Buffer("\xA9\x78\x23\x61"))->readUint32());
    }

    public function testReadInt32()
    {
        $this->assertEquals(0xA9782361 - 0x100000000, (new Buffer("\xA9\x78\x23\x61"))->readInt32());
    }

    public function testConsumeUint32()
    {
        $this->assertEquals(0xA9782361, (new Buffer("\xA9\x78\x23\x61"))->consumeUint32());
    }

    public function testConsumeInt32()
    {
        $this->assertEquals(0xA9782361 - 0x100000000, (new Buffer("\xA9\x78\x23\x61"))->consumeInt32());
    }

    public function testAppendUint32()
    {
        $this->assertEquals("\xA9\x78\x23\x61", (new Buffer)->appendUint32(0xA9782361)->read(4));
    }

    public function testAppendInt32()
    {
        $this->assertEquals("\xA9\x78\x23\x61", (new Buffer)->appendInt32(0xA9782361)->read(4));
    }

    // 64-bit integer functions

    public function testReadUint64()
    {
        $this->assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->readUint64());
    }

    public function testReadInt64()
    {
        $this->assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->readInt64());
    }

    public function testConsumeUint64()
    {
        $this->assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->consumeUint64());
    }

    public function testConsumeInt64()
    {
        $this->assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->consumeInt64());
    }

    public function testAppendUint64()
    {
        $this->assertEquals("\x19\x78\x23\x61\x34\x73\x85\x25", (new Buffer)->appendUint64(0x1978236134738525)->read(8));
    }

    public function testAppendInt64()
    {
        $this->assertEquals("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE", (new Buffer)->appendInt64(-2)->read(8));
    }

    // 64-bit integer functions

    public function testNotNativeReadUint64()
    {
        self::enableNotNative64Int();

        $this->assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->readUint64());
    }

    public function testNotNativeReadInt64()
    {
        self::enableNotNative64Int();

        $this->assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->readInt64());
    }

    public function testNotNativeConsumeUint64()
    {
        self::enableNotNative64Int();

        $this->assertEquals(0x1978236134738525, (new Buffer("\x19\x78\x23\x61\x34\x73\x85\x25"))->consumeUint64());
    }

    public function testNotNativeConsumeInt64()
    {
        self::enableNotNative64Int();

        $this->assertEquals(-2, (new Buffer("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->consumeInt64());
    }

    public function testNotNativeAppendUint64()
    {
        self::enableNotNative64Int();

        $this->assertEquals("\x19\x78\x23\x61\x34\x73\x85\x25", (new Buffer)->appendUint64(0x1978236134738525)->read(8));
    }

    public function testNotNativeAppendInt64()
    {
        self::enableNotNative64Int();

        $this->assertEquals("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE", (new Buffer)->appendInt64(-2)->read(8));
    }

    // float

    public function testReadFloat()
    {
        $this->assertEquals(1.5, (new Buffer("\x3F\xC0\x00\x00"))->readFloat());
    }

    public function testConsumeFloat()
    {
        $this->assertEquals(1.5, (new Buffer("\x3F\xC0\x00\x00"))->consumeFloat());
    }

    public function testAppendFloat()
    {
        $this->assertEquals("\x3F\xC0\x00\x00", (new Buffer)->appendFloat(1.5)->read(4));
    }

    // double

    public function testReadDouble()
    {
        $this->assertEquals(1.5, (new Buffer("\x3F\xF8\x00\x00\x00\x00\x00\x00"))->readDouble());
    }

    public function testConsumeDouble()
    {
        $this->assertEquals(1.5, (new Buffer("\x3F\xF8\x00\x00\x00\x00\x00\x00"))->consumeDouble());
    }

    public function testAppendDouble()
    {
        $this->assertEquals("\x3F\xF8\x00\x00\x00\x00\x00\x00", (new Buffer)->appendDouble(1.5)->read(8));
    }

    private static function enableNotNative64Int()
    {
        $property = new \ReflectionProperty(Buffer::class, 'native64BitPack');
        $property->setAccessible(true);
        $property->setValue(false);
    }
}
