--TEST--
Buffer to string test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer('abcd');

echo (string) $buffer . \PHP_EOL;
echo $buffer->__toString() . \PHP_EOL;
?>
--EXPECT--
abcd
abcd
