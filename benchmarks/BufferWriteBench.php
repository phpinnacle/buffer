<?php

use PHPinnacle\Buffer\ByteBuffer;

/**
 * @BeforeMethods({"init"})
 * @AfterMethods({"clear"})
 */
class BufferWriteBench
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
    }

    /**
     * @Revs(5)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchAppendIntegers(): void
    {
        $this->buffer
            ->appendInt8(1)
            ->appendInt16(1)
            ->appendInt32(1)
            ->appendInt64(1)
            ->appendUint8(1)
            ->appendUint16(1)
            ->appendUint32(1)
            ->appendUint64(1)
        ;
    }

    /**
     * @Revs(5)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchAppendFloats(): void
    {
        $this->buffer
            ->appendFloat(1.0)
            ->appendFloat(-1.0)
            ->appendFloat(\M_PI)
            ->appendDouble(1.0)
            ->appendDouble(-1.0)
            ->appendDouble(\M_PI)
        ;
    }

    /**
     * @Revs(5)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchAppendString(): void
    {
        $this->buffer
            ->append('some string')
            ->append("other string")
            ->append(str_repeat('str', 1000))
        ;
    }

    /**
     * @return void
     */
    public function clear(): void
    {
        $this->buffer->flush();
    }
}
