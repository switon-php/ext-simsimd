#ifdef HAVE_CONFIG_H
#include "config.h"
#endif

#include "php.h"
#include "zend_exceptions.h"
#include "ext/standard/info.h"
#if defined(__has_include)
# if __has_include("simsimd_version.h")
#  include "simsimd_version.h"
# endif
#endif
#include "php_simsimd.h"

#define SIMSIMD_DYNAMIC_DISPATCH 1
#define SIMSIMD_NATIVE_F16 0
#define SIMSIMD_NATIVE_BF16 0
#include <simsimd/simsimd.h>

/* lib.c exports these. The 6.5.16 header only declares them when dynamic dispatch is off. */
void simsimd_intersect_u16(simsimd_u16_t const *a, simsimd_u16_t const *b, simsimd_size_t a_length, simsimd_size_t b_length, simsimd_distance_t *d);
void simsimd_intersect_u32(simsimd_u32_t const *a, simsimd_u32_t const *b, simsimd_size_t a_length, simsimd_size_t b_length, simsimd_distance_t *d);
void simsimd_bilinear_f64(simsimd_f64_t const *a, simsimd_f64_t const *b, simsimd_f64_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_f32(simsimd_f32_t const *a, simsimd_f32_t const *b, simsimd_f32_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_f16(simsimd_f16_t const *a, simsimd_f16_t const *b, simsimd_f16_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_bf16(simsimd_bf16_t const *a, simsimd_bf16_t const *b, simsimd_bf16_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_f64c(simsimd_f64c_t const *a, simsimd_f64c_t const *b, simsimd_f64c_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_f32c(simsimd_f32c_t const *a, simsimd_f32c_t const *b, simsimd_f32c_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_f16c(simsimd_f16c_t const *a, simsimd_f16c_t const *b, simsimd_f16c_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_bilinear_bf16c(simsimd_bf16c_t const *a, simsimd_bf16c_t const *b, simsimd_bf16c_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_mahalanobis_f64(simsimd_f64_t const *a, simsimd_f64_t const *b, simsimd_f64_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_mahalanobis_f32(simsimd_f32_t const *a, simsimd_f32_t const *b, simsimd_f32_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_mahalanobis_f16(simsimd_f16_t const *a, simsimd_f16_t const *b, simsimd_f16_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_mahalanobis_bf16(simsimd_bf16_t const *a, simsimd_bf16_t const *b, simsimd_bf16_t const *c, simsimd_size_t n, simsimd_distance_t *d);
void simsimd_wsum_f64(simsimd_f64_t const *a, simsimd_f64_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f64_t *result);
void simsimd_wsum_f32(simsimd_f32_t const *a, simsimd_f32_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f32_t *result);
void simsimd_wsum_f16(simsimd_f16_t const *a, simsimd_f16_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f16_t *result);
void simsimd_wsum_bf16(simsimd_bf16_t const *a, simsimd_bf16_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_bf16_t *result);
void simsimd_wsum_i8(simsimd_i8_t const *a, simsimd_i8_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_i8_t *result);
void simsimd_wsum_u8(simsimd_u8_t const *a, simsimd_u8_t const *b, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_u8_t *result);
void simsimd_fma_f64(simsimd_f64_t const *a, simsimd_f64_t const *b, simsimd_f64_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f64_t *result);
void simsimd_fma_f32(simsimd_f32_t const *a, simsimd_f32_t const *b, simsimd_f32_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f32_t *result);
void simsimd_fma_f16(simsimd_f16_t const *a, simsimd_f16_t const *b, simsimd_f16_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_f16_t *result);
void simsimd_fma_bf16(simsimd_bf16_t const *a, simsimd_bf16_t const *b, simsimd_bf16_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_bf16_t *result);
void simsimd_fma_i8(simsimd_i8_t const *a, simsimd_i8_t const *b, simsimd_i8_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_i8_t *result);
void simsimd_fma_u8(simsimd_u8_t const *a, simsimd_u8_t const *b, simsimd_u8_t const *c, simsimd_size_t n, simsimd_distance_t alpha, simsimd_distance_t beta, simsimd_u8_t *result);

static zend_class_entry *simsimd_vector_ce;
static zend_class_entry *simsimd_exception_ce;
static zend_class_entry *simsimd_length_exception_ce;
static zend_class_entry *simsimd_value_exception_ce;

typedef enum {
    SIMSIMD_KIND_F64,
    SIMSIMD_KIND_F32,
    SIMSIMD_KIND_F16,
    SIMSIMD_KIND_BF16,
    SIMSIMD_KIND_I8,
    SIMSIMD_KIND_U8
} simsimd_php_kind;

typedef struct {
    void *data;
    size_t n;
    zend_bool owned;
} simsimd_buf;

static char simsimd_empty_byte;

static zend_result simsimd_parse_kind(const char *kind, size_t kind_len, simsimd_php_kind *out)
{
    if (kind_len == 3 && memcmp(kind, "f64", 3) == 0) {
        *out = SIMSIMD_KIND_F64;
        return SUCCESS;
    }
    if (kind_len == 3 && memcmp(kind, "f32", 3) == 0) {
        *out = SIMSIMD_KIND_F32;
        return SUCCESS;
    }
    if (kind_len == 3 && memcmp(kind, "f16", 3) == 0) {
        *out = SIMSIMD_KIND_F16;
        return SUCCESS;
    }
    if (kind_len == 4 && memcmp(kind, "bf16", 4) == 0) {
        *out = SIMSIMD_KIND_BF16;
        return SUCCESS;
    }
    if (kind_len == 2 && memcmp(kind, "i8", 2) == 0) {
        *out = SIMSIMD_KIND_I8;
        return SUCCESS;
    }
    if (kind_len == 2 && memcmp(kind, "u8", 2) == 0) {
        *out = SIMSIMD_KIND_U8;
        return SUCCESS;
    }
    zend_throw_exception(simsimd_value_exception_ce, "kind must be f64, f32, f16, bf16, i8, or u8", 0);
    return FAILURE;
}

static void simsimd_buf_clear(simsimd_buf *buf)
{
    buf->data = &simsimd_empty_byte;
    buf->n = 0;
    buf->owned = 0;
}

static void simsimd_buf_release(simsimd_buf *buf)
{
    if (buf->owned) {
        efree(buf->data);
    }
    simsimd_buf_clear(buf);
}

static void simsimd_throw_length(size_t left, size_t right)
{
    zend_throw_exception_ex(
        simsimd_length_exception_ce,
        0,
        "vectors must have the same length, %zu and %zu",
        left,
        right
    );
}

static zend_result simsimd_require_list(HashTable *ht)
{
    if (zend_hash_num_elements(ht) > 0 && !zend_array_is_list(ht)) {
        zend_throw_exception(simsimd_value_exception_ce, "vector must be a list", 0);
        return FAILURE;
    }
    return SUCCESS;
}

static zend_result simsimd_read_f64(HashTable *ht, simsimd_f64_t **out, size_t *n)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    *out = emalloc(count * sizeof(simsimd_f64_t));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        if (Z_TYPE_P(val) == IS_LONG) {
            (*out)[i++] = (simsimd_f64_t) Z_LVAL_P(val);
        } else if (Z_TYPE_P(val) == IS_DOUBLE) {
            (*out)[i++] = Z_DVAL_P(val);
        } else {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "vector element must be a number", 0);
            return FAILURE;
        }
    } ZEND_HASH_FOREACH_END();
    *n = i;
    return SUCCESS;
}

static zend_result simsimd_read_f32(HashTable *ht, simsimd_f32_t **out, size_t *n)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    *out = emalloc(count * sizeof(simsimd_f32_t));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        if (Z_TYPE_P(val) == IS_LONG) {
            (*out)[i++] = (simsimd_f32_t) Z_LVAL_P(val);
        } else if (Z_TYPE_P(val) == IS_DOUBLE) {
            (*out)[i++] = (simsimd_f32_t) Z_DVAL_P(val);
        } else {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "vector element must be a number", 0);
            return FAILURE;
        }
    } ZEND_HASH_FOREACH_END();
    *n = i;
    return SUCCESS;
}

static zend_result simsimd_read_i8(HashTable *ht, simsimd_i8_t **out, size_t *n)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    *out = emalloc(count * sizeof(simsimd_i8_t));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        zend_long number;

        if (Z_TYPE_P(val) != IS_LONG) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "i8 vector element must be an int", 0);
            return FAILURE;
        }
        number = Z_LVAL_P(val);
        if (number < -128 || number > 127) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "i8 vector element must be between -128 and 127", 0);
            return FAILURE;
        }
        (*out)[i++] = (simsimd_i8_t) number;
    } ZEND_HASH_FOREACH_END();
    *n = i;
    return SUCCESS;
}

static zend_result simsimd_own(void *data, size_t n, simsimd_buf *out)
{
    if (n == 0) {
        if (data != NULL) {
            efree(data);
        }
        simsimd_buf_clear(out);
        return SUCCESS;
    }
    out->data = data;
    out->n = n;
    out->owned = 1;
    return SUCCESS;
}

static zend_result simsimd_packed(zend_string *str, size_t width, simsimd_buf *out)
{
    if (ZSTR_LEN(str) % width != 0) {
        zend_throw_exception_ex(
            simsimd_value_exception_ce,
            0,
            "packed string length must be a multiple of %zu",
            width
        );
        return FAILURE;
    }
    out->n = ZSTR_LEN(str) / width;
    if (out->n == 0) {
        simsimd_buf_clear(out);
        return SUCCESS;
    }
    out->data = ZSTR_VAL(str);
    out->owned = 0;
    return SUCCESS;
}

static zend_result simsimd_read_number(zval *val, double *out)
{
    if (Z_TYPE_P(val) == IS_LONG) {
        *out = (double) Z_LVAL_P(val);
        return SUCCESS;
    }
    if (Z_TYPE_P(val) == IS_DOUBLE) {
        *out = Z_DVAL_P(val);
        return SUCCESS;
    }
    zend_throw_exception(simsimd_value_exception_ce, "vector element must be a number", 0);
    return FAILURE;
}

static zend_result simsimd_read_half(HashTable *ht, simsimd_buf *out, zend_bool f16)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);
    unsigned short *data;

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    if (count == 0) {
        simsimd_buf_clear(out);
        return SUCCESS;
    }
    data = emalloc(count * sizeof(unsigned short));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        double number;

        if (simsimd_read_number(val, &number) == FAILURE) {
            efree(data);
            return FAILURE;
        }
        if (f16) {
            simsimd_f32_to_f16((simsimd_f32_t) number, (simsimd_f16_t *) &data[i]);
        } else {
            simsimd_f32_to_bf16((simsimd_f32_t) number, (simsimd_bf16_t *) &data[i]);
        }
        i++;
    } ZEND_HASH_FOREACH_END();
    out->data = data;
    out->n = i;
    out->owned = 1;
    return SUCCESS;
}

static zend_result simsimd_read_u8(HashTable *ht, simsimd_u8_t **out, size_t *n)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    *out = emalloc(count * sizeof(simsimd_u8_t));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        zend_long number;

        if (Z_TYPE_P(val) != IS_LONG) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "u8 vector element must be an int", 0);
            return FAILURE;
        }
        number = Z_LVAL_P(val);
        if (number < 0 || number > 255) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "u8 vector element must be between 0 and 255", 0);
            return FAILURE;
        }
        (*out)[i++] = (simsimd_u8_t) number;
    } ZEND_HASH_FOREACH_END();
    *n = i;
    return SUCCESS;
}

static zend_result simsimd_load_real(zval *zv, simsimd_php_kind kind, simsimd_buf *out)
{
    HashTable *ht;

    simsimd_buf_clear(out);
    if (Z_TYPE_P(zv) == IS_STRING) {
        if (kind != SIMSIMD_KIND_F16 && kind != SIMSIMD_KIND_BF16) {
            zend_throw_exception(simsimd_value_exception_ce, "packed strings are only for f16 and bf16", 0);
            return FAILURE;
        }
        return simsimd_packed(Z_STR_P(zv), sizeof(unsigned short), out);
    }
    if (Z_TYPE_P(zv) != IS_ARRAY) {
        zend_throw_exception(zend_ce_type_error, "vector must be an array or a string", 0);
        return FAILURE;
    }
    ht = Z_ARRVAL_P(zv);
    switch (kind) {
        case SIMSIMD_KIND_F64: {
            simsimd_f64_t *data = NULL;
            size_t n = 0;
            if (simsimd_read_f64(ht, &data, &n) == FAILURE) {
                return FAILURE;
            }
            return simsimd_own(data, n, out);
        }
        case SIMSIMD_KIND_F32: {
            simsimd_f32_t *data = NULL;
            size_t n = 0;
            if (simsimd_read_f32(ht, &data, &n) == FAILURE) {
                return FAILURE;
            }
            return simsimd_own(data, n, out);
        }
        case SIMSIMD_KIND_F16:
            return simsimd_read_half(ht, out, 1);
        case SIMSIMD_KIND_BF16:
            return simsimd_read_half(ht, out, 0);
        case SIMSIMD_KIND_U8: {
            simsimd_u8_t *data = NULL;
            size_t n = 0;
            if (simsimd_read_u8(ht, &data, &n) == FAILURE) {
                return FAILURE;
            }
            return simsimd_own(data, n, out);
        }
        default: {
            simsimd_i8_t *data = NULL;
            size_t n = 0;
            if (simsimd_read_i8(ht, &data, &n) == FAILURE) {
                return FAILURE;
            }
            return simsimd_own(data, n, out);
        }
    }
}

static zend_result simsimd_read_pair(zval *val, double *real, double *imag)
{
    HashTable *ht;
    zval *part;

    if (Z_TYPE_P(val) != IS_ARRAY) {
        zend_throw_exception(simsimd_value_exception_ce, "complex element must be a pair of numbers", 0);
        return FAILURE;
    }
    ht = Z_ARRVAL_P(val);
    if (zend_hash_num_elements(ht) != 2 || !zend_array_is_list(ht)) {
        zend_throw_exception(simsimd_value_exception_ce, "complex element must be a pair of numbers", 0);
        return FAILURE;
    }
    part = zend_hash_index_find(ht, 0);
    if (part == NULL || simsimd_read_number(part, real) == FAILURE) {
        if (part == NULL && !EG(exception)) {
            zend_throw_exception(simsimd_value_exception_ce, "complex element must be a pair of numbers", 0);
        }
        return FAILURE;
    }
    part = zend_hash_index_find(ht, 1);
    if (part == NULL || simsimd_read_number(part, imag) == FAILURE) {
        if (part == NULL && !EG(exception)) {
            zend_throw_exception(simsimd_value_exception_ce, "complex element must be a pair of numbers", 0);
        }
        return FAILURE;
    }
    return SUCCESS;
}

static void simsimd_store_complex(simsimd_php_kind kind, void *data, size_t i, double real, double imag)
{
    switch (kind) {
        case SIMSIMD_KIND_F64:
            ((simsimd_f64c_t *) data)[i].real = real;
            ((simsimd_f64c_t *) data)[i].imag = imag;
            break;
        case SIMSIMD_KIND_F32:
            ((simsimd_f32c_t *) data)[i].real = (simsimd_f32_t) real;
            ((simsimd_f32c_t *) data)[i].imag = (simsimd_f32_t) imag;
            break;
        case SIMSIMD_KIND_F16:
            simsimd_f32_to_f16((simsimd_f32_t) real, &((simsimd_f16c_t *) data)[i].real);
            simsimd_f32_to_f16((simsimd_f32_t) imag, &((simsimd_f16c_t *) data)[i].imag);
            break;
        case SIMSIMD_KIND_BF16:
            simsimd_f32_to_bf16((simsimd_f32_t) real, &((simsimd_bf16c_t *) data)[i].real);
            simsimd_f32_to_bf16((simsimd_f32_t) imag, &((simsimd_bf16c_t *) data)[i].imag);
            break;
        default:
            break;
    }
}

static size_t simsimd_complex_width(simsimd_php_kind kind)
{
    switch (kind) {
        case SIMSIMD_KIND_F32:
            return sizeof(simsimd_f32c_t);
        case SIMSIMD_KIND_F16:
            return sizeof(simsimd_f16c_t);
        case SIMSIMD_KIND_BF16:
            return sizeof(simsimd_bf16c_t);
        default:
            return sizeof(simsimd_f64c_t);
    }
}

static zend_result simsimd_load_complex(zval *zv, simsimd_php_kind kind, simsimd_buf *out)
{
    HashTable *ht;
    zval *val;
    size_t i = 0;
    uint32_t count;
    void *data;

    simsimd_buf_clear(out);
    if (kind == SIMSIMD_KIND_I8 || kind == SIMSIMD_KIND_U8) {
        zend_throw_exception(
            simsimd_value_exception_ce,
            kind == SIMSIMD_KIND_I8 ? "i8 is not a complex type" : "u8 is not a complex type",
            0
        );
        return FAILURE;
    }
    if (Z_TYPE_P(zv) == IS_STRING) {
        if (kind != SIMSIMD_KIND_F16 && kind != SIMSIMD_KIND_BF16) {
            zend_throw_exception(simsimd_value_exception_ce, "packed strings are only for f16 and bf16", 0);
            return FAILURE;
        }
        return simsimd_packed(Z_STR_P(zv), sizeof(simsimd_f16c_t), out);
    }
    if (Z_TYPE_P(zv) != IS_ARRAY) {
        zend_throw_exception(zend_ce_type_error, "vector must be an array or a string", 0);
        return FAILURE;
    }
    ht = Z_ARRVAL_P(zv);
    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    count = zend_hash_num_elements(ht);
    if (count == 0) {
        simsimd_buf_clear(out);
        return SUCCESS;
    }
    data = emalloc(count * simsimd_complex_width(kind));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        double real;
        double imag;

        if (simsimd_read_pair(val, &real, &imag) == FAILURE) {
            efree(data);
            return FAILURE;
        }
        simsimd_store_complex(kind, data, i, real, imag);
        i++;
    } ZEND_HASH_FOREACH_END();
    out->data = data;
    out->n = i;
    out->owned = 1;
    return SUCCESS;
}

static zend_result simsimd_load_unsigned(zval *zv, int bits, simsimd_buf *out)
{
    HashTable *ht;
    zval *val;
    size_t i = 0;
    uint32_t count;
    uint64_t previous = 0;
    zend_bool have_previous = 0;
    void *data;
    size_t width = bits == 16 ? sizeof(simsimd_u16_t) : sizeof(simsimd_u32_t);
    uint64_t limit = bits == 16 ? 65535ULL : 4294967295ULL;

    simsimd_buf_clear(out);
    if (Z_TYPE_P(zv) != IS_ARRAY) {
        zend_throw_exception(zend_ce_type_error, "vector must be an array", 0);
        return FAILURE;
    }
    ht = Z_ARRVAL_P(zv);
    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    count = zend_hash_num_elements(ht);
    if (count == 0) {
        simsimd_buf_clear(out);
        return SUCCESS;
    }
    data = emalloc(count * width);
    ZEND_HASH_FOREACH_VAL(ht, val) {
        zend_long number;

        if (Z_TYPE_P(val) != IS_LONG) {
            efree(data);
            zend_throw_exception(simsimd_value_exception_ce, "intersect element must be an int", 0);
            return FAILURE;
        }
        number = Z_LVAL_P(val);
        if (number < 0 || (uint64_t) number > limit) {
            efree(data);
            zend_throw_exception(
                simsimd_value_exception_ce,
                bits == 16
                    ? "u16 element must be an int between 0 and 65535"
                    : "u32 element must be an int between 0 and 4294967295",
                0
            );
            return FAILURE;
        }
        if (have_previous && (uint64_t) number < previous) {
            efree(data);
            zend_throw_exception(simsimd_value_exception_ce, "intersect inputs must be sorted in non-decreasing order", 0);
            return FAILURE;
        }
        if (bits == 16) {
            ((simsimd_u16_t *) data)[i] = (simsimd_u16_t) number;
        } else {
            ((simsimd_u32_t *) data)[i] = (simsimd_u32_t) number;
        }
        previous = (uint64_t) number;
        have_previous = 1;
        i++;
    } ZEND_HASH_FOREACH_END();
    out->data = data;
    out->n = i;
    out->owned = 1;
    return SUCCESS;
}

static zend_result simsimd_accept_square(simsimd_buf *left, simsimd_buf *right, simsimd_buf *matrix)
{
    size_t n = left->n;

    if (left->n != right->n) {
        simsimd_throw_length(left->n, right->n);
        return FAILURE;
    }
    if ((n == 0 && matrix->n != 0) || (n != 0 && (matrix->n / n != n || matrix->n % n != 0))) {
        zend_throw_exception_ex(
            simsimd_length_exception_ce,
            0,
            "matrix must be n by n, length %zu for n %zu",
            matrix->n,
            n
        );
        return FAILURE;
    }
    return SUCCESS;
}

static void simsimd_dense(
    INTERNAL_FUNCTION_PARAMETERS,
    zend_bool allow_i8,
    zend_bool allow_u8,
    void (*fn64)(simsimd_f64_t const *, simsimd_f64_t const *, simsimd_size_t, simsimd_distance_t *),
    void (*fn32)(simsimd_f32_t const *, simsimd_f32_t const *, simsimd_size_t, simsimd_distance_t *),
    void (*fn16)(simsimd_f16_t const *, simsimd_f16_t const *, simsimd_size_t, simsimd_distance_t *),
    void (*fnbf)(simsimd_bf16_t const *, simsimd_bf16_t const *, simsimd_size_t, simsimd_distance_t *),
    void (*fn8)(simsimd_i8_t const *, simsimd_i8_t const *, simsimd_size_t, simsimd_distance_t *),
    void (*fnu8)(simsimd_u8_t const *, simsimd_u8_t const *, simsimd_size_t, simsimd_distance_t *)
) {
    zval *left_zv;
    zval *right_zv;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf left;
    simsimd_buf right;
    simsimd_distance_t distance = 0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_ZVAL(left_zv)
        Z_PARAM_ZVAL(right_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (parsed == SIMSIMD_KIND_I8 && !allow_i8) {
        zend_throw_exception(simsimd_value_exception_ce, "i8 is not a probability type", 0);
        return;
    }
    if (parsed == SIMSIMD_KIND_U8 && !allow_u8) {
        zend_throw_exception(simsimd_value_exception_ce, "u8 is not a probability type", 0);
        return;
    }
    if (simsimd_load_real(left_zv, parsed, &left) == FAILURE) {
        return;
    }
    if (simsimd_load_real(right_zv, parsed, &right) == FAILURE) {
        simsimd_buf_release(&left);
        return;
    }
    if (left.n != right.n) {
        size_t left_n = left.n;
        size_t right_n = right.n;
        simsimd_buf_release(&left);
        simsimd_buf_release(&right);
        simsimd_throw_length(left_n, right_n);
        return;
    }

    switch (parsed) {
        case SIMSIMD_KIND_F64:
            fn64(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
        case SIMSIMD_KIND_F32:
            fn32(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
        case SIMSIMD_KIND_F16:
            fn16(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
        case SIMSIMD_KIND_BF16:
            fnbf(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
        case SIMSIMD_KIND_U8:
            fnu8(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
        default:
            fn8(left.data, right.data, (simsimd_size_t) left.n, &distance);
            break;
    }
    simsimd_buf_release(&left);
    simsimd_buf_release(&right);
    RETURN_DOUBLE(distance);
}

static void simsimd_binary(
    INTERNAL_FUNCTION_PARAMETERS,
    void (*fn)(simsimd_b8_t const *, simsimd_b8_t const *, simsimd_size_t, simsimd_distance_t *)
) {
    zend_string *left;
    zend_string *right;
    simsimd_distance_t distance = 0;

    ZEND_PARSE_PARAMETERS_START(2, 2)
        Z_PARAM_STR(left)
        Z_PARAM_STR(right)
    ZEND_PARSE_PARAMETERS_END();

    if (ZSTR_LEN(left) != ZSTR_LEN(right)) {
        simsimd_throw_length(ZSTR_LEN(left), ZSTR_LEN(right));
        return;
    }

    fn(
        (simsimd_b8_t const *) ZSTR_VAL(left),
        (simsimd_b8_t const *) ZSTR_VAL(right),
        (simsimd_size_t) ZSTR_LEN(left),
        &distance
    );
    RETURN_DOUBLE(distance);
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_simsimd_construct, 0, 0, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_dense, 0, 2, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_complex, 0, 2, IS_ARRAY, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_intersect, 0, 2, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_INFO(0, a, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO(0, b, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"u32\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_curved, 0, 3, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, c, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_complex_curved, 0, 3, IS_ARRAY, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, c, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_binary, 0, 2, IS_DOUBLE, 0)
    ZEND_ARG_TYPE_INFO(0, a, IS_STRING, 0)
    ZEND_ARG_TYPE_INFO(0, b, IS_STRING, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_wsum, 0, 2, IS_ARRAY, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, alpha, IS_DOUBLE, 0, "1.0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, beta, IS_DOUBLE, 0, "1.0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_fma, 0, 3, IS_ARRAY, 0)
    ZEND_ARG_TYPE_MASK(0, a, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, b, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_MASK(0, c, MAY_BE_ARRAY | MAY_BE_STRING, NULL)
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, alpha, IS_DOUBLE, 0, "1.0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, beta, IS_DOUBLE, 0, "1.0")
    ZEND_ARG_TYPE_INFO_WITH_DEFAULT_VALUE(0, kind, IS_STRING, 0, "\"f64\"")
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_sparse_dot, 0, 4, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO(0, a, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO(0, b, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO(0, aWeights, IS_ARRAY, 0)
    ZEND_ARG_TYPE_INFO(0, bWeights, IS_ARRAY, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_capabilities, 0, 0, IS_ARRAY, 0)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_WITH_RETURN_TYPE_INFO_EX(arginfo_simsimd_flush, 0, 0, _IS_BOOL, 0)
ZEND_END_ARG_INFO()

PHP_METHOD(SimsimdVector, __construct)
{
    ZEND_PARSE_PARAMETERS_NONE();
}

PHP_METHOD(SimsimdVector, cosine)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        1,
        1,
        simsimd_cos_f64,
        simsimd_cos_f32,
        simsimd_cos_f16,
        simsimd_cos_bf16,
        simsimd_cos_i8,
        simsimd_cos_u8
    );
}

PHP_METHOD(SimsimdVector, dot)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        1,
        1,
        simsimd_dot_f64,
        simsimd_dot_f32,
        simsimd_dot_f16,
        simsimd_dot_bf16,
        simsimd_dot_i8,
        simsimd_dot_u8
    );
}

PHP_METHOD(SimsimdVector, sqeuclidean)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        1,
        1,
        simsimd_l2sq_f64,
        simsimd_l2sq_f32,
        simsimd_l2sq_f16,
        simsimd_l2sq_bf16,
        simsimd_l2sq_i8,
        simsimd_l2sq_u8
    );
}

PHP_METHOD(SimsimdVector, euclidean)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        1,
        1,
        simsimd_l2_f64,
        simsimd_l2_f32,
        simsimd_l2_f16,
        simsimd_l2_bf16,
        simsimd_l2_i8,
        simsimd_l2_u8
    );
}

PHP_METHOD(SimsimdVector, jensenShannon)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        0,
        0,
        simsimd_js_f64,
        simsimd_js_f32,
        simsimd_js_f16,
        simsimd_js_bf16,
        NULL,
        NULL
    );
}

PHP_METHOD(SimsimdVector, kullbackLeibler)
{
    simsimd_dense(
        INTERNAL_FUNCTION_PARAM_PASSTHRU,
        0,
        0,
        simsimd_kl_f64,
        simsimd_kl_f32,
        simsimd_kl_f16,
        simsimd_kl_bf16,
        NULL,
        NULL
    );
}

PHP_METHOD(SimsimdVector, hamming)
{
    simsimd_binary(INTERNAL_FUNCTION_PARAM_PASSTHRU, simsimd_hamming_b8);
}

PHP_METHOD(SimsimdVector, jaccard)
{
    simsimd_binary(INTERNAL_FUNCTION_PARAM_PASSTHRU, simsimd_jaccard_b8);
}

static void simsimd_return_pair(zval *return_value, simsimd_distance_t real, simsimd_distance_t imag)
{
    array_init(return_value);
    add_next_index_double(return_value, real);
    add_next_index_double(return_value, imag);
}

static void simsimd_complex_product(simsimd_php_kind kind, zend_bool conjugate, simsimd_buf *left, simsimd_buf *right, simsimd_distance_t *results)
{
    simsimd_size_t n = (simsimd_size_t) left->n;

    switch (kind) {
        case SIMSIMD_KIND_F32:
            if (conjugate) {
                simsimd_vdot_f32c(left->data, right->data, n, results);
            } else {
                simsimd_dot_f32c(left->data, right->data, n, results);
            }
            break;
        case SIMSIMD_KIND_F16:
            if (conjugate) {
                /* 6.5.16 Haswell tail calls simsimd_dot_f16c_serial, so short vectors are not conjugated. */
                simsimd_vdot_f16c_serial(left->data, right->data, n, results);
            } else {
                simsimd_dot_f16c(left->data, right->data, n, results);
            }
            break;
        case SIMSIMD_KIND_BF16:
            if (conjugate) {
                /* 6.5.16 NEON tail calls simsimd_dot_bf16c_serial for the leftover pairs. */
                simsimd_vdot_bf16c_serial(left->data, right->data, n, results);
            } else {
                simsimd_dot_bf16c(left->data, right->data, n, results);
            }
            break;
        default:
            if (conjugate) {
                simsimd_vdot_f64c(left->data, right->data, n, results);
            } else {
                simsimd_dot_f64c(left->data, right->data, n, results);
            }
            break;
    }
}

static void simsimd_complex(INTERNAL_FUNCTION_PARAMETERS, zend_bool conjugate)
{
    zval *left_zv;
    zval *right_zv;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf left;
    simsimd_buf right;
    simsimd_distance_t results[2] = {0, 0};

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_ZVAL(left_zv)
        Z_PARAM_ZVAL(right_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (simsimd_load_complex(left_zv, parsed, &left) == FAILURE) {
        return;
    }
    if (simsimd_load_complex(right_zv, parsed, &right) == FAILURE) {
        simsimd_buf_release(&left);
        return;
    }
    if (left.n != right.n) {
        size_t left_n = left.n;
        size_t right_n = right.n;
        simsimd_buf_release(&left);
        simsimd_buf_release(&right);
        simsimd_throw_length(left_n, right_n);
        return;
    }
    simsimd_complex_product(parsed, conjugate, &left, &right, results);
    simsimd_buf_release(&left);
    simsimd_buf_release(&right);
    simsimd_return_pair(return_value, results[0], results[1]);
}

static zend_result simsimd_parse_unsigned_kind(const char *kind, size_t kind_len, int *bits)
{
    if (kind_len == 3 && memcmp(kind, "u16", 3) == 0) {
        *bits = 16;
        return SUCCESS;
    }
    if (kind_len == 3 && memcmp(kind, "u32", 3) == 0) {
        *bits = 32;
        return SUCCESS;
    }
    zend_throw_exception(simsimd_value_exception_ce, "kind must be u16 or u32", 0);
    return FAILURE;
}

static zend_result simsimd_load_curve(zval *az, zval *bz, zval *cz, simsimd_php_kind kind, zend_bool complex, simsimd_buf *a, simsimd_buf *b, simsimd_buf *c)
{
    zend_result (*load)(zval *, simsimd_php_kind, simsimd_buf *) = complex ? simsimd_load_complex : simsimd_load_real;

    if (load(az, kind, a) == FAILURE) {
        return FAILURE;
    }
    if (load(bz, kind, b) == FAILURE) {
        simsimd_buf_release(a);
        return FAILURE;
    }
    if (load(cz, kind, c) == FAILURE) {
        simsimd_buf_release(a);
        simsimd_buf_release(b);
        return FAILURE;
    }
    if (simsimd_accept_square(a, b, c) == FAILURE) {
        simsimd_buf_release(a);
        simsimd_buf_release(b);
        simsimd_buf_release(c);
        return FAILURE;
    }
    return SUCCESS;
}

static void simsimd_real_curve(simsimd_php_kind kind, zend_bool mahalanobis, simsimd_buf *a, simsimd_buf *b, simsimd_buf *c, simsimd_distance_t *result)
{
    simsimd_size_t n = (simsimd_size_t) a->n;

    switch (kind) {
        case SIMSIMD_KIND_F32:
            if (mahalanobis) {
                simsimd_mahalanobis_f32(a->data, b->data, c->data, n, result);
            } else {
                simsimd_bilinear_f32(a->data, b->data, c->data, n, result);
            }
            break;
        case SIMSIMD_KIND_F16:
            if (mahalanobis) {
                simsimd_mahalanobis_f16(a->data, b->data, c->data, n, result);
            } else {
                simsimd_bilinear_f16(a->data, b->data, c->data, n, result);
            }
            break;
        case SIMSIMD_KIND_BF16:
            if (mahalanobis) {
                simsimd_mahalanobis_bf16(a->data, b->data, c->data, n, result);
            } else {
                simsimd_bilinear_bf16(a->data, b->data, c->data, n, result);
            }
            break;
        default:
            if (mahalanobis) {
                simsimd_mahalanobis_f64(a->data, b->data, c->data, n, result);
            } else {
                simsimd_bilinear_f64(a->data, b->data, c->data, n, result);
            }
            break;
    }
}

static void simsimd_complex_curve(simsimd_php_kind kind, simsimd_buf *a, simsimd_buf *b, simsimd_buf *c, simsimd_distance_t *results)
{
    simsimd_size_t n = (simsimd_size_t) a->n;

    switch (kind) {
        case SIMSIMD_KIND_F32:
            simsimd_bilinear_f32c(a->data, b->data, c->data, n, results);
            break;
        case SIMSIMD_KIND_F16:
            simsimd_bilinear_f16c(a->data, b->data, c->data, n, results);
            break;
        case SIMSIMD_KIND_BF16:
            /* 6.5.16 Genoa kernel returns garbage when n is not a multiple of 16. */
            simsimd_bilinear_bf16c_serial(a->data, b->data, c->data, n, results);
            break;
        default:
            simsimd_bilinear_f64c(a->data, b->data, c->data, n, results);
            break;
    }
}

PHP_METHOD(SimsimdVector, complexDot)
{
    simsimd_complex(INTERNAL_FUNCTION_PARAM_PASSTHRU, 0);
}

PHP_METHOD(SimsimdVector, complexConjugateDot)
{
    simsimd_complex(INTERNAL_FUNCTION_PARAM_PASSTHRU, 1);
}

PHP_METHOD(SimsimdVector, intersect)
{
    zval *left_zv;
    zval *right_zv;
    char *kind = "u32";
    size_t kind_len = 3;
    int bits = 32;
    simsimd_buf left;
    simsimd_buf right;
    simsimd_distance_t count = 0;

    ZEND_PARSE_PARAMETERS_START(2, 3)
        Z_PARAM_ZVAL(left_zv)
        Z_PARAM_ZVAL(right_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_unsigned_kind(kind, kind_len, &bits) == FAILURE) {
        return;
    }
    if (simsimd_load_unsigned(left_zv, bits, &left) == FAILURE) {
        return;
    }
    if (simsimd_load_unsigned(right_zv, bits, &right) == FAILURE) {
        simsimd_buf_release(&left);
        return;
    }
    if (bits == 16) {
        simsimd_intersect_u16(left.data, right.data, (simsimd_size_t) left.n, (simsimd_size_t) right.n, &count);
    } else {
        simsimd_intersect_u32(left.data, right.data, (simsimd_size_t) left.n, (simsimd_size_t) right.n, &count);
    }
    simsimd_buf_release(&left);
    simsimd_buf_release(&right);
    RETURN_DOUBLE(count);
}

PHP_METHOD(SimsimdVector, bilinear)
{
    zval *a_zv;
    zval *b_zv;
    zval *c_zv;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf c;
    simsimd_distance_t distance = 0;

    ZEND_PARSE_PARAMETERS_START(3, 4)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(c_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (parsed == SIMSIMD_KIND_I8 || parsed == SIMSIMD_KIND_U8) {
        zend_throw_exception(
            simsimd_value_exception_ce,
            parsed == SIMSIMD_KIND_I8 ? "i8 is not a curved type" : "u8 is not a curved type",
            0
        );
        return;
    }
    if (simsimd_load_curve(a_zv, b_zv, c_zv, parsed, 0, &a, &b, &c) == FAILURE) {
        return;
    }
    simsimd_real_curve(parsed, 0, &a, &b, &c, &distance);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&c);
    RETURN_DOUBLE(distance);
}

PHP_METHOD(SimsimdVector, complexBilinear)
{
    zval *a_zv;
    zval *b_zv;
    zval *c_zv;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf c;
    simsimd_distance_t results[2] = {0, 0};

    ZEND_PARSE_PARAMETERS_START(3, 4)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(c_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (simsimd_load_curve(a_zv, b_zv, c_zv, parsed, 1, &a, &b, &c) == FAILURE) {
        return;
    }
    simsimd_complex_curve(parsed, &a, &b, &c, results);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&c);
    simsimd_return_pair(return_value, results[0], results[1]);
}

PHP_METHOD(SimsimdVector, mahalanobis)
{
    zval *a_zv;
    zval *b_zv;
    zval *c_zv;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf c;
    simsimd_distance_t distance = 0;

    ZEND_PARSE_PARAMETERS_START(3, 4)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(c_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (parsed == SIMSIMD_KIND_I8 || parsed == SIMSIMD_KIND_U8) {
        zend_throw_exception(
            simsimd_value_exception_ce,
            parsed == SIMSIMD_KIND_I8 ? "i8 is not a curved type" : "u8 is not a curved type",
            0
        );
        return;
    }
    if (simsimd_load_curve(a_zv, b_zv, c_zv, parsed, 0, &a, &b, &c) == FAILURE) {
        return;
    }
    simsimd_real_curve(parsed, 1, &a, &b, &c, &distance);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&c);
    RETURN_DOUBLE(distance);
}

static size_t simsimd_real_width(simsimd_php_kind kind)
{
    switch (kind) {
        case SIMSIMD_KIND_F64:
            return sizeof(simsimd_f64_t);
        case SIMSIMD_KIND_F32:
            return sizeof(simsimd_f32_t);
        case SIMSIMD_KIND_F16:
            return sizeof(simsimd_f16_t);
        case SIMSIMD_KIND_BF16:
            return sizeof(simsimd_bf16_t);
        case SIMSIMD_KIND_I8:
            return sizeof(simsimd_i8_t);
        default:
            return sizeof(simsimd_u8_t);
    }
}

static void simsimd_return_vector(zval *return_value, simsimd_php_kind kind, void const *data, size_t n)
{
    size_t i;

    array_init_size(return_value, n);
    for (i = 0; i < n; i++) {
        switch (kind) {
            case SIMSIMD_KIND_F64:
                add_next_index_double(return_value, ((simsimd_f64_t const *) data)[i]);
                break;
            case SIMSIMD_KIND_F32:
                add_next_index_double(return_value, ((simsimd_f32_t const *) data)[i]);
                break;
            case SIMSIMD_KIND_F16:
                add_next_index_double(return_value, simsimd_f16_to_f32(&((simsimd_f16_t const *) data)[i]));
                break;
            case SIMSIMD_KIND_BF16:
                add_next_index_double(return_value, simsimd_bf16_to_f32(&((simsimd_bf16_t const *) data)[i]));
                break;
            case SIMSIMD_KIND_I8:
                add_next_index_long(return_value, ((simsimd_i8_t const *) data)[i]);
                break;
            default:
                add_next_index_long(return_value, ((simsimd_u8_t const *) data)[i]);
                break;
        }
    }
}

static void simsimd_call_wsum(simsimd_php_kind kind, void const *a, void const *b, size_t n, double alpha, double beta, void *result)
{
    simsimd_size_t count = (simsimd_size_t) n;

    switch (kind) {
        case SIMSIMD_KIND_F32:
            simsimd_wsum_f32(a, b, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_F16:
            simsimd_wsum_f16(a, b, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_BF16:
            simsimd_wsum_bf16(a, b, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_I8:
            simsimd_wsum_i8(a, b, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_U8:
            simsimd_wsum_u8(a, b, count, alpha, beta, result);
            break;
        default:
            simsimd_wsum_f64(a, b, count, alpha, beta, result);
            break;
    }
}

static void simsimd_call_fma(
    simsimd_php_kind kind,
    void const *a,
    void const *b,
    void const *c,
    size_t n,
    double alpha,
    double beta,
    void *result
) {
    simsimd_size_t count = (simsimd_size_t) n;

    switch (kind) {
        case SIMSIMD_KIND_F32:
            simsimd_fma_f32(a, b, c, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_F16:
            simsimd_fma_f16(a, b, c, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_BF16:
            simsimd_fma_bf16(a, b, c, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_I8:
            simsimd_fma_i8(a, b, c, count, alpha, beta, result);
            break;
        case SIMSIMD_KIND_U8:
            simsimd_fma_u8(a, b, c, count, alpha, beta, result);
            break;
        default:
            simsimd_fma_f64(a, b, c, count, alpha, beta, result);
            break;
    }
}

static zend_result simsimd_load_equal(zval *az, zval *bz, zval *cz, simsimd_php_kind kind, simsimd_buf *a, simsimd_buf *b, simsimd_buf *c)
{
    if (simsimd_load_real(az, kind, a) == FAILURE) {
        return FAILURE;
    }
    if (simsimd_load_real(bz, kind, b) == FAILURE) {
        simsimd_buf_release(a);
        return FAILURE;
    }
    if (a->n != b->n) {
        size_t left_n = a->n;
        size_t right_n = b->n;
        simsimd_buf_release(a);
        simsimd_buf_release(b);
        simsimd_throw_length(left_n, right_n);
        return FAILURE;
    }
    if (cz == NULL) {
        return SUCCESS;
    }
    if (simsimd_load_real(cz, kind, c) == FAILURE) {
        simsimd_buf_release(a);
        simsimd_buf_release(b);
        return FAILURE;
    }
    if (a->n != c->n) {
        size_t left_n = a->n;
        size_t right_n = c->n;
        simsimd_buf_release(a);
        simsimd_buf_release(b);
        simsimd_buf_release(c);
        simsimd_throw_length(left_n, right_n);
        return FAILURE;
    }
    return SUCCESS;
}

PHP_METHOD(SimsimdVector, wsum)
{
    zval *a_zv;
    zval *b_zv;
    double alpha = 1.0;
    double beta = 1.0;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf a;
    simsimd_buf b;
    void *result;

    ZEND_PARSE_PARAMETERS_START(2, 5)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_DOUBLE(alpha)
        Z_PARAM_DOUBLE(beta)
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (simsimd_load_equal(a_zv, b_zv, NULL, parsed, &a, &b, NULL) == FAILURE) {
        return;
    }
    result = ecalloc(a.n > 0 ? a.n : 1, simsimd_real_width(parsed));
    simsimd_call_wsum(parsed, a.data, b.data, a.n, alpha, beta, result);
    simsimd_return_vector(return_value, parsed, result, a.n);
    efree(result);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
}

PHP_METHOD(SimsimdVector, fma)
{
    zval *a_zv;
    zval *b_zv;
    zval *c_zv;
    double alpha = 1.0;
    double beta = 1.0;
    char *kind = "f64";
    size_t kind_len = 3;
    simsimd_php_kind parsed;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf c;
    void *result;

    ZEND_PARSE_PARAMETERS_START(3, 6)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(c_zv)
        Z_PARAM_OPTIONAL
        Z_PARAM_DOUBLE(alpha)
        Z_PARAM_DOUBLE(beta)
        Z_PARAM_STRING(kind, kind_len)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_parse_kind(kind, kind_len, &parsed) == FAILURE) {
        return;
    }
    if (simsimd_load_equal(a_zv, b_zv, c_zv, parsed, &a, &b, &c) == FAILURE) {
        return;
    }
    result = ecalloc(a.n > 0 ? a.n : 1, simsimd_real_width(parsed));
    simsimd_call_fma(parsed, a.data, b.data, c.data, a.n, alpha, beta, result);
    simsimd_return_vector(return_value, parsed, result, a.n);
    efree(result);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&c);
}

static zend_result simsimd_read_i16(HashTable *ht, simsimd_i16_t **out, size_t *n)
{
    zval *val;
    size_t i = 0;
    uint32_t count = zend_hash_num_elements(ht);

    if (simsimd_require_list(ht) == FAILURE) {
        return FAILURE;
    }
    *out = emalloc(count * sizeof(simsimd_i16_t));
    ZEND_HASH_FOREACH_VAL(ht, val) {
        zend_long number;

        if (Z_TYPE_P(val) != IS_LONG) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "i16 weight must be an int", 0);
            return FAILURE;
        }
        number = Z_LVAL_P(val);
        if (number < -32768 || number > 32767) {
            efree(*out);
            *out = NULL;
            zend_throw_exception(simsimd_value_exception_ce, "i16 weight must be between -32768 and 32767", 0);
            return FAILURE;
        }
        (*out)[i++] = (simsimd_i16_t) number;
    } ZEND_HASH_FOREACH_END();
    *n = i;
    return SUCCESS;
}

static zend_result simsimd_load_i16(zval *zv, simsimd_buf *out)
{
    simsimd_i16_t *data = NULL;
    size_t n = 0;

    simsimd_buf_clear(out);
    if (Z_TYPE_P(zv) != IS_ARRAY) {
        zend_throw_exception(zend_ce_type_error, "vector must be an array", 0);
        return FAILURE;
    }
    if (simsimd_read_i16(Z_ARRVAL_P(zv), &data, &n) == FAILURE) {
        return FAILURE;
    }
    return simsimd_own(data, n, out);
}

static void simsimd_pad_second_weights(simsimd_buf *weights, size_t id_count, size_t width)
{
    void *grown;

    if (id_count <= weights->n) {
        return;
    }
    grown = ecalloc(id_count, width);
    if (weights->n > 0) {
        memcpy(grown, weights->data, weights->n * width);
    }
    if (weights->owned) {
        efree(weights->data);
    }
    weights->data = grown;
    weights->owned = 1;
}

typedef void (*simsimd_spdot_counts_fn)(
    simsimd_u16_t const *,
    simsimd_u16_t const *,
    simsimd_i16_t const *,
    simsimd_i16_t const *,
    simsimd_size_t,
    simsimd_size_t,
    simsimd_distance_t *
);
typedef void (*simsimd_spdot_weights_fn)(
    simsimd_u16_t const *,
    simsimd_u16_t const *,
    simsimd_bf16_t const *,
    simsimd_bf16_t const *,
    simsimd_size_t,
    simsimd_size_t,
    simsimd_distance_t *
);

static void simsimd_call_spdot_counts(
    simsimd_u16_t const *a,
    simsimd_u16_t const *b,
    simsimd_i16_t const *a_weights,
    simsimd_i16_t const *b_weights,
    size_t a_n,
    size_t b_n,
    simsimd_distance_t *results
) {
    simsimd_kernel_punned_t kernel = NULL;
    simsimd_capability_t used = simsimd_cap_serial_k;

    simsimd_find_kernel_punned(
        simsimd_metric_spdot_counts_k,
        simsimd_datatype_u16_k,
        simsimd_capabilities(),
        simsimd_cap_any_k,
        &kernel,
        &used
    );
    if (kernel == NULL) {
        simsimd_spdot_counts_u16_serial(a, b, a_weights, b_weights, (simsimd_size_t) a_n, (simsimd_size_t) b_n, results);
        return;
    }
    ((simsimd_spdot_counts_fn) kernel)(a, b, a_weights, b_weights, (simsimd_size_t) a_n, (simsimd_size_t) b_n, results);
}

static void simsimd_call_spdot_weights(
    simsimd_u16_t const *a,
    simsimd_u16_t const *b,
    simsimd_bf16_t const *a_weights,
    simsimd_bf16_t const *b_weights,
    size_t a_n,
    size_t b_n,
    simsimd_distance_t *results
) {
    simsimd_kernel_punned_t kernel = NULL;
    simsimd_capability_t used = simsimd_cap_serial_k;

    simsimd_find_kernel_punned(
        simsimd_metric_spdot_weights_k,
        simsimd_datatype_u16_k,
        simsimd_capabilities(),
        simsimd_cap_any_k,
        &kernel,
        &used
    );
    if (kernel == NULL) {
        simsimd_spdot_weights_u16_serial(a, b, a_weights, b_weights, (simsimd_size_t) a_n, (simsimd_size_t) b_n, results);
        return;
    }
    ((simsimd_spdot_weights_fn) kernel)(a, b, a_weights, b_weights, (simsimd_size_t) a_n, (simsimd_size_t) b_n, results);
}

static zend_result simsimd_weights_match(simsimd_buf *ids, simsimd_buf *weights)
{
    if (ids->n == weights->n) {
        return SUCCESS;
    }
    zend_throw_exception_ex(
        simsimd_length_exception_ce,
        0,
        "weights must have the same length, %zu and %zu",
        ids->n,
        weights->n
    );
    return FAILURE;
}

PHP_METHOD(SimsimdVector, sparseI16Dot)
{
    zval *a_zv;
    zval *b_zv;
    zval *a_weights_zv;
    zval *b_weights_zv;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf a_weights;
    simsimd_buf b_weights;
    simsimd_distance_t results[2] = {0, 0};

    ZEND_PARSE_PARAMETERS_START(4, 4)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(a_weights_zv)
        Z_PARAM_ZVAL(b_weights_zv)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_load_unsigned(a_zv, 16, &a) == FAILURE) {
        return;
    }
    if (simsimd_load_unsigned(b_zv, 16, &b) == FAILURE) {
        simsimd_buf_release(&a);
        return;
    }
    if (simsimd_load_i16(a_weights_zv, &a_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        return;
    }
    if (simsimd_load_i16(b_weights_zv, &b_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        simsimd_buf_release(&a_weights);
        return;
    }
    if (simsimd_weights_match(&a, &a_weights) == FAILURE || simsimd_weights_match(&b, &b_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        simsimd_buf_release(&a_weights);
        simsimd_buf_release(&b_weights);
        return;
    }
    simsimd_pad_second_weights(&b_weights, a.n, sizeof(simsimd_i16_t));
    simsimd_call_spdot_counts(a.data, b.data, a_weights.data, b_weights.data, a.n, b.n, results);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&a_weights);
    simsimd_buf_release(&b_weights);
    simsimd_return_pair(return_value, results[0], results[1]);
}

PHP_METHOD(SimsimdVector, sparseBf16Dot)
{
    zval *a_zv;
    zval *b_zv;
    zval *a_weights_zv;
    zval *b_weights_zv;
    simsimd_buf a;
    simsimd_buf b;
    simsimd_buf a_weights;
    simsimd_buf b_weights;
    simsimd_distance_t results[2] = {0, 0};

    ZEND_PARSE_PARAMETERS_START(4, 4)
        Z_PARAM_ZVAL(a_zv)
        Z_PARAM_ZVAL(b_zv)
        Z_PARAM_ZVAL(a_weights_zv)
        Z_PARAM_ZVAL(b_weights_zv)
    ZEND_PARSE_PARAMETERS_END();

    if (simsimd_load_unsigned(a_zv, 16, &a) == FAILURE) {
        return;
    }
    if (simsimd_load_unsigned(b_zv, 16, &b) == FAILURE) {
        simsimd_buf_release(&a);
        return;
    }
    if (simsimd_load_real(a_weights_zv, SIMSIMD_KIND_BF16, &a_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        return;
    }
    if (simsimd_load_real(b_weights_zv, SIMSIMD_KIND_BF16, &b_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        simsimd_buf_release(&a_weights);
        return;
    }
    if (simsimd_weights_match(&a, &a_weights) == FAILURE || simsimd_weights_match(&b, &b_weights) == FAILURE) {
        simsimd_buf_release(&a);
        simsimd_buf_release(&b);
        simsimd_buf_release(&a_weights);
        simsimd_buf_release(&b_weights);
        return;
    }
    simsimd_pad_second_weights(&b_weights, a.n, sizeof(simsimd_bf16_t));
    simsimd_call_spdot_weights(a.data, b.data, a_weights.data, b_weights.data, a.n, b.n, results);
    simsimd_buf_release(&a);
    simsimd_buf_release(&b);
    simsimd_buf_release(&a_weights);
    simsimd_buf_release(&b_weights);
    simsimd_return_pair(return_value, results[0], results[1]);
}

PHP_METHOD(SimsimdVector, capabilities)
{
    ZEND_PARSE_PARAMETERS_NONE();

    array_init(return_value);
    add_assoc_bool(return_value, "neon", simsimd_uses_neon());
    add_assoc_bool(return_value, "neonF16", simsimd_uses_neon_f16());
    add_assoc_bool(return_value, "neonBf16", simsimd_uses_neon_bf16());
    add_assoc_bool(return_value, "neonI8", simsimd_uses_neon_i8());
    add_assoc_bool(return_value, "sve", simsimd_uses_sve());
    add_assoc_bool(return_value, "sveF16", simsimd_uses_sve_f16());
    add_assoc_bool(return_value, "sveBf16", simsimd_uses_sve_bf16());
    add_assoc_bool(return_value, "sveI8", simsimd_uses_sve_i8());
    add_assoc_bool(return_value, "haswell", simsimd_uses_haswell());
    add_assoc_bool(return_value, "skylake", simsimd_uses_skylake());
    add_assoc_bool(return_value, "ice", simsimd_uses_ice());
    add_assoc_bool(return_value, "genoa", simsimd_uses_genoa());
    add_assoc_bool(return_value, "sapphire", simsimd_uses_sapphire());
    add_assoc_bool(return_value, "turin", simsimd_uses_turin());
    add_assoc_bool(return_value, "sierra", simsimd_uses_sierra());
    add_assoc_bool(return_value, "dynamicDispatch", simsimd_uses_dynamic_dispatch());
}

PHP_METHOD(SimsimdVector, flushDenormals)
{
    ZEND_PARSE_PARAMETERS_NONE();
    RETURN_BOOL(simsimd_flush_denormals());
}

static const zend_function_entry simsimd_vector_methods[] = {
    PHP_ME(SimsimdVector, __construct, arginfo_simsimd_construct, ZEND_ACC_PROTECTED)
    PHP_ME(SimsimdVector, cosine, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, dot, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, sqeuclidean, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, euclidean, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, jensenShannon, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, kullbackLeibler, arginfo_simsimd_dense, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, hamming, arginfo_simsimd_binary, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, jaccard, arginfo_simsimd_binary, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, complexDot, arginfo_simsimd_complex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, complexConjugateDot, arginfo_simsimd_complex, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, intersect, arginfo_simsimd_intersect, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, bilinear, arginfo_simsimd_curved, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, complexBilinear, arginfo_simsimd_complex_curved, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, mahalanobis, arginfo_simsimd_curved, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, wsum, arginfo_simsimd_wsum, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, fma, arginfo_simsimd_fma, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, sparseI16Dot, arginfo_simsimd_sparse_dot, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, sparseBf16Dot, arginfo_simsimd_sparse_dot, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, capabilities, arginfo_simsimd_capabilities, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_ME(SimsimdVector, flushDenormals, arginfo_simsimd_flush, ZEND_ACC_PUBLIC | ZEND_ACC_STATIC)
    PHP_FE_END
};

PHP_MINIT_FUNCTION(simsimd)
{
    zend_class_entry ce;

    INIT_NS_CLASS_ENTRY(ce, "Switon\\Simsimd", "Exception", NULL);
    simsimd_exception_ce = zend_register_internal_class_ex(&ce, zend_ce_exception);

    INIT_NS_CLASS_ENTRY(ce, "Switon\\Simsimd", "LengthException", NULL);
    simsimd_length_exception_ce = zend_register_internal_class_ex(&ce, simsimd_exception_ce);

    INIT_NS_CLASS_ENTRY(ce, "Switon\\Simsimd", "ValueException", NULL);
    simsimd_value_exception_ce = zend_register_internal_class_ex(&ce, simsimd_exception_ce);

    INIT_NS_CLASS_ENTRY(ce, "Switon\\Simsimd", "Vector", simsimd_vector_methods);
    simsimd_vector_ce = zend_register_internal_class(&ce);

    return SUCCESS;
}

PHP_MINFO_FUNCTION(simsimd)
{
    php_info_print_table_start();
    php_info_print_table_row(2, "Vendor", "Switon");
    php_info_print_table_row(2, "URL", "https://github.com/switon-php/ext-simsimd");
    php_info_print_table_row(2, "Summary", "SIMD distances for equal-length vectors");
    php_info_print_table_row(2, "Version", PHP_SIMSIMD_VERSION);
    php_info_print_table_row(2, "SimSIMD", PHP_SIMSIMD_LIBRARY);
    php_info_print_table_end();
}

zend_module_entry simsimd_module_entry = {
    STANDARD_MODULE_HEADER,
    "simsimd",
    NULL,
    PHP_MINIT(simsimd),
    NULL,
    NULL,
    NULL,
    PHP_MINFO(simsimd),
    PHP_SIMSIMD_VERSION,
    STANDARD_MODULE_PROPERTIES
};

#ifdef COMPILE_DL_SIMSIMD
ZEND_GET_MODULE(simsimd)
#endif
