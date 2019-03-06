--TEST--
Buffer read test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

echo $buffer->read(1) . \PHP_EOL;
echo $buffer->read(2) . \PHP_EOL;
echo $buffer->read(3) . \PHP_EOL;
echo $buffer->read(4) . \PHP_EOL;
echo $buffer->read(1, 1) . \PHP_EOL;
echo $buffer->read(3, 1) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

try {
	$buffer->read(5);
} catch (\PHPinnacle\Buffer\BufferOverflow $e) {
	echo $e->getMessage();
}
?>
--EXPECT--
a
ab
abc
abcd
b
bcd
size: 4
Buffer overflow.
