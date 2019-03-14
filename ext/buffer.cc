/**
 * This file is part of PHPinnacle/Buffer.
 *
 * (c) PHPinnacle Team <dev@phpinnacle.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

#include "php_buffer.h"
#include "Buffer.hpp"

#define GUARD_SIZE(b, s) { if (guard_data_size(b, s) == false) return; }

typedef struct _buffer_object  {
	Buffer *data;
	zend_object std;
} buffer_object;

static zend_class_entry *buffer_object_ce = NULL;
static zend_class_entry *buffer_exception_ce = NULL;
static zend_object_handlers buffer_object_handlers;

static void copy_zend_string_to_buffer(zend_string *str, Buffer *data)
{
	char *input = ZSTR_VAL(str);
	zend_long input_len = ZSTR_LEN(str);

    for (int i = 0; i < input_len; i++)
        data->appendInt8(input[i]);
}

static zend_object* buffer_object_to_zend_object(buffer_object *objval)
{
    return ((zend_object*)(objval + 1)) - 1;
}

static buffer_object* buffer_object_from_zend_object(zend_object *objval)
{
    return ((buffer_object*)(objval + 1)) - 1;
}

static bool guard_data_size(buffer_object * buffer, unsigned int size)
{
    if (size > buffer->data->size()) {
        zend_throw_exception(buffer_exception_ce, "Buffer overflow.", 0);

        return false;
    }

    return true;
}

PHP_METHOD(ByteBuffer, __construct)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

	objval->data = new Buffer();

    if (ZEND_NUM_ARGS() == 0) {
        return;
    }

    zval *val;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "z", &val) == FAILURE) {
        return;
    }

    if (Z_TYPE_P(val) == IS_STRING) {
        copy_zend_string_to_buffer(Z_STR_P(val), objval->data);
    } else if (Z_TYPE_P(val) == IS_OBJECT && instanceof_function(Z_OBJCE_P(val), buffer_object_ce) != 0) {
        buffer_object *appval = buffer_object_from_zend_object(Z_OBJ_P(val));

        objval->data->merge(*appval->data);
    } else {
        zend_type_error("Invalid ByteBuffer constructor arguments.");

        return;
    }
}

PHP_METHOD(ByteBuffer, __toString)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    std::string s = objval->data->bytes();

    RETURN_NEW_STR(zend_string_init(s.data(), s.size(), 0));
}

PHP_METHOD(ByteBuffer, bytes)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    std::string s = objval->data->bytes();

    RETURN_NEW_STR(zend_string_init(s.data(), s.size(), 0));
}

PHP_METHOD(ByteBuffer, size)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    RETURN_LONG(objval->data->size());
}

PHP_METHOD(ByteBuffer, empty)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    RETURN_BOOL(objval->data->empty());
}

PHP_METHOD(ByteBuffer, append)
{
    if (ZEND_NUM_ARGS() == 0) {
        return;
    }

    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zval *val;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "z", &val) == FAILURE) {
        return;
    }

    if (Z_TYPE_P(val) == IS_STRING) {
        copy_zend_string_to_buffer(Z_STR_P(val), objval->data);
    } else if (Z_TYPE_P(val) == IS_OBJECT && instanceof_function(Z_OBJCE_P(val), buffer_object_ce) != 0) {
        buffer_object *appval = buffer_object_from_zend_object(Z_OBJ_P(val));

        objval->data->merge(*appval->data);
    } else {
        zend_type_error("Invalid argument for append.");
        return;
    }

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, read)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long size = 0;
    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l|l", &size, &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + size);

    std::string s = objval->data->readString(size, offset);

    RETURN_NEW_STR(zend_string_init(s.data(), s.size(), 0));
}

PHP_METHOD(ByteBuffer, consume)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long size = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &size) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, size);

    std::string s = objval->data->consumeString(size);

    RETURN_NEW_STR(zend_string_init(s.data(), s.size(), 0));
}

PHP_METHOD(ByteBuffer, discard)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long size = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &size) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, size);

    objval->data->discard(size);
}

PHP_METHOD(ByteBuffer, slice)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long size = 0;
    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l|l", &size, &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + size);

	buffer_object *new_obj = NULL;

	object_init_ex(return_value, objval->std.ce);

	new_obj = buffer_object_from_zend_object(Z_OBJ_P(return_value));
	new_obj->data = new Buffer();
    new_obj->data->appendString(objval->data->readString(size, offset));
}

PHP_METHOD(ByteBuffer, shift)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long size = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &size) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, size);

	buffer_object *new_obj = NULL;

	object_init_ex(return_value, objval->std.ce);

	new_obj = buffer_object_from_zend_object(Z_OBJ_P(return_value));
	new_obj->data = new Buffer();
    new_obj->data->appendString(objval->data->consumeString(size));
}

PHP_METHOD(ByteBuffer, flush)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    std::string s = objval->data->flush();

    RETURN_NEW_STR(zend_string_init(s.data(), s.size(), 0));
}

PHP_METHOD(ByteBuffer, appendBool)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_bool value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "b", &value) == FAILURE) {
        return;
    }

    objval->data->appendBoolean(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readBool)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 1);

    RETURN_BOOL(objval->data->readBoolean(offset));
}

PHP_METHOD(ByteBuffer, consumeBool)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 1);

    RETURN_BOOL(objval->data->consumeBool());
}

PHP_METHOD(ByteBuffer, appendInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendInt8(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 1);

    RETURN_LONG(objval->data->readInt8(offset));
}

PHP_METHOD(ByteBuffer, consumeInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 1);

    RETURN_LONG(objval->data->consumeInt8());
}

PHP_METHOD(ByteBuffer, appendUInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendUInt8(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readUInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 1);

    RETURN_LONG(objval->data->readUInt8(offset));
}

PHP_METHOD(ByteBuffer, consumeUInt8)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 1);

    RETURN_LONG(objval->data->consumeUInt8());
}

PHP_METHOD(ByteBuffer, appendInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendInt16(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 2);

    RETURN_LONG(objval->data->readInt16(offset));
}

PHP_METHOD(ByteBuffer, consumeInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 2);

    RETURN_LONG(objval->data->consumeInt16());
}

PHP_METHOD(ByteBuffer, appendUInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendUInt16(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readUInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 2);

    RETURN_LONG(objval->data->readUInt16(offset));
}

PHP_METHOD(ByteBuffer, consumeUInt16)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 2);

    RETURN_LONG(objval->data->consumeUInt16());
}

PHP_METHOD(ByteBuffer, appendInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendInt32(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 4);

    RETURN_LONG(objval->data->readInt32(offset));
}

PHP_METHOD(ByteBuffer, consumeInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 4);

    RETURN_LONG(objval->data->consumeInt32());
}

PHP_METHOD(ByteBuffer, appendUInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendUInt32(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readUInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 4);

    RETURN_LONG(objval->data->readUInt32(offset));
}

PHP_METHOD(ByteBuffer, consumeUInt32)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 4);

    RETURN_LONG(objval->data->consumeUInt32());
}

PHP_METHOD(ByteBuffer, appendInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendInt64(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 8);

    RETURN_LONG(objval->data->readInt64(offset));
}

PHP_METHOD(ByteBuffer, consumeInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 8);

    RETURN_LONG(objval->data->consumeInt64());
}

PHP_METHOD(ByteBuffer, appendUInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "l", &value) == FAILURE) {
        return;
    }

    objval->data->appendUInt64(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readUInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 8);

    RETURN_LONG(objval->data->readUInt64(offset));
}

PHP_METHOD(ByteBuffer, consumeUInt64)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 8);

    RETURN_LONG(objval->data->consumeUInt64());
}

PHP_METHOD(ByteBuffer, appendFloat)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    double value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "d", &value) == FAILURE) {
        return;
    }

    objval->data->appendFloat(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readFloat)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 4);

    RETURN_DOUBLE(objval->data->readFloat(offset));
}

PHP_METHOD(ByteBuffer, consumeFloat)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 4);

    RETURN_DOUBLE(objval->data->consumeFloat());
}

PHP_METHOD(ByteBuffer, appendDouble)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    double value;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "d", &value) == FAILURE) {
        return;
    }

    objval->data->appendDouble(value);

    RETURN_ZVAL(getThis(), 1, 0);
}

PHP_METHOD(ByteBuffer, readDouble)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    zend_long offset = 0;

    if (zend_parse_parameters_throw(ZEND_NUM_ARGS(), "|l", &offset) == FAILURE) {
        return;
    }

    GUARD_SIZE(objval, offset + 8);

    RETURN_DOUBLE(objval->data->readDouble(offset));
}

PHP_METHOD(ByteBuffer, consumeDouble)
{
    buffer_object *objval = buffer_object_from_zend_object(Z_OBJ_P(getThis()));

    GUARD_SIZE(objval, 8);

    RETURN_DOUBLE(objval->data->consumeDouble());
}

static zend_function_entry buffer_object_methods[] = {
    PHP_ME(ByteBuffer, __construct, NULL, ZEND_ACC_CTOR | ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, __toString, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, bytes, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, size, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, empty, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, append, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, read, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consume, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, discard, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, slice, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, shift, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, flush, NULL, ZEND_ACC_PUBLIC)
    // BOOL
    PHP_ME(ByteBuffer, appendBool, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readBool, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeBool, NULL, ZEND_ACC_PUBLIC)
    // INT 8
    PHP_ME(ByteBuffer, appendInt8, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readInt8, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeInt8, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, appendUInt8, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readUInt8, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeUInt8, NULL, ZEND_ACC_PUBLIC)
    // INT 16
    PHP_ME(ByteBuffer, appendInt16, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readInt16, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeInt16, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, appendUInt16, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readUInt16, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeUInt16, NULL, ZEND_ACC_PUBLIC)
    // INT 32
    PHP_ME(ByteBuffer, appendInt32, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readInt32, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeInt32, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, appendUInt32, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readUInt32, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeUInt32, NULL, ZEND_ACC_PUBLIC)
    // INT 64
    PHP_ME(ByteBuffer, appendInt64, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readInt64, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeInt64, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, appendUInt64, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readUInt64, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeUInt64, NULL, ZEND_ACC_PUBLIC)
    // FLOAT
    PHP_ME(ByteBuffer, appendFloat, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readFloat, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeFloat, NULL, ZEND_ACC_PUBLIC)
    // DOUBLE
    PHP_ME(ByteBuffer, appendDouble, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, readDouble, NULL, ZEND_ACC_PUBLIC)
    PHP_ME(ByteBuffer, consumeDouble, NULL, ZEND_ACC_PUBLIC)

    PHP_FE_END
};

static zend_object* buffer_object_create(zend_class_entry *ce) {
    buffer_object *objval = (buffer_object*) ecalloc(1, sizeof(buffer_object) + zend_object_properties_size(ce));

    zend_object* ret = buffer_object_to_zend_object(objval);

    zend_object_std_init(ret, ce);
    object_properties_init(ret, ce);

    ret->handlers = &buffer_object_handlers;

    return ret;
}

static zend_object *buffer_object_clone(zval *object)
{
	buffer_object *old_obj = buffer_object_from_zend_object(Z_OBJ_P(object));
	buffer_object *new_obj = buffer_object_from_zend_object(buffer_object_create(old_obj->std.ce));

	zend_objects_clone_members(&new_obj->std, &old_obj->std);

	new_obj->data = new Buffer();
    new_obj->data->merge(*old_obj->data);

	return &new_obj->std;
}

static void buffer_object_free(zend_object *zobj)
{
    buffer_object *obj = buffer_object_from_zend_object(zobj);

    delete obj->data;

    zend_object_std_dtor(zobj);
}

static void buffer_object_destroy(zend_object *object)
{
    zend_objects_destroy_object(object);
}

static PHP_MINIT_FUNCTION(buffer)
{
    zend_class_entry bce;
    zend_class_entry ece;

    INIT_CLASS_ENTRY(bce, "PHPinnacle\\Buffer\\ByteBuffer", buffer_object_methods);
	INIT_CLASS_ENTRY(ece, "PHPinnacle\\Buffer\\BufferOverflow", NULL);

	buffer_exception_ce = zend_register_internal_class_ex(&ece, zend_ce_exception);

    buffer_object_ce = zend_register_internal_class(&bce);
    buffer_object_ce->create_object = buffer_object_create;

    memcpy(&buffer_object_handlers, zend_get_std_object_handlers(), sizeof(zend_object_handlers));

    buffer_object_handlers.offset = XtOffsetOf(buffer_object, std);
    buffer_object_handlers.clone_obj = buffer_object_clone;
    buffer_object_handlers.free_obj = buffer_object_free;
    buffer_object_handlers.dtor_obj = buffer_object_destroy;

    return SUCCESS;
}

static PHP_MSHUTDOWN_FUNCTION(buffer)
{
    return SUCCESS;
}

static PHP_MINFO_FUNCTION(buffer)
{
    php_info_print_table_start();
    php_info_print_table_header(2, "PHPinnacle ByteBuffer support", "enabled");
    php_info_print_table_end();
}

zend_module_entry buffer_module_entry = {
	STANDARD_MODULE_HEADER,
    "buffer",
    NULL,
    PHP_MINIT(buffer),
    PHP_MSHUTDOWN(buffer),
    NULL,
    NULL,
    PHP_MINFO(buffer),
    PHP_BUFFER_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_BUFFER
extern "C" {
  ZEND_GET_MODULE(buffer)
}
#endif
