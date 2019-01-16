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

use PHPinnacle\Buffer\Binary;
use PHPUnit\Framework\TestCase;

class BinaryTest extends TestCase
{
    public function testGetLength()
    {
        $buf = new Binary;
        $this->assertEquals(0, $buf->size());

        $buf->append('a');
        $this->assertEquals(1, $buf->size());

        $buf->append('a');
        $this->assertEquals(2, $buf->size());

        $buf->read(1);
        $this->assertEquals(2, $buf->size());

        $buf->read(2);
        $this->assertEquals(2, $buf->size());

        $buf->consume(1);
        $this->assertEquals(1, $buf->size());

        $buf->consume(1);
        $this->assertEquals(0, $buf->size());
    }

    public function testIsEmpty()
    {
        $buf = new Binary;
        $this->assertTrue($buf->empty());

        $buf->append('a');
        $this->assertFalse($buf->empty());
    }

    public function testRead()
    {
        $buf = new Binary('abcd');

        $this->assertEquals('a', $buf->read(1));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('ab', $buf->read(2));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('abc', $buf->read(3));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('abcd', $buf->read(4));
        $this->assertEquals(4, $buf->size());
    }

    public function testReadOffset()
    {
        $buf = new Binary('abcd');

        $this->assertEquals('a', $buf->read(1, 0));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('b', $buf->read(1, 1));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('c', $buf->read(1, 2));
        $this->assertEquals(4, $buf->size());

        $this->assertEquals('d', $buf->read(1, 3));
        $this->assertEquals(4, $buf->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testReadThrows()
    {
        $buf = new Binary;
        $buf->read(1);
    }

    public function testConsume()
    {
        $buf = new Binary('abcd');

        $this->assertEquals('a', $buf->consume(1));
        $this->assertEquals(3, $buf->size());

        $this->assertEquals('bc', $buf->consume(2));
        $this->assertEquals(1, $buf->size());

        $this->assertEquals('d', $buf->consume(1));
        $this->assertEquals(0, $buf->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testConsumeThrows()
    {
        $buf = new Binary;
        $buf->consume(1);
    }

    public function testDiscard()
    {
        $buf = new Binary('abcd');

        $buf->discard(1);
        $this->assertEquals('bcd', $buf->read($buf->size()));
        $this->assertEquals(3, $buf->size());

        $buf->discard(2);
        $this->assertEquals('d', $buf->read($buf->size()));
        $this->assertEquals(1, $buf->size());

        $buf->discard(1);
        $this->assertEquals(0, $buf->size());
        $this->assertTrue($buf->empty());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testDiscardThrows()
    {
        $buf = new Binary;
        $buf->discard(1);
    }

    public function testSlice()
    {
        $buf = new Binary('abcd');

        $slice1 = $buf->slice(1);
        $this->assertEquals('a', $slice1->read($slice1->size()));
        $this->assertEquals(4, $buf->size());

        $slice2 = $buf->slice(2);
        $this->assertEquals('ab', $slice2->read($slice2->size()));
        $this->assertEquals(4, $buf->size());

        $slice3 = $buf->slice(3);
        $this->assertEquals('abc', $slice3->read($slice3->size()));
        $this->assertEquals(4, $buf->size());

        $slice4 = $buf->slice(4);
        $this->assertEquals('abcd', $slice4->read($slice4->size()));
        $this->assertEquals(4, $buf->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testSliceThrows()
    {
        $buf = new Binary;
        $buf->slice(1);
    }

    public function testConsumeSlice()
    {
        $buf = new Binary('abcdef');

        $slice1 = $buf->shift(1);
        $this->assertEquals('a', $slice1->read($slice1->size()));
        $this->assertEquals(5, $buf->size());

        $slice2 = $buf->shift(2);
        $this->assertEquals('bc', $slice2->read($slice2->size()));
        $this->assertEquals(3, $buf->size());

        $slice3 = $buf->shift(3);
        $this->assertEquals('def', $slice3->read($slice3->size()));
        $this->assertEquals(0, $buf->size());
    }

    /**
     * @expectedException \PHPinnacle\Buffer\Exception\BufferUnderflow
     */
    public function testConsumeSliceThrows()
    {
        $buf = new Binary;
        $buf->shift(1);
    }

    public function testAppend()
    {
        $buf = new Binary;
        $this->assertEquals(0, $buf->size());

        $buf->append('abcd');
        $this->assertEquals(4, $buf->size());
        $this->assertEquals('abcd', $buf->read(4));

        $buf->append('efgh');
        $this->assertEquals(8, $buf->size());
        $this->assertEquals('abcdefgh', $buf->read(8));
    }

    public function testAppendBuffer()
    {
        $buf = new Binary;
        $this->assertEquals(0, $buf->size());

        $buf->append(new Binary('ab'));
        $this->assertEquals(2, $buf->size());
        $this->assertEquals('ab', $buf->read(2));

        $buf->append('cd');
        $this->assertEquals(4, $buf->size());
        $this->assertEquals('abcd', $buf->read(4));

        $buf->append(new Binary('ef'));
        $this->assertEquals(6, $buf->size());
        $this->assertEquals('abcdef', $buf->read(6));
    }

    // 8-bit integer functions

    public function testReadUint8()
    {
        $this->assertEquals(0xA9, (new Binary("\xA9"))->readUint8());
    }

    public function testReadInt8()
    {
        $this->assertEquals(0xA9 - 0x100, (new Binary("\xA9"))->readInt8());
    }

    public function testConsumeUint8()
    {
        $this->assertEquals(0xA9, (new Binary("\xA9"))->consumeUint8());
    }

    public function testConsumeInt8()
    {
        $this->assertEquals(0xA9 - 0x100, (new Binary("\xA9"))->consumeInt8());
    }

    public function testAppendUint8()
    {
        $this->assertEquals("\xA9", (new Binary)->appendUint8(0xA9)->read(1));
    }

    public function testAppendInt8()
    {
        $this->assertEquals("\xA9", (new Binary)->appendInt8(0xA9 - 0x100)->read(1));
    }

    // 16-bit integer functions

    public function testReadUint16()
    {
        $this->assertEquals(0xA978, (new Binary("\xA9\x78"))->readUint16());
    }

    public function testReadInt16()
    {
        $this->assertEquals(0xA978 - 0x10000, (new Binary("\xA9\x78"))->readInt16());
    }

    public function testConsumeUint16()
    {
        $this->assertEquals(0xA978, (new Binary("\xA9\x78"))->consumeUint16());
    }

    public function testConsumeInt16()
    {
        $this->assertEquals(0xA978 - 0x10000, (new Binary("\xA9\x78"))->consumeInt16());
    }

    public function testAppendUint16()
    {
        $this->assertEquals("\xA9\x78", (new Binary)->appendUint16(0xA978)->read(2));
    }

    public function testAppendInt16()
    {
        $this->assertEquals("\xA9\x78", (new Binary)->appendInt16(0xA978)->read(2));
    }

    // 32-bit integer functions

    public function testReadUint32()
    {
        $this->assertEquals(0xA9782361, (new Binary("\xA9\x78\x23\x61"))->readUint32());
    }

    public function testReadInt32()
    {
        $this->assertEquals(0xA9782361 - 0x100000000, (new Binary("\xA9\x78\x23\x61"))->readInt32());
    }

    public function testConsumeUint32()
    {
        $this->assertEquals(0xA9782361, (new Binary("\xA9\x78\x23\x61"))->consumeUint32());
    }

    public function testConsumeInt32()
    {
        $this->assertEquals(0xA9782361 - 0x100000000, (new Binary("\xA9\x78\x23\x61"))->consumeInt32());
    }

    public function testAppendUint32()
    {
        $this->assertEquals("\xA9\x78\x23\x61", (new Binary)->appendUint32(0xA9782361)->read(4));
    }

    public function testAppendInt32()
    {
        $this->assertEquals("\xA9\x78\x23\x61", (new Binary)->appendInt32(0xA9782361)->read(4));
    }

    // 64-bit integer functions

    public function testReadUint64()
    {
        $this->assertEquals(0x1978236134738525, (new Binary("\x19\x78\x23\x61\x34\x73\x85\x25"))->readUint64());
    }

    public function testReadInt64()
    {
        $this->assertEquals(-2, (new Binary("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->readInt64());
    }

    public function testConsumeUint64()
    {
        $this->assertEquals(0x1978236134738525, (new Binary("\x19\x78\x23\x61\x34\x73\x85\x25"))->consumeUint64());
    }

    public function testConsumeInt64()
    {
        $this->assertEquals(-2, (new Binary("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE"))->consumeInt64());
    }

    public function testAppendUint64()
    {
        $this->assertEquals("\x19\x78\x23\x61\x34\x73\x85\x25", (new Binary)->appendUint64(0x1978236134738525)->read(8));
    }

    public function testAppendInt64()
    {
        $this->assertEquals("\xFF\xFF\xFF\xFF\xFF\xFF\xFF\xFE", (new Binary)->appendInt64(-2)->read(8));
    }

    // float

    public function testReadFloat()
    {
        $this->assertEquals(1.5, (new Binary("\x3F\xC0\x00\x00"))->readFloat());
    }

    public function testConsumeFloat()
    {
        $this->assertEquals(1.5, (new Binary("\x3F\xC0\x00\x00"))->consumeFloat());
    }

    public function testAppendFloat()
    {
        $this->assertEquals("\x3F\xC0\x00\x00", (new Binary)->appendFloat(1.5)->read(4));
    }

    // double

    public function testReadDouble()
    {
        $this->assertEquals(1.5, (new Binary("\x3F\xF8\x00\x00\x00\x00\x00\x00"))->readDouble());
    }

    public function testConsumeDouble()
    {
        $this->assertEquals(1.5, (new Binary("\x3F\xF8\x00\x00\x00\x00\x00\x00"))->consumeDouble());
    }

    public function testAppendDouble()
    {
        $this->assertEquals("\x3F\xF8\x00\x00\x00\x00\x00\x00", (new Binary)->appendDouble(1.5)->read(8));
    }
}
