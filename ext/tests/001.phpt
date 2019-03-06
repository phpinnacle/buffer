--TEST--
Check if buffer is loaded
--SKIPIF--
<?php
if (!extension_loaded('buffer')) {
	echo 'skip';
}
?>
--FILE--
<?php
echo 'The extension "buffer" is available';
?>
--EXPECT--
The extension "buffer" is available