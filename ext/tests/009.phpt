--TEST--
Buffer slice test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

$slice3 = $buffer->slice(3);

echo $slice3->read(3) . \PHP_EOL;
echo "size: " . $buffer->size() . \PHP_EOL;

try {
	$buffer->slice(5);
} catch (\PHPinnacle\Buffer\BufferOverflow $e) {
	echo $e->getMessage();
}
?>
--EXPECT--
abc
size: 4
Buffer overflow.