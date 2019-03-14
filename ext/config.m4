dnl config.m4 for extension buffer

dnl Comments in this file start with the string 'dnl'.
dnl Remove where necessary.

dnl If your extension references something external, use with:

dnl PHP_ARG_WITH(buffer, for buffer support,
dnl Make sure that the comment is aligned:
dnl [  --with-buffer             Include buffer support])

dnl Otherwise use enable:

PHP_ARG_ENABLE(buffer, whether to enable buffer support,
dnl Make sure that the comment is aligned:
[  --enable-buffer          Enable byte buffer support], no)

if test "$PHP_BUFFER" != "no"; then
  dnl Write more examples of tests here...

  dnl # get library FOO build options from pkg-config output
  dnl AC_PATH_PROG(PKG_CONFIG, pkg-config, no)
  dnl AC_MSG_CHECKING(for libfoo)
  dnl if test -x "$PKG_CONFIG" && $PKG_CONFIG --exists foo; then
  dnl   if $PKG_CONFIG foo --atleast-version 1.2.3; then
  dnl     LIBFOO_CFLAGS=\`$PKG_CONFIG foo --cflags\`
  dnl     LIBFOO_LIBDIR=\`$PKG_CONFIG foo --libs\`
  dnl     LIBFOO_VERSON=\`$PKG_CONFIG foo --modversion\`
  dnl     AC_MSG_RESULT(from pkgconfig: version $LIBFOO_VERSON)
  dnl   else
  dnl     AC_MSG_ERROR(system libfoo is too old: version 1.2.3 required)
  dnl   fi
  dnl else
  dnl   AC_MSG_ERROR(pkg-config not found)
  dnl fi
  dnl PHP_EVAL_LIBLINE($LIBFOO_LIBDIR, BUFFER_SHARED_LIBADD)
  dnl PHP_EVAL_INCLINE($LIBFOO_CFLAGS)

  dnl # --with-buffer -> check with-path
  dnl SEARCH_PATH="/usr/local /usr"     # you might want to change this
  dnl SEARCH_FOR="/include/buffer.h"  # you most likely want to change this
  dnl if test -r $PHP_BUFFER/$SEARCH_FOR; then # path given as parameter
  dnl   BUFFER_DIR=$PHP_BUFFER
  dnl else # search default path list
  dnl   AC_MSG_CHECKING([for buffer files in default path])
  dnl   for i in $SEARCH_PATH ; do
  dnl     if test -r $i/$SEARCH_FOR; then
  dnl       BUFFER_DIR=$i
  dnl       AC_MSG_RESULT(found in $i)
  dnl     fi
  dnl   done
  dnl fi
  dnl
  dnl if test -z "$BUFFER_DIR"; then
  dnl   AC_MSG_RESULT([not found])
  dnl   AC_MSG_ERROR([Please reinstall the buffer distribution])
  dnl fi

  dnl # --with-buffer -> add include path
  dnl PHP_ADD_INCLUDE($BUFFER_DIR/include)

  dnl # --with-buffer -> check for lib and symbol presence
  dnl LIBNAME=BUFFER # you may want to change this
  dnl LIBSYMBOL=BUFFER # you most likely want to change this

  dnl PHP_CHECK_LIBRARY($LIBNAME,$LIBSYMBOL,
  dnl [
  dnl   PHP_ADD_LIBRARY_WITH_PATH($LIBNAME, $BUFFER_DIR/$PHP_LIBDIR, BUFFER_SHARED_LIBADD)
  dnl   AC_DEFINE(HAVE_BUFFERLIB,1,[ ])
  dnl ],[
  dnl   AC_MSG_ERROR([wrong buffer lib version or lib not found])
  dnl ],[
  dnl   -L$BUFFER_DIR/$PHP_LIBDIR -lm
  dnl ])
  dnl
  dnl PHP_SUBST(BUFFER_SHARED_LIBADD)

  PHP_REQUIRE_CXX()
  PHP_SUBST(BUFFER_SHARED_LIBADD)
  PHP_ADD_LIBRARY(stdc++, 1, BUFFER_SHARED_LIBADD)
  PHP_NEW_EXTENSION(buffer, buffer.cc Buffer.cpp,  $ext_shared)
fi
