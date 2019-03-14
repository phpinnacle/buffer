--TEST--
Buffer discard test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

$buffer->discard(1);

echo $buffer->read(3) . \PHP_EOL;

$buffer->discard(2);

echo $buffer->read(1) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

try {
	$buffer->discard(2);
} catch (\PHPinnacle\Buffer\BufferOverflow $e) {
	echo $e->getMessage();
}
?>
--EXPECT--
bcd
d
size: 1
Buffer overflow.