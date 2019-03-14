--TEST--
Buffer size test
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
$buffer = new PHPinnacle\Buffer\ByteBuffer;

echo "size: {$buffer->size()}\n";

$buffer->append('a');
echo "size: {$buffer->size()}\n";

$buffer->append('a');
echo "size: {$buffer->size()}\n";

$buffer->read(1);
echo "size: {$buffer->size()}\n";

$buffer->read(2);
echo "size: {$buffer->size()}\n";

$buffer->consume(1);
echo "size: {$buffer->size()}\n";

$buffer->consume(1);
echo "size: {$buffer->size()}\n";
?>
--EXPECT--
size: 0
size: 1
size: 2
size: 2
size: 2
size: 1
size: 0
