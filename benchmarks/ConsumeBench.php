<?php

use PHPinnacle\Buffer\ByteBuffer;

/**
 * @BeforeMethods({"init"})
 */
class ConsumeBench
{
    /**
     * @var ByteBuffer
     */
    private $buffer;

    /**
     * @var int
     */
    private $revs = 1000;

    /**
     * @return void
     */
    public function init(): void
    {
        $this->buffer = new ByteBuffer;

        for ($i = 0; $i < $this->revs; ++$i) {
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
    }

    /**
     * @Revs(1000)
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
}
