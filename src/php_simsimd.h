#ifndef PHP_SIMSIMD_H
#define PHP_SIMSIMD_H

#ifndef PHP_SIMSIMD_VERSION
#define PHP_SIMSIMD_VERSION "1.0.0"
#endif
#define PHP_SIMSIMD_LIBRARY "6.5.16"

extern zend_module_entry simsimd_module_entry;
#define phpext_simsimd_ptr &simsimd_module_entry

#endif
