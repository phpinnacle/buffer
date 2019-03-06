--TEST--
Buffer shift test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

$shift3 = $buffer->shift(3);

echo $shift3->read(3) . \PHP_EOL;
echo $buffer->read(1) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

try {
	$buffer->shift(2);
} catch (\PHPinnacle\Buffer\BufferOverflow $e) {
	echo $e->getMessage();
}
?>
--EXPECT--
abc
d
size: 1
Buffer overflow.