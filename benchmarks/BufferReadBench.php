<?php

use PHPinnacle\Buffer\ByteBuffer;

/**
 * @BeforeMethods({"init"})
 * @AfterMethods({"clear"})
 */
class BufferReadBench
{
    /**
     * @var ByteBuffer
     */
    private $buffer;

    /**
     * @return void
     */
    public function init(): void
    {
        $this->buffer = new ByteBuffer();
        $this->buffer
            ->appendInt8(1)
            ->appendInt16(1)
            ->appendInt32(1)
            ->appendInt64(1)
            ->appendUint8(1)
            ->appendUint16(1)
            ->appendUint32(1)
            ->appendUint64(1)
            ->appendFloat(1.1)
            ->appendFloat(-1.1)
            ->appendFloat(\M_PI)
            ->appendDouble(1.1)
            ->appendDouble(-1.1)
            ->appendDouble(\M_PI)
            ->append('some string')
            ->append("other string")
        ;
    }

    /**
     * @Revs(1)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchConsume(): void
    {
        $this->buffer->consumeInt8();
        $this->buffer->consumeInt16();
        $this->buffer->consumeInt32();
        $this->buffer->consumeInt64();
        $this->buffer->consumeUint8();
        $this->buffer->consumeUint16();
        $this->buffer->consumeUint32();
        $this->buffer->consumeUint64();
        $this->buffer->consumeFloat();
        $this->buffer->consumeFloat();
        $this->buffer->consumeFloat();
        $this->buffer->consumeDouble();
        $this->buffer->consumeDouble();
        $this->buffer->consumeDouble();
        $this->buffer->consume(11);
        $this->buffer->consume(12);
    }

    /**
     * @Revs(1)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchRead(): void
    {
        $this->buffer->readInt8();
        $this->buffer->readInt16(1);
        $this->buffer->readInt32(3);
        $this->buffer->readInt64(7);
        $this->buffer->readUint8(15);
        $this->buffer->readUint16(16);
        $this->buffer->readUint32(18);
        $this->buffer->readUint64(22);
        $this->buffer->readFloat(30);
        $this->buffer->readFloat(34);
        $this->buffer->readFloat(38);
        $this->buffer->readDouble(42);
        $this->buffer->readDouble(50);
        $this->buffer->readDouble(58);
        $this->buffer->read(11, 66);
        $this->buffer->read(12, 77);
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->buffer->flush();
    }
}
