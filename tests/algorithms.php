<?php

declare(strict_types=1);

use Switon\Simsimd\LengthException;
use Switon\Simsimd\ValueException;
use Switon\Simsimd\Vector;

/**
 * Independent formulas for the kernels added after the first distance set.
 * Half-precision inputs here are values that stay exact in both f16 and bf16,
 * so a change in the product, the merge, or aᵀCb fails the comparison.
 */
const HALF_LENGTHS = [0, 1, 7, 8, 15, 16, 17, 31, 32, 33];
const CURVE_LENGTHS = [0, 1, 2, 3, 4, 7, 8, 15, 16];

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function near(float $got, float $want, string $message, float $tol = 1e-6): void
{
    check(is_finite($got), $message . " got non-finite $got");
    check(abs($got - $want) <= $tol, $message . " got $got want $want");
}

/**
 * @param class-string<Throwable> $class
 */
function throws(string $class, callable $fn, string $fragment, string $label): void
{
    try {
        $fn();
        check(false, "$label did not throw");
    } catch (Throwable $e) {
        check($e instanceof $class, "$label threw " . $e::class . ': ' . $e->getMessage());
        check(str_contains($e->getMessage(), $fragment), "$label message: " . $e->getMessage());
    }
}

function ints(int $n, int $seed): array
{
    $out = [];
    for ($i = 0; $i < $n; $i++) {
        $out[] = (($i * 3 + $seed) % 5) - 2;
    }

    return $out;
}

function refDot(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $sum += (float) $value * (float) $b[$i];
    }

    return $sum;
}

function f32(float $value): float
{
    return unpack('g', pack('g', $value))[1];
}

/** Bit trick from simsimd_approximate_square_root(). lib.c uses it for f64. */
function refSqrt(float $number): float
{
    if ($number == 0.0) {
        return 0.0;
    }
    $bits = unpack('V', pack('g', $number))[1];
    if ($bits >= 0x80000000) {
        $bits -= 0x100000000;
    }
    $bits = 0x5F1FFFF9 - ($bits >> 1);
    $guess = unpack('g', pack('V', $bits & 0xFFFFFFFF))[1];
    $guess = f32($guess * f32(f32(0.703952253) * f32(f32(2.38924456) - f32(f32($number * $guess) * $guess))));

    return f32($number * $guess);
}

function nearRoot(float $got, float $sum, string $message): void
{
    check(is_finite($got), $message . " got non-finite $got");
    $exact = $sum > 0.0 ? sqrt($sum) : 0.0;
    $approx = refSqrt($sum);
    $error = min(abs($got - $exact), abs($got - $approx));
    $scale = max(abs($exact), 1e-3);
    check($error <= max(1e-4, 0.01 * $scale), $message . " got $got exact $exact approx $approx");
}

function refSqeuclidean(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $delta = (float) $value - (float) $b[$i];
        $sum += $delta * $delta;
    }

    return $sum;
}

function refCosine(array $a, array $b): float
{
    $dot = 0.0;
    $left = 0.0;
    $right = 0.0;
    foreach ($a as $i => $value) {
        $ai = (float) $value;
        $bi = (float) $b[$i];
        $dot += $ai * $bi;
        $left += $ai * $ai;
        $right += $bi * $bi;
    }
    if ($left == 0.0 && $right == 0.0) {
        return 0.0;
    }
    if ($dot == 0.0) {
        return 1.0;
    }
    $distance = 1.0 - $dot / sqrt($left * $right);

    return $distance > 0.0 ? $distance : 0.0;
}

function refComplexDot(array $a, array $b): array
{
    $real = 0.0;
    $imag = 0.0;
    foreach ($a as $i => $pair) {
        $real += $pair[0] * $b[$i][0] - $pair[1] * $b[$i][1];
        $imag += $pair[0] * $b[$i][1] + $pair[1] * $b[$i][0];
    }

    return [$real, $imag];
}

function refComplexVdot(array $a, array $b): array
{
    $real = 0.0;
    $imag = 0.0;
    foreach ($a as $i => $pair) {
        $real += $pair[0] * $b[$i][0] + $pair[1] * $b[$i][1];
        $imag += $pair[0] * $b[$i][1] - $pair[1] * $b[$i][0];
    }

    return [$real, $imag];
}

function refBilinear(array $a, array $b, array $c): float
{
    $n = count($a);
    $sum = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $row = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $row += (float) $c[$i * $n + $j] * (float) $b[$j];
        }
        $sum += (float) $a[$i] * $row;
    }

    return $sum;
}

function refComplexBilinear(array $a, array $b, array $c): array
{
    $n = count($a);
    $real = 0.0;
    $imag = 0.0;
    for ($i = 0; $i < $n; $i++) {
        $rowReal = 0.0;
        $rowImag = 0.0;
        for ($j = 0; $j < $n; $j++) {
            $cr = (float) $c[$i * $n + $j][0];
            $ci = (float) $c[$i * $n + $j][1];
            $br = (float) $b[$j][0];
            $bi = (float) $b[$j][1];
            $rowReal += $cr * $br - $ci * $bi;
            $rowImag += $cr * $bi + $ci * $br;
        }
        $ar = (float) $a[$i][0];
        $ai = (float) $a[$i][1];
        $real += $ar * $rowReal - $ai * $rowImag;
        $imag += $ar * $rowImag + $ai * $rowReal;
    }

    return [$real, $imag];
}

/**
 * Serial intersect. The shorter list is the first cursor. On equality only the
 * second cursor advances. At or above a 64x length ratio the kernel gallops and
 * counts each shorter element that occurs at least once in the longer list.
 */
function refIntersect(array $a, array $b): int
{
    if (count($b) < count($a)) {
        [$a, $b] = [$b, $a];
    }
    if ($a !== [] && count($b) >= 64 * count($a)) {
        $count = 0;
        $start = 0;
        $length = count($b);
        foreach ($a as $value) {
            $low = $start;
            $high = $length;
            while ($low < $high) {
                $mid = intdiv($low + $high, 2);
                if ($b[$mid] < $value) {
                    $low = $mid + 1;
                } else {
                    $high = $mid;
                }
            }
            $start = $low;
            if ($low < $length && $b[$low] === $value) {
                $count++;
            }
        }

        return $count;
    }
    $i = 0;
    $j = 0;
    $count = 0;
    $aCount = count($a);
    $bCount = count($b);
    while ($i < $aCount && $j < $bCount) {
        if ($a[$i] === $b[$j]) {
            $count++;
            $j++;
        } elseif ($a[$i] < $b[$j]) {
            $i++;
        } else {
            $j++;
        }
    }

    return $count;
}

function pairs(int $n, int $seed): array
{
    $out = [];
    for ($i = 0; $i < $n; $i++) {
        $out[] = [(($i * 3 + $seed) % 5) - 2, (($i * 5 + $seed) % 5) - 2];
    }

    return $out;
}

function square(int $n, int $seed): array
{
    return ints($n * $n, $seed);
}

function complexSquare(int $n, int $seed): array
{
    return pairs($n * $n, $seed);
}

function packHalf(string $kind, array $values): string
{
    $f16 = [
        -2.0 => 0xC000, -1.0 => 0xBC00, 0.0 => 0x0000, 1.0 => 0x3C00, 2.0 => 0x4000,
        3.0 => 0x4200, 4.0 => 0x4400, 5.0 => 0x4500, 6.0 => 0x4600,
    ];
    $bf16 = [
        -2.0 => 0xC000, -1.0 => 0xBF80, 0.0 => 0x0000, 1.0 => 0x3F80, 2.0 => 0x4000,
        3.0 => 0x4040, 4.0 => 0x4080, 5.0 => 0x40A0, 6.0 => 0x40C0,
    ];
    $table = $kind === 'f16' ? $f16 : $bf16;
    $bytes = '';
    foreach ($values as $value) {
        $bits = $table[(float) $value] ?? null;
        check($bits !== null, "no packed bits for $value");
        $bytes .= pack('S', $bits);
    }

    return $bytes;
}

/**
 * @param array{0: float, 1: float} $got
 * @param array{0: float, 1: float} $want
 */
function nearPair(array $got, array $want, string $message, float $tol = 1e-6): void
{
    check(array_is_list($got) && count($got) === 2, $message . ' is not a pair');
    check(is_float($got[0]) && is_float($got[1]), $message . ' is not two floats');
    near($got[0], $want[0], $message . ' real', $tol);
    near($got[1], $want[1], $message . ' imag', $tol);
}

check(extension_loaded('simsimd'), 'simsimd is not loaded');

foreach (['f16', 'bf16'] as $kind) {
    $tol = 1e-4;
    near(Vector::cosine([1, 0], [-1, 0], $kind), 2.0, "cosine $kind opposite", 1e-3);
    check(abs(Vector::cosine([1, 0], [-1, 0], $kind) - M_PI) > 0.5, "cosine $kind became angular");
    near(Vector::cosine([0, 0], [0, 0], $kind), 0.0, "cosine $kind zero", 1e-5);
    near(Vector::cosine([0, 0], [1, 0], $kind), 1.0, "cosine $kind one zero", 1e-4);
    near(Vector::dot([1, 2, 3], [4, 5, 6], $kind), 32.0, "dot $kind sample");
    near(Vector::sqeuclidean([1, 2], [4, 6], $kind), 25.0, "sqeuclidean $kind sample");
    nearRoot(Vector::euclidean([3, 4], [0, 0], $kind), 25.0, "euclidean $kind sample");
    foreach (HALF_LENGTHS as $n) {
        $left = ints($n, 1);
        $right = ints($n, 4);
        near(Vector::dot($left, $right, $kind), refDot($left, $right), "dot $kind $n");
        near(Vector::sqeuclidean($left, $right, $kind), refSqeuclidean($left, $right), "sqeuclidean $kind $n", $tol);
        near(Vector::cosine($left, $right, $kind), refCosine($left, $right), "cosine $kind $n", 1e-3);
        nearRoot(Vector::euclidean($left, $right, $kind), refSqeuclidean($left, $right), "euclidean $kind $n");
    }
    $packedLeft = packHalf($kind, [1.0, 2.0, 3.0]);
    $packedRight = packHalf($kind, [4.0, 5.0, 6.0]);
    near(Vector::dot($packedLeft, $packedRight, $kind), 32.0, "dot $kind packed");
    near(Vector::dot($packedLeft, [4, 5, 6], $kind), 32.0, "dot $kind mixed");
    near(Vector::dot('', '', $kind), 0.0, "dot $kind empty string");
    throws(ValueException::class, static fn () => Vector::dot('a', 'a', $kind), 'multiple of 2', "dot $kind odd string");
    throws(LengthException::class, static fn () => Vector::dot($packedLeft, [1, 2], $kind), '3 and 2', "dot $kind packed length");
    $js = Vector::jensenShannon([0.5, 0.25, 0.75], [0.25, 0.5, 0.25], $kind);
    $kl = Vector::kullbackLeibler([0.5, 0.25, 0.75], [0.25, 0.5, 0.25], $kind);
    check(is_float($js) && is_finite($js) && $js > 0.0, "js $kind sample");
    check(is_float($kl) && is_finite($kl) && $kl > 0.0, "kl $kind sample");
    near(Vector::jensenShannon([], [], $kind), 0.0, "js $kind empty");
    near(Vector::kullbackLeibler([], [], $kind), 0.0, "kl $kind empty");
}

foreach (['f64', 'f32', 'f16', 'bf16'] as $kind) {
    $tol = $kind === 'f64' || $kind === 'f32' ? 1e-6 : 1e-3;
    $left = [[1.0, 2.0], [3.0, 4.0]];
    $right = [[5.0, 6.0], [7.0, 8.0]];
    nearPair(Vector::complexDot($left, $right, $kind), [-18.0, 68.0], "complexDot $kind sample", $tol);
    nearPair(Vector::complexConjugateDot($left, $right, $kind), [70.0, -8.0], "complexConjugateDot $kind sample", $tol);
    nearPair(Vector::complexDot([], [], $kind), [0.0, 0.0], "complexDot $kind empty", $tol);
    foreach (HALF_LENGTHS as $n) {
        $a = pairs($n, 1);
        $b = pairs($n, 4);
        nearPair(Vector::complexDot($a, $b, $kind), refComplexDot($a, $b), "complexDot $kind $n", $tol);
        nearPair(Vector::complexConjugateDot($a, $b, $kind), refComplexVdot($a, $b), "complexConjugateDot $kind $n", $tol);
    }
    throws(LengthException::class, static fn () => Vector::complexDot([[1, 0]], [], $kind), '1 and 0', "complexDot $kind length");
    throws(ValueException::class, static fn () => Vector::complexDot([1], [1], $kind), 'pair', "complexDot $kind bare number");
    throws(ValueException::class, static fn () => Vector::complexDot([[1]], [[1, 0]], $kind), 'pair', "complexDot $kind short pair");
    throws(ValueException::class, static fn () => Vector::complexDot([[1, 2, 3]], [[1, 0]], $kind), 'pair', "complexDot $kind long pair");
    throws(ValueException::class, static fn () => Vector::complexDot([['a' => 1, 'b' => 2]], [[1, 0]], $kind), 'pair', "complexDot $kind assoc pair");
    if ($kind === 'f64' || $kind === 'f32') {
        throws(ValueException::class, static fn () => Vector::complexDot('abcd', 'abcd', $kind), 'f16', "complexDot $kind string");
    }
}

$complexPacked = packHalf('f16', [1.0, 0.0, 0.0, 1.0]);
$complexOther = packHalf('f16', [0.0, 1.0, 1.0, 0.0]);
nearPair(Vector::complexDot($complexPacked, $complexOther, 'f16'), [0.0, 2.0], 'complexDot f16 packed');
throws(ValueException::class, static fn () => Vector::complexDot('abc', 'abc', 'f16'), 'multiple of 4', 'complexDot f16 odd packed');
throws(ValueException::class, static fn () => Vector::complexDot([[1, 0]], [[1, 0]], 'i8'), 'i8', 'complexDot i8');
throws(ArgumentCountError::class, static fn () => Vector::complexDot(), 'at least', 'complexDot no arguments');
throws(ArgumentCountError::class, static fn () => Vector::complexDot([[1, 0]], [[1, 0]], 'f64', 1), 'at most', 'complexDot extra');
throws(TypeError::class, static fn () => Vector::complexDot(1, [[1, 0]]), 'array', 'complexDot type');

foreach (['u16', 'u32'] as $kind) {
    near(Vector::intersect([1, 2, 3], [2, 3, 4], $kind), 2.0, "intersect $kind sample");
    near(Vector::intersect([], [1], $kind), 0.0, "intersect $kind empty");
    near(Vector::intersect([1, 1], [1, 1], $kind), (float) refIntersect([1, 1], [1, 1]), "intersect $kind duplicates");
    near(Vector::intersect([1, 1], [1], $kind), (float) refIntersect([1, 1], [1]), "intersect $kind duplicate against one");
    $long = range(0, 199);
    near(Vector::intersect([10, 150], $long, $kind), 2.0, "intersect $kind long");
    near(Vector::intersect(range(0, 127), range(64, 190), $kind), 64.0, "intersect $kind window");
    $repeated = array_merge(range(0, 4), [5], range(6, 200));
    near(Vector::intersect([5, 5], $repeated, $kind), (float) refIntersect([5, 5], $repeated), "intersect $kind gallop");
    throws(ValueException::class, static fn () => Vector::intersect([2, 1], [1], $kind), 'sorted', "intersect $kind unsorted left");
    throws(ValueException::class, static fn () => Vector::intersect([1], [2, 1], $kind), 'sorted', "intersect $kind unsorted right");
    throws(ValueException::class, static fn () => Vector::intersect([-1], [1], $kind), '0', "intersect $kind negative");
    throws(ValueException::class, static fn () => Vector::intersect([1.0], [1], $kind), 'int', "intersect $kind float");
    throws(ValueException::class, static fn () => Vector::intersect(['a' => 1], [1], $kind), 'list', "intersect $kind assoc");
}
near(Vector::intersect([1, 2], [2]), 1.0, 'intersect default u32');
throws(ValueException::class, static fn () => Vector::intersect([65536], [1], 'u16'), '65535', 'intersect u16 range');
throws(ValueException::class, static fn () => Vector::intersect([4294967296], [1], 'u32'), '4294967295', 'intersect u32 range');
near(Vector::intersect([65535], [65535], 'u16'), 1.0, 'intersect u16 max');
near(Vector::intersect([4294967295], [4294967295], 'u32'), 1.0, 'intersect u32 max');
throws(ValueException::class, static fn () => Vector::intersect([1], [1], 'f64'), 'u16', 'intersect kind');
throws(ArgumentCountError::class, static fn () => Vector::intersect([1]), 'at least', 'intersect missing');
throws(TypeError::class, static fn () => Vector::intersect('ab', [1]), 'array', 'intersect type');

foreach (['f64', 'f32', 'f16', 'bf16'] as $kind) {
    $tol = $kind === 'f64' ? 1e-8 : 1e-3;
    near(Vector::bilinear([1, 2], [3, 4], [1, 0, 0, 1], $kind), 11.0, "bilinear $kind identity", $tol);
    near(Vector::bilinear([], [], [], $kind), 0.0, "bilinear $kind empty");
    nearRoot(Vector::mahalanobis([3, 4], [0, 0], [1, 0, 0, 1], $kind), 25.0, "mahalanobis $kind identity");
    near(Vector::mahalanobis([1, 1], [1, 1], [2, 0, 0, 3], $kind), 0.0, "mahalanobis $kind same point", 1e-5);
    foreach (CURVE_LENGTHS as $n) {
        $a = ints($n, 1);
        $b = ints($n, 2);
        $c = square($n, 3);
        near(Vector::bilinear($a, $b, $c, $kind), refBilinear($a, $b, $c), "bilinear $kind $n", $tol);
        $eye = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                $eye[] = $i === $j ? 1 : 0;
            }
        }
        nearRoot(Vector::mahalanobis($a, $b, $eye, $kind), refSqeuclidean($a, $b), "mahalanobis $kind $n");
        $ca = pairs($n, 1);
        $cb = pairs($n, 2);
        $cc = complexSquare($n, 3);
        nearPair(Vector::complexBilinear($ca, $cb, $cc, $kind), refComplexBilinear($ca, $cb, $cc), "complexBilinear $kind $n", $tol);
    }
    throws(LengthException::class, static fn () => Vector::bilinear([1, 2], [3], [1, 0, 0, 1], $kind), '2 and 1', "bilinear $kind vector length");
    throws(LengthException::class, static fn () => Vector::bilinear([1, 2], [3, 4], [1, 0, 0], $kind), 'n by n', "bilinear $kind matrix");
    throws(LengthException::class, static fn () => Vector::mahalanobis([1], [1], [], $kind), 'n by n', "mahalanobis $kind matrix");
    throws(ValueException::class, static fn () => Vector::bilinear([1], [1], [1], 'i8'), 'i8', "bilinear i8");
    throws(ValueException::class, static fn () => Vector::complexBilinear([[1, 0]], [[1, 0]], [[1, 0]], 'i8'), 'i8', 'complexBilinear i8');
}

throws(ArgumentCountError::class, static fn () => Vector::bilinear([1], [1]), 'at least', 'bilinear missing');
throws(ArgumentCountError::class, static fn () => Vector::bilinear([1], [1], [1], 'f64', 1), 'at most', 'bilinear extra');
throws(TypeError::class, static fn () => Vector::mahalanobis(1, [1], [1]), 'array', 'mahalanobis type');
throws(ValueException::class, static fn () => Vector::bilinear('ab', 'ab', 'abcd', 'f64'), 'f16', 'bilinear packed f64');

function nearVector(array $got, array $want, string $message, bool $asInt = false, float $tol = 1e-6): void
{
    check(array_is_list($got) && count($got) === count($want), $message . ' length ' . count($got) . ' vs ' . count($want));
    foreach ($want as $i => $value) {
        if ($asInt) {
            check($got[$i] === $value, "$message [$i] got {$got[$i]} want $value");
        } else {
            check(is_float($got[$i]), "$message [$i] is not a float");
            near($got[$i], (float) $value, "$message [$i]", $tol);
        }
    }
}

function refWsum(array $a, array $b, float $alpha, float $beta): array
{
    $out = [];
    foreach ($a as $i => $value) {
        $out[] = (float) $value * $alpha + (float) $b[$i] * $beta;
    }

    return $out;
}

function refFma(array $a, array $b, array $c, float $alpha, float $beta): array
{
    $out = [];
    foreach ($a as $i => $value) {
        $out[] = (float) $value * (float) $b[$i] * $alpha + (float) $c[$i] * $beta;
    }

    return $out;
}

function quantizeI8(float $value): int
{
    if ($value > 127.0) {
        return 127;
    }
    if ($value < -128.0) {
        return -128;
    }

    return (int) ($value + ($value < 0.0 ? -0.5 : 0.5));
}

function quantizeU8(float $value): int
{
    if ($value > 255.0) {
        return 255;
    }
    if ($value < 0.0) {
        return 0;
    }

    return (int) ($value + ($value < 0.0 ? -0.5 : 0.5));
}

/** 6.5.16 multiplies weights at the first cursor, including on a match. */
function refSparse(array $a, array $b, array $aWeights, array $bWeights): array
{
    $i = 0;
    $j = 0;
    $count = 0.0;
    $product = 0.0;
    while ($i < count($a) && $j < count($b)) {
        if ($a[$i] === $b[$j]) {
            $count++;
            $product += $aWeights[$i] * $bWeights[$i];
            $j++;
        } elseif ($a[$i] < $b[$j]) {
            $i++;
        } else {
            $j++;
        }
    }

    return [$count, $product];
}

near(Vector::dot([1, 2, 3], [4, 5, 6], 'u8'), 32.0, 'dot u8');
near(Vector::cosine([1, 0], [1, 0], 'u8'), 0.0, 'cosine u8 identical', 1e-4);
near(Vector::cosine([1, 0], [0, 1], 'u8'), 1.0, 'cosine u8 orthogonal', 1e-4);
near(Vector::sqeuclidean([1, 2], [4, 6], 'u8'), 25.0, 'sqeuclidean u8');
nearRoot(Vector::euclidean([3, 4], [0, 0], 'u8'), 25.0, 'euclidean u8');
near(Vector::dot([], [], 'u8'), 0.0, 'dot u8 empty');
throws(ValueException::class, static fn () => Vector::dot([256], [1], 'u8'), '255', 'u8 256');
throws(ValueException::class, static fn () => Vector::dot([-1], [1], 'u8'), '0', 'u8 negative');
throws(ValueException::class, static fn () => Vector::dot([1.0], [1], 'u8'), 'int', 'u8 float');
throws(ValueException::class, static fn () => Vector::jensenShannon([1], [1], 'u8'), 'u8', 'js u8');
throws(ValueException::class, static fn () => Vector::bilinear([1], [1], [1], 'u8'), 'u8', 'bilinear u8');

foreach (['f64', 'f32', 'f16', 'bf16'] as $kind) {
    $tol = $kind === 'f64' ? 1e-9 : 1e-4;
    $want = refWsum([1, 2, 3], [4, 5, 6], 2.0, 0.5);
    nearVector(Vector::wsum([1, 2, 3], [4, 5, 6], 2.0, 0.5, $kind), $want, "wsum $kind", false, $tol);
    $fma = refFma([1, 2], [3, 4], [5, 6], 2.0, 0.5);
    nearVector(Vector::fma([1, 2], [3, 4], [5, 6], 2.0, 0.5, $kind), $fma, "fma $kind", false, $tol);
    nearVector(Vector::wsum([], [], 1.0, 1.0, $kind), [], "wsum $kind empty");
}
nearVector(Vector::wsum([1, 2], [3, 4]), refWsum([1, 2], [3, 4], 1.0, 1.0), 'wsum defaults');
nearVector(Vector::wsum([100], [0], 2.0, 1.0, 'i8'), [127], 'wsum i8 clamp', true);
nearVector(Vector::wsum([-100], [0], 2.0, 1.0, 'i8'), [-128], 'wsum i8 clamp low', true);
nearVector(Vector::wsum([200], [0], 2.0, 1.0, 'u8'), [255], 'wsum u8 clamp', true);
nearVector(Vector::fma([2, 3], [4, 5], [6, 7], 1.0, 1.0, 'i8'), [14, 22], 'fma i8', true);
nearVector(Vector::wsum([1, -2], [3, 4], 1.0, 1.0, 'i8'), [4, 2], 'wsum i8 exact', true);
throws(LengthException::class, static fn () => Vector::wsum([1], [1, 2]), '1 and 2', 'wsum length');
throws(LengthException::class, static fn () => Vector::fma([1], [1], [1, 2]), '1 and 2', 'fma length');
throws(ArgumentCountError::class, static fn () => Vector::wsum([1]), 'at least', 'wsum missing');
throws(ArgumentCountError::class, static fn () => Vector::fma([1], [1], [1], 1.0, 1.0, 'f64', 1), 'at most', 'fma extra');
throws(ValueException::class, static fn () => Vector::wsum([1.5], [1], 1.0, 1.0, 'i8'), 'int', 'wsum i8 float');

$idsA = [1, 2, 3];
$idsB = [2, 3, 5];
$counts = refSparse($idsA, $idsB, [2, 3, 4], [6, 7, 8]);
nearPair(Vector::sparseI16Dot($idsA, $idsB, [2, 3, 4], [6, 7, 8]), $counts, 'sparseI16Dot');
nearPair(Vector::sparseBf16Dot($idsA, $idsB, [2, 3, 4], [6, 7, 8]), $counts, 'sparseBf16Dot', 1e-5);
nearPair(Vector::sparseI16Dot([], [], [], []), [0.0, 0.0], 'sparseI16Dot empty');
throws(ValueException::class, static fn () => Vector::sparseI16Dot([2, 1], [1], [1, 1], [1]), 'sorted', 'sparse unsorted');
throws(LengthException::class, static fn () => Vector::sparseI16Dot([1], [1], [1, 2], [1]), '1 and 2', 'sparse weight length');
throws(ValueException::class, static fn () => Vector::sparseI16Dot([1], [1], [32768], [1]), '32767', 'sparse i16 range');
check(Vector::sparseBf16Dot([1], [1], [1.5], [1])[0] === 1.0, 'sparse bf16 accepts a float weight');
throws(ArgumentCountError::class, static fn () => Vector::sparseI16Dot([1], [1], [1]), 'exactly 4', 'sparse missing');
throws(TypeError::class, static fn () => Vector::sparseI16Dot(1, [1], [1], [1]), 'array', 'sparse type');

echo "ok\n";
