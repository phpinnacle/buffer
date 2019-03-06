--TEST--
Buffer append test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer;

echo "size: " . $buffer->size() . \PHP_EOL;

$buffer->append('abcd');

echo $buffer->read(4) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

$buffer->append('efgh');

echo $buffer->read(8) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

$buffer->append(new PHPinnacle\Buffer\ByteBuffer('zz'));

echo $buffer->read(10) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;
?>
--EXPECT--
size: 0
abcd
size: 4
abcdefgh
size: 8
abcdefghzz
size: 10