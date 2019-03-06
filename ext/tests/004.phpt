--TEST--
Buffer flush test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

echo $buffer->flush() . \PHP_EOL;
echo $buffer->empty() ? 'Empty' : 'Not empty';
?>
--EXPECT--
abcd
Empty
