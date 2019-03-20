<?php

use PHPinnacle\Buffer\ByteBuffer;

/**
 * @BeforeMethods({"init"})
 */
class AppendBench
{
    /**
     * @var ByteBuffer
     */
    private $buffer;

    /**
     * @var string
     */
    private $string;

    /**
     * @return void
     */
    public function init(): void
    {
        $this->buffer = new ByteBuffer;
        $this->string = \str_repeat('str', 1000);
    }

    /**
     * @Revs(1000)
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
     * @Revs(1000)
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
     * @Revs(1000)
     * @Iterations(100)
     *
     * @return void
     */
    public function benchAppendString(): void
    {
        $this->buffer
            ->append('some string')
            ->append("other string")
            ->append($this->string)
        ;
    }
}
