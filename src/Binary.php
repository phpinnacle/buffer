<?php
/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\Buffer;

class Binary
{
    /**
     * @var bool
     */
    private static $isLittleEndian;

    /**
     * @var bool
     */
    private static $native64BitPack;

    /**
     * @var string
     */
    private $data;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string $buffer
     */
    public function __construct(string $buffer = '')
    {
        $this->data = $buffer;
        $this->size = \strlen($this->data);

        if (!isset(self::$native64BitPack)) {
            self::$native64BitPack = PHP_INT_SIZE === 8;
            self::$isLittleEndian  = \unpack("S", "\x01\x00")[1] === 1;
        }
    }

    /**
     * @param string|self $value
     *
     * @return self
     */
    public function append($value): self
    {
        if ($value instanceof Binary) {
            $value = $value->data;
        }

        $this->data .= $value;
        $this->size += \strlen($value);

        return $this;
    }

    /**
     * @param int $n
     * @param int $offset
     *
     * @return string
     */
    public function read(int $n, int $offset = 0): string
    {
        if ($this->size < $offset + $n) {
            throw new Exception\BufferUnderflow;
        } elseif ($offset === 0 && $this->size === $offset + $n) {
            return $this->data;
        } else {
            return \substr($this->data, $offset, $n);
        }
    }

    /**
     * @param int $n
     *
     * @return string
     */
    public function consume(int $n): string
    {
        if ($this->size < $n) {
            throw new Exception\BufferUnderflow;
        } elseif ($this->size === $n) {
            $buffer = $this->data;

            $this->data = '';
            $this->size = 0;

            return $buffer;
        } else {
            $buffer = \substr($this->data, 0, $n);

            $this->data = \substr($this->data, $n);
            $this->size -= $n;

            return $buffer;
        }
    }

    /**
     * @param int $n
     *
     * @return self
     */
    public function discard(int $n): self
    {
        if ($this->size < $n) {
            throw new Exception\BufferUnderflow;
        } elseif ($this->size === $n) {
            $this->data = '';
            $this->size = 0;

            return $this;
        } else {
            $this->data = \substr($this->data, $n);
            $this->size -= $n;

            return $this;
        }
    }

    /**
     * @param int $n
     *
     * @return static
     */
    public function slice(int $n): self
    {
        if ($this->size < $n) {
            throw new Exception\BufferUnderflow;
        } elseif ($this->size === $n) {
            return new static($this->data);
        } else {
            return new static(\substr($this->data, 0, $n));
        }
    }

    /**
     * @param int $n
     *
     * @return static
     */
    public function shift(int $n): self
    {
        if ($this->size < $n) {
            throw new Exception\BufferUnderflow;
        } elseif ($this->size === $n) {
            $buffer = $this->data;

            $this->data = '';
            $this->size = 0;

            return new static($buffer);

        } else {
            $buffer = \substr($this->data, 0, $n);

            $this->data = \substr($this->data, $n);
            $this->size -= $n;

            return new static($buffer);
        }
    }

    /**
     * @return string
     */
    public function flush(): string
    {
        $data = $this->data;

        $this->data = '';
        $this->size = 0;

        return $data;
    }

    /**
     * @return int
     */
    public function size(): int
    {
        return $this->size;
    }

    /**
     * @return boolean
     */
    public function empty(): bool
    {
        return $this->size === 0;
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendInt8(int $value): self
    {
        return $this->append(\pack("c", $value));
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function readInt8(int $offset = 0): int
    {
        return \unpack("c", $this->read(1, $offset))[1];
    }
    
    /**
     * @return int
     */
    public function consumeInt8(): int
    {
        return \unpack("c", $this->consume(1))[1];
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendInt16(int $value): self
    {
        return $this->append(self::swapEndian16(\pack("s", $value)));
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function readInt16(int $offset = 0): int
    {
        return \unpack("s", self::swapEndian16($this->read(2, $offset)))[1];
    }

    /**
     * @return int
     */
    public function consumeInt16(): int
    {
        return \unpack("s", self::swapEndian16($this->consume(2)))[1];
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendInt32(int $value): self
    {
        return $this->append(self::swapEndian32(\pack("l", $value)));
    }
    
    /**
     * @param int $offset
     *
     * @return int
     */
    public function readInt32(int $offset = 0): int
    {
        return \unpack("l", self::swapEndian32($this->read(4, $offset)))[1];
    }
    
    /**
     * @return int
     */
    public function consumeInt32(): int
    {
        return \unpack("l", self::swapEndian32($this->consume(4)))[1];
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendInt64(int $value): self
    {
        if (self::$native64BitPack) {
            $s = self::swapEndian64(\pack("q", $value));
        } else {
            $s = \pack("LL", ($value & 0xffffffff00000000) >> 32, $value & 0x00000000ffffffff);
            $s = self::swapHalvedEndian64($s);
        }

        return $this->append($s);
    }

    /**
     * @param int $offset
     *
     * @return int
     */
    public function readInt64(int $offset = 0): int
    {
        $s = $this->read(8, $offset);
        
        if (self::$native64BitPack) {
            $r = \unpack("q", self::swapEndian64($s))[1];
        } else {
            $d = \unpack("Lh/Ll", self::swapHalvedEndian64($s));
            $r = $d["h"] << 32 | $d["l"];
        }
        
        return $r;
    }
    
    /**
     * @return int
     */
    public function consumeInt64(): int
    {
        $s = $this->consume(8);

        if (self::$native64BitPack) {
            $r = \unpack("q", self::swapEndian64($s))[1];
        } else {
            $d = \unpack("Lh/Ll", self::swapHalvedEndian64($s));
            $r = $d["h"] << 32 | $d["l"];
        }
        
        return $r;
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendUint8(int $value): self
    {
        return $this->append(\pack("C", $value));
    }
    
    /**
     * @param int $offset
     *
     * @return int
     */
    public function readUint8(int $offset = 0): int
    {
        return \unpack("C", $this->read(1, $offset))[1];
    }
    
    /**
     * @return int
     */
    public function consumeUint8(): int
    {
        $r = \unpack("C", $this->data)[1];
        
        $this->discard(1);
        
        return $r;
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendUint16(int $value): self
    {
        return $this->append(\pack("n", $value));
    }
    
    /**
     * @param int $offset
     *
     * @return int
     */
    public function readUint16(int $offset = 0): int
    {
        return \unpack("n", $this->read(2, $offset))[1];
    }
    
    /**
     * @return int
     */
    public function consumeUint16(): int
    {
        $r = \unpack("n", $this->data)[1];

        $this->discard(2);

        return $r;
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendUint32(int $value): self
    {
        return $this->append(\pack("N", $value));
    }
    
    /**
     * @param int $offset
     *
     * @return int
     */
    public function readUint32(int $offset = 0): int
    {
        return \unpack("N", $this->read(4, $offset))[1];
    }
    
    /**
     * @return int
     */
    public function consumeUint32(): int
    {
        $r = unpack("N", $this->data)[1];
        
        $this->discard(4);
        
        return $r;
    }
    
    /**
     * @param int $value
     *
     * @return self
     */
    public function appendUint64(int $value): self
    {
        if (self::$native64BitPack) {
            $s = self::swapEndian64(\pack("Q", $value));
        } else {
            $s = \pack("LL", ($value & 0xffffffff00000000) >> 32, $value & 0x00000000ffffffff);
            $s = self::swapHalvedEndian64($s);
        }
        
        return $this->append($s);
    }
    
    /**
     * @param int $offset
     *
     * @return int
     */
    public function readUint64(int $offset = 0): int
    {
        $s = $this->read(8, $offset);
        
        if (self::$native64BitPack) {
            $r = \unpack("Q", self::swapEndian64($s))[1];
        } else {
            $d = \unpack("Lh/Ll", self::swapHalvedEndian64($s));
            $r = $d["h"] << 32 | $d["l"];
        }
        
        return $r;
    }

    /**
     * @return int
     */
    public function consumeUint64(): int
    {
        $s = $this->consume(8);

        if (self::$native64BitPack) {
            $r = \unpack("Q", self::swapEndian64($s))[1];
        } else {
            $d = \unpack("Lh/Ll", self::swapHalvedEndian64($s));
            $r = $d["h"] << 32 | $d["l"];
        }

        return $r;
    }
    
    /**
     * @param float $value
     *
     * @return self
     */
    public function appendFloat(float $value): self
    {
        return $this->append(self::swapEndian32(\pack("f", $value)));
    }

    /**
     * @param int $offset
     *
     * @return float
     */
    public function readFloat(int $offset = 0): float
    {
        return \unpack("f", self::swapEndian32($this->read(4, $offset)))[1];
    }

    /**
     * @return float
     */
    public function consumeFloat(): float
    {
        return \unpack("f", self::swapEndian32($this->consume(4)))[1];
    }
    
    /**
     * @param float $value
     *
     * @return self
     */
    public function appendDouble($value): self
    {
        return $this->append(self::swapEndian64(\pack("d", $value)));
    }

    /**
     * @param int $offset
     *
     * @return float
     */
    public function readDouble(int $offset = 0): float
    {
        return \unpack("d", self::swapEndian64($this->read(8, $offset)))[1];
    }

    /**
     * @return float
     */
    public function consumeDouble(): float
    {
        return \unpack("d", self::swapEndian64($this->consume(8)))[1];
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->data;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian16(string $s): string
    {
        return self::$isLittleEndian ? $s[1] . $s[0] : $s;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian32(string $s): string
    {
        return self::$isLittleEndian ? $s[3] . $s[2] . $s[1] . $s[0] : $s;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapEndian64(string $s): string
    {
        return self::$isLittleEndian ? $s[7] . $s[6] . $s[5] . $s[4] . $s[3] . $s[2] . $s[1] . $s[0] : $s;
    }

    /**
     * @param string $s
     *
     * @return string
     */
    private static function swapHalvedEndian64(string $s): string
    {
        return self::$isLittleEndian ? $s[3] . $s[2] . $s[1] . $s[0] . $s[7] . $s[6] . $s[5] . $s[4] : $s;
    }
}
