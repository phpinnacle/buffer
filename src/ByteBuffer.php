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

namespace PHPinnacle\Buffer;

if (!\class_exists('\PHPinnacle\Buffer\ByteBuffer'))
{
    class ByteBuffer
    {
        /**
         * @var bool
         */
        private static $isLittleEndian;

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

            if (!isset(self::$isLittleEndian)) {
                self::$isLittleEndian = \unpack("S", "\x01\x00")[1] === 1;
            }
        }

        /**
         * @param string|static $value
         *
         * @return static
         * @throws \TypeError
         */
        public function append($value): self
        {
            if ($value instanceof self) {
                $value = $value->data;
            }

            if (!\is_string($value)) {
                throw new \TypeError;
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
         * @throws BufferOverflow
         */
        public function read(int $n, int $offset = 0): string
        {
            if ($this->size < $offset + $n) {
                throw new BufferOverflow;
            }

            if ($offset === 0 && $this->size === $offset + $n) {
                return $this->data;
            }

            return \substr($this->data, $offset, $n);
        }

        /**
         * @param int $n
         *
         * @return string
         * @throws BufferOverflow
         */
        public function consume(int $n): string
        {
            if ($this->size < $n) {
                throw new BufferOverflow;
            }

            if ($this->size === $n) {
                $buffer = $this->data;

                $this->data = '';
                $this->size = 0;
            } else {
                $buffer = \substr($this->data, 0, $n);

                $this->data = \substr($this->data, $n);
                $this->size -= $n;
            }

            return $buffer;
        }

        /**
         * @param int $n
         *
         * @return static
         * @throws BufferOverflow
         */
        public function discard(int $n): self
        {
            if ($this->size < $n) {
                throw new BufferOverflow;
            }

            if ($this->size === $n) {
                $this->data = '';
                $this->size = 0;
            } else {
                $this->data = \substr($this->data, $n);
                $this->size -= $n;
            }

            return $this;
        }

        /**
         * @param int $n
         * @param int $offset
         *
         * @return static
         * @throws BufferOverflow
         */
        public function slice(int $n, int $offset = 0): self
        {
            if ($this->size < $n) {
                throw new BufferOverflow;
            }

            return $this->size === $n ? new static($this->data) : new static(\substr($this->data, $offset, $n));
        }

        /**
         * @param int $n
         *
         * @return static
         * @throws BufferOverflow
         */
        public function shift(int $n): self
        {
            if ($this->size < $n) {
                throw new BufferOverflow;
            }

            if ($this->size === $n) {
                $buffer = $this->data;

                $this->data = '';
                $this->size = 0;
            } else {
                $buffer = \substr($this->data, 0, $n);
                
                $this->data = \substr($this->data, $n);
                $this->size -= $n;
            }

            return new static($buffer);
        }

        /**
         * @return string
         */
        public function bytes(): string
        {
            return $this->data;
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
         * @return static
         */
        public function appendInt8(int $value): self
        {
            return $this->append(\pack("c", $value));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readInt8(int $offset = 0): int
        {
            return \unpack("c", $this->read(1, $offset))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeInt8(): int
        {
            return \unpack("c", $this->consume(1))[1];
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendInt16(int $value): self
        {
            return $this->append(self::swapEndian16(\pack("s", $value)));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readInt16(int $offset = 0): int
        {
            return \unpack("s", self::swapEndian16($this->read(2, $offset)))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeInt16(): int
        {
            return \unpack("s", self::swapEndian16($this->consume(2)))[1];
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendInt32(int $value): self
        {
            return $this->append(self::swapEndian32(\pack("l", $value)));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readInt32(int $offset = 0): int
        {
            return \unpack("l", self::swapEndian32($this->read(4, $offset)))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeInt32(): int
        {
            return \unpack("l", self::swapEndian32($this->consume(4)))[1];
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendInt64(int $value): self
        {
            return $this->append(self::swapEndian64(\pack("q", $value)));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readInt64(int $offset = 0): int
        {
            return \unpack("q", self::swapEndian64($this->read(8, $offset)))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeInt64(): int
        {
            return \unpack("q", self::swapEndian64($this->consume(8)))[1];
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendUint8(int $value): self
        {
            return $this->append(\pack("C", $value));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readUint8(int $offset = 0): int
        {
            return \unpack("C", $this->read(1, $offset))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
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
         * @return static
         */
        public function appendUint16(int $value): self
        {
            return $this->append(\pack("n", $value));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readUint16(int $offset = 0): int
        {
            return \unpack("n", $this->read(2, $offset))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
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
         * @return static
         */
        public function appendUint32(int $value): self
        {
            return $this->append(\pack("N", $value));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readUint32(int $offset = 0): int
        {
            return \unpack("N", $this->read(4, $offset))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeUint32(): int
        {
            $r = \unpack("N", $this->data)[1];

            $this->discard(4);

            return $r;
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendUint32LE(int $value): self
        {
            return $this->append(\pack("V", $value));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readUint32LE(int $offset = 0): int
        {
            return \unpack("V", $this->read(4, $offset))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeUint32LE(): int
        {
            $r = \unpack("V", $this->data)[1];

            $this->discard(4);

            return $r;
        }

        /**
         * @param int $value
         *
         * @return static
         */
        public function appendUint64(int $value): self
        {
            return $this->append(self::swapEndian64(\pack("Q", $value)));
        }

        /**
         * @param int $offset
         *
         * @return int
         * @throws BufferOverflow
         */
        public function readUint64(int $offset = 0): int
        {
            return \unpack("Q", self::swapEndian64($this->read(8, $offset)))[1];
        }

        /**
         * @return int
         * @throws BufferOverflow
         */
        public function consumeUint64(): int
        {
            return \unpack("Q", self::swapEndian64($this->consume(8)))[1];
        }

        /**
         * @param float $value
         *
         * @return static
         */
        public function appendFloat(float $value): self
        {
            return $this->append(self::swapEndian32(\pack("f", $value)));
        }

        /**
         * @param int $offset
         *
         * @return float
         * @throws BufferOverflow
         */
        public function readFloat(int $offset = 0): float
        {
            return \unpack("f", self::swapEndian32($this->read(4, $offset)))[1];
        }

        /**
         * @return float
         * @throws BufferOverflow
         */
        public function consumeFloat(): float
        {
            return \unpack("f", self::swapEndian32($this->consume(4)))[1];
        }

        /**
         * @param float $value
         *
         * @return static
         */
        public function appendDouble($value): self
        {
            return $this->append(self::swapEndian64(\pack("d", $value)));
        }

        /**
         * @param int $offset
         *
         * @return float
         * @throws BufferOverflow
         */
        public function readDouble(int $offset = 0): float
        {
            return \unpack("d", self::swapEndian64($this->read(8, $offset)))[1];
        }

        /**
         * @return float
         * @throws BufferOverflow
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
    }
}
