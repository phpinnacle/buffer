<?php
/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace PHPinnacle\Buffer;

if (!\class_exists('\PHPinnacle\Buffer\BufferOverflow'))
{
    final class BufferOverflow extends \Exception
    {
    }
}
