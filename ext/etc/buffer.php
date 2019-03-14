<?php
/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PHPinnacle\Buffer\ByteBuffer;

$buffer = new ByteBuffer;

$buffer->append('abcd');
$buffer->read(4);

$buffer->append(new ByteBuffer('zz'));
$buffer->consume(6);

$buffer->appendUint8(1);
$buffer->appendInt8(1);
$slice = $buffer->slice(2);
$slice->readUint8();
$slice->readInt8(1);
$buffer->consumeUint8();
$buffer->consumeInt8();

$buffer->appendUint16(1);
$buffer->appendInt16(1);
$shift = $buffer->shift(4);
$shift->consumeUint16();
$shift->consumeInt16();

$clone = clone $buffer;
$clone->appendUint32(1);

echo $buffer->flush();
echo (string) $clone;
