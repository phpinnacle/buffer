--TEST--
Buffer empty test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer;

if ($buffer->empty()) {
	echo "Empty\n";
}

$buffer->append('a');

if (!$buffer->empty()) {
	echo "Not empty\n";
}
?>
--EXPECT--
Empty
Not empty