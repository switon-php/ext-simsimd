PHP_ARG_ENABLE([simsimd],
  [whether to enable simsimd],
  [AS_HELP_STRING([--enable-simsimd], [Enable the SimSIMD distance extension])],
  [yes])

if test "$PHP_SIMSIMD" != "no"; then
  AC_DEFINE([HAVE_SIMSIMD], [1], [Have the SimSIMD extension])
  ln -sfn ../src "$srcdir/src"
  ln -sfn ../third_party "$srcdir/third_party"
  SIMSIMD_EXT_VERSION=`sh "$srcdir/../scripts/ext-version.sh"`
  printf '#define PHP_SIMSIMD_VERSION "%s"\n' "$SIMSIMD_EXT_VERSION" > "$srcdir/src/simsimd_version.h"
  PHP_NEW_EXTENSION([simsimd], [src/php_simsimd.c third_party/simsimd/c/lib.c], [$ext_shared])
  PHP_ADD_INCLUDE([$ext_srcdir/third_party/simsimd/include])
  PHP_ADD_BUILD_DIR([$ext_builddir/src])
  PHP_ADD_BUILD_DIR([$ext_builddir/third_party/simsimd/c])
fi
