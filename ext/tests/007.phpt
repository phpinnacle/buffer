--TEST--
Buffer consume test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

echo $buffer->consume(1) . \PHP_EOL;
echo $buffer->consume(2) . \PHP_EOL;
echo $buffer->consume(1) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

try {
	$buffer->consume(1);
} catch (\PHPinnacle\Buffer\BufferOverflow $e) {
	echo $e->getMessage();
}
?>
--EXPECT--
a
bc
d
size: 0
Buffer overflow.