<?php

declare(strict_types=1);

namespace Switon\Simsimd;

/**
 * Equal-length vector distances from SimSIMD 6.5.16.
 *
 * Calls stay in the extension. `kind` selects the stored type: `f64` (the default),
 * `f32`, `f16`, `bf16`, `i8`, or `u8`. A list of PHP ints and floats is accepted for the
 * floating kinds. `f16` and `bf16` also accept a native-endian packed string, two
 * bytes per element. `i8` accepts only ints in `-128..127`. `u8` accepts only
 * ints in `0..255`.
 *
 * `cosine` is the cosine distance, `1` minus the cosine similarity. `dot` is the
 * inner product itself. `jaccard` is the bit-level Jaccard distance. `hamming` and
 * `jaccard` take raw byte strings, eight bits per byte. A zero vector or an empty
 * list is passed through to the kernel.
 *
 * IDE stub. The extension provides this class at runtime. Do not include this file.
 * The constructor is protected. Distance calls are static.
 */
class Vector
{
    protected function __construct()
    {
    }

    /**
     * Cosine distance between two lists.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     */
    public static function cosine(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Inner product of two lists.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     */
    public static function dot(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Squared Euclidean distance between two lists.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     */
    public static function sqeuclidean(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Euclidean distance between two lists.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     */
    public static function euclidean(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Jensen-Shannon divergence between two lists.
     *
     * `i8` is rejected.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     */
    public static function jensenShannon(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Kullback-Leibler divergence between two lists.
     *
     * `i8` is rejected.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     */
    public static function kullbackLeibler(array|string $a, array|string $b, string $kind = 'f64'): float
    {
    }

    /**
     * Bit-level Hamming distance between two byte strings of equal length.
     */
    public static function hamming(string $a, string $b): float
    {
    }

    /**
     * Bit-level Jaccard distance between two byte strings of equal length.
     *
     * Identical bytes return `0`. Two empty strings return `1`.
     */
    public static function jaccard(string $a, string $b): float
    {
    }

    /**
     * Complex inner product. Each element is a `[real, imag]` pair.
     *
     * Returns `[real, imag]`. `f16` and `bf16` also accept a packed string of
     * native-endian pairs, four bytes per complex value. `complexConjugateDot`
     * conjugates the second vector.
     *
     * @param list<array{int|float, int|float}>|string $a
     * @param list<array{int|float, int|float}>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     * @return array{float, float}
     */
    public static function complexDot(array|string $a, array|string $b, string $kind = 'f64'): array
    {
    }

    /**
     * Complex inner product with the second vector conjugated.
     *
     * @param list<array{int|float, int|float}>|string $a
     * @param list<array{int|float, int|float}>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     * @return array{float, float}
     */
    public static function complexConjugateDot(array|string $a, array|string $b, string $kind = 'f64'): array
    {
    }

    /**
     * Count of matches in two sorted unsigned integer lists.
     *
     * Lengths may differ. Values must be non-decreasing. `u16` is `0..65535`.
     * `u32` is `0..4294967295`. The default kind is `u32`. The kernel puts the
     * shorter list first. On a match only that second cursor moves, so repeated
     * values are not a set cardinality. Unique values are.
     *
     * @param list<int> $a
     * @param list<int> $b
     * @param 'u16'|'u32' $kind
     */
    public static function intersect(array $a, array $b, string $kind = 'u32'): float
    {
    }

    /**
     * Bilinear form `aᵀ C b`. `c` is row-major and has length `n * n`.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param list<int|float>|string $c
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     */
    public static function bilinear(array|string $a, array|string $b, array|string $c, string $kind = 'f64'): float
    {
    }

    /**
     * Complex bilinear form. `c` is a row-major list of `n * n` pairs.
     *
     * @param list<array{int|float, int|float}>|string $a
     * @param list<array{int|float, int|float}>|string $b
     * @param list<array{int|float, int|float}>|string $c
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     * @return array{float, float}
     */
    public static function complexBilinear(array|string $a, array|string $b, array|string $c, string $kind = 'f64'): array
    {
    }

    /**
     * Mahalanobis distance `sqrt((a-b)ᵀ C (a-b))`. `c` is row-major `n * n`.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param list<int|float>|string $c
     * @param 'f64'|'f32'|'f16'|'bf16' $kind
     */
    public static function mahalanobis(array|string $a, array|string $b, array|string $c, string $kind = 'f64'): float
    {
    }

    /**
     * Element-wise weighted sum `alpha * a + beta * b`.
     *
     * Returns one stored value per element. `f16` and `bf16` results are decoded
     * back to PHP floats. `i8` and `u8` results are the clamped stored ints.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     * @return list<float>|list<int>
     */
    public static function wsum(array|string $a, array|string $b, float $alpha = 1.0, float $beta = 1.0, string $kind = 'f64'): array
    {
    }

    /**
     * Element-wise fused multiply-add `alpha * a * b + beta * c`.
     *
     * @param list<int|float>|string $a
     * @param list<int|float>|string $b
     * @param list<int|float>|string $c
     * @param 'f64'|'f32'|'f16'|'bf16'|'i8'|'u8' $kind
     * @return list<float>|list<int>
     */
    public static function fma(array|string $a, array|string $b, array|string $c, float $alpha = 1.0, float $beta = 1.0, string $kind = 'f64'): array
    {
    }

    /**
     * Sparse dot of two sorted `u16` id lists with `i16` weights.
     *
     * One weight per id. Returns `[count, product]`. On a match the 6.5.16
     * kernel multiplies the weights at the first list's cursor.
     *
     * @param list<int> $a
     * @param list<int> $b
     * @param list<int> $aWeights
     * @param list<int> $bWeights
     * @return array{float, float}
     */
    public static function sparseI16Dot(array $a, array $b, array $aWeights, array $bWeights): array
    {
    }

    /**
     * Sparse dot of two sorted `u16` id lists with `bf16` weights.
     *
     * @param list<int> $a
     * @param list<int> $b
     * @param list<int|float> $aWeights
     * @param list<int|float> $bWeights
     * @return array{float, float}
     */
    public static function sparseBf16Dot(array $a, array $b, array $aWeights, array $bWeights): array
    {
    }

    /**
     * Runtime CPU capabilities selected by SimSIMD.
     *
     * @return array{
     *     neon: bool,
     *     neonF16: bool,
     *     neonBf16: bool,
     *     neonI8: bool,
     *     sve: bool,
     *     sveF16: bool,
     *     sveBf16: bool,
     *     sveI8: bool,
     *     haswell: bool,
     *     skylake: bool,
     *     ice: bool,
     *     genoa: bool,
     *     sapphire: bool,
     *     turin: bool,
     *     sierra: bool,
     *     dynamicDispatch: bool
     * }
     */
    public static function capabilities(): array
    {
    }

    /**
     * Flush denormal floats to zero on this thread.
     *
     * SimSIMD asks for this before heavy FMA work. It changes the floating-point
     * environment of the calling thread for the rest of that thread's life, including
     * later PHP float math. The extension does not call it on startup.
     */
    public static function flushDenormals(): bool
    {
    }
}
