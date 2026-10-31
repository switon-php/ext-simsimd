# Switon\Simsimd

IDE stubs for the switon SimSIMD extension. One class per file. The directory matches the namespace. Do not `include` these files and do not Composer-autoload them. The extension provides the same classes at runtime.

`Vector` wraps SimSIMD 6.5.16. The distance runs in the extension. `kind` is `f64` by default, or `f32`, `f16`, `bf16`, `i8`, or `u8`. Call-style samples are in [examples/](../examples/).

`cosine` returns the cosine distance, `1` minus the cosine similarity. `dot` returns the inner product, not `1` minus that product. `complexDot` returns `[real, imag]`. `complexConjugateDot` conjugates the second vector. `jaccard` returns the bit-level Jaccard distance. `hamming` and `jaccard` take raw byte strings. A zero vector or an empty list is passed through to the kernel.

`f16` and `bf16` accept a PHP list, converted with the SimSIMD half compressors, or a native-endian packed string. `intersect` takes sorted `u16` or `u32` lists and returns a count. `bilinear` and `mahalanobis` take a row-major `n×n` matrix. NumKong is not this extension.

## Recommended use

Embedding vectors are `f32`. Pass `kind: 'f32'` for that kernel. PHP lists of numbers are copied into a contiguous buffer for the call.

```php
use Switon\Simsimd\Vector;

$distance = Vector::cosine($left, $right, 'f32');
```

`flushDenormals()` is optional. It changes the calling thread's floating-point environment and is not run during module startup.

## Classes

| Class | Role |
| --- | --- |
| `Switon\Simsimd\Vector` | Static distance calls. The constructor is protected. |
| `Switon\Simsimd\Exception` | Base type for failures from a distance call. |
| `Switon\Simsimd\LengthException` | The vectors differ in length, or a matrix is not `n` by `n`. |
| `Switon\Simsimd\ValueException` | `kind` is unknown, a list element has the wrong type, an `i8` value is outside `-128..127`, a probability or curved call was given `i8`, a packed string has a bad length, or an intersect input is unsorted or out of range. |

A value that is not an array or a string is a PHP `TypeError`, raised by the engine before the call.

## Methods

| PHP | SimSIMD |
| --- | --- |
| `cosine` | `simsimd_cos_{f64,f32,f16,bf16,i8,u8}` |
| `dot` | `simsimd_dot_{f64,f32,f16,bf16,i8,u8}` |
| `sqeuclidean` | `simsimd_l2sq_{f64,f32,f16,bf16,i8,u8}` |
| `euclidean` | `simsimd_l2_{f64,f32,f16,bf16,i8,u8}` |
| `jensenShannon` | `simsimd_js_{f64,f32,f16,bf16}` |
| `kullbackLeibler` | `simsimd_kl_{f64,f32,f16,bf16}` |
| `hamming` | `simsimd_hamming_b8` |
| `jaccard` | `simsimd_jaccard_b8` |
| `complexDot` | `simsimd_dot_{f64c,f32c,f16c,bf16c}` |
| `complexConjugateDot` | `simsimd_vdot_{f64c,f32c,f16c,bf16c}` |
| `intersect` | `simsimd_intersect_{u16,u32}` |
| `bilinear` | `simsimd_bilinear_{f64,f32,f16,bf16}` |
| `complexBilinear` | `simsimd_bilinear_{f64c,f32c,f16c,bf16c}` |
| `mahalanobis` | `simsimd_mahalanobis_{f64,f32,f16,bf16}` |
| `wsum` | `simsimd_wsum_{f64,f32,f16,bf16,i8,u8}` |
| `fma` | `simsimd_fma_{f64,f32,f16,bf16,i8,u8}` |
| `sparseI16Dot` | `simsimd_spdot_counts_u16` |
| `sparseBf16Dot` | `simsimd_spdot_weights_u16` |
| `capabilities` | `simsimd_uses_*` and `simsimd_uses_dynamic_dispatch` |
| `flushDenormals` | `simsimd_flush_denormals` |

## Pitfalls

`i8` rejects floats, including `1.0` stored as a PHP float. Pass ints.

An associative array is not a vector. Keys must be a list, `0..n-1`.

`jaccard('', '')` is `1` because the upstream kernel returns `1` when the bit union is empty.

`intersect` requires sorted inputs. Repeated values follow the kernel's shorter-list walk, not a set cardinality. `mahalanobis` on `f64` uses the library's approximate square root.

`sparseI16Dot` and `sparseBf16Dot` multiply the weights at the first list's cursor. That is the 6.5.16 kernel, including when the two cursors differ. `i8` and `u8` results from `wsum` and `fma` are clamped into the stored range.

Under ZTS the calls need no lock. `flushDenormals()` applies only to the thread that called it.
