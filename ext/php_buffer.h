/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#ifndef PHP_BUFFER_H
#define PHP_BUFFER_H

#define PHP_BUFFER_EXTNAME "buffer"
#define PHP_BUFFER_VERSION "0.1.0"

#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

extern "C" {
#include "php.h"
#include "ext/standard/info.h"
#include "zend_exceptions.h"
}

extern zend_module_entry buffer_module_entry;
#define buffer_module_ptr &buffer_module_entry
#define phpext_buffer_ptr buffer_module_ptr

#endif
