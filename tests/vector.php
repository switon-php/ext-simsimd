<?php

declare(strict_types=1);

use Switon\Simsimd\Exception as SimsimdException;
use Switon\Simsimd\LengthException;
use Switon\Simsimd\ValueException;
use Switon\Simsimd\Vector;

/**
 * Independent of the extension. A kernel that changes formula (cosine becoming
 * angular, dot becoming a distance, Jaccard dropping the empty-union case)
 * fails here even when the PHP signatures stay the same.
 */
const SIMSIMD_EPSILON = 1e-7;
const DENSE_LENGTHS = [0, 1, 7, 8, 15, 16, 17, 31, 32, 33, 64];
const BYTE_LENGTHS = [0, 1, 15, 16, 17, 31, 32, 33, 495, 496, 497];

function check(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, $message . PHP_EOL);
        exit(1);
    }
}

function near(float $got, float $want, string $message, float $tol = 1e-9): void
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
        $out[] = (($i * 3 + $seed) % 7) - 3;
    }

    return $out;
}

function floats(int $n, int $seed): array
{
    $out = [];
    for ($i = 0; $i < $n; $i++) {
        $out[] = ((($i * 3 + $seed) % 7) - 3) / 2;
    }

    return $out;
}

function probs(int $n, int $seed): array
{
    $out = [];
    for ($i = 0; $i < $n; $i++) {
        $out[] = (($i * 5 + $seed) % 9 + 1) / 10;
    }

    return $out;
}

function bytes(int $n, int $seed): string
{
    $out = '';
    $x = $seed & 0x7fffffff;
    for ($i = 0; $i < $n; $i++) {
        $x = (1103515245 * $x + 12345) & 0x7fffffff;
        $out .= chr($x & 0xff);
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

function refSqeuclidean(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $delta = (float) $value - (float) $b[$i];
        $sum += $delta * $delta;
    }

    return $sum;
}

function refEuclidean(array $a, array $b): float
{
    return sqrt(refSqeuclidean($a, $b));
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

function f32(float $value): float
{
    return unpack('g', pack('g', $value))[1];
}

/** Mercator series from simsimd_approximate_log(). lib.c uses this instead of log(). */
function refLog(float $number): float
{
    $x = f32($number - 1.0);
    $x2 = f32($x * $x);
    $x3 = f32($x * $x * $x);

    return f32(f32($x - f32($x2 / 2.0)) + f32($x3 / 3.0));
}

/** Bit trick from simsimd_approximate_square_root(). lib.c uses this instead of sqrt(). */
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

/** Euclidean may use libc sqrt or simsimd_approximate_square_root(). */
function nearSqrt(float $got, float $squared, string $message): void
{
    $exact = sqrt($squared);
    $approx = refSqrt($squared);
    $err = min(abs($got - $exact), abs($got - $approx));
    $scale = max(abs($exact), 1e-3);
    check($err <= max(1e-4, 0.01 * $scale), $message . " got $got exact $exact approx $approx");
}

function refKlExact(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $ai = (float) $value;
        $bi = (float) $b[$i];
        $sum += $ai * log(($ai + SIMSIMD_EPSILON) / ($bi + SIMSIMD_EPSILON));
    }

    return $sum;
}

function refJsExact(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $ai = (float) $value;
        $bi = (float) $b[$i];
        $mid = ($ai + $bi) / 2;
        $sum += $ai * log(($ai + SIMSIMD_EPSILON) / ($mid + SIMSIMD_EPSILON));
        $sum += $bi * log(($bi + SIMSIMD_EPSILON) / ($mid + SIMSIMD_EPSILON));
    }
    $half = $sum / 2;

    return $half > 0.0 ? sqrt($half) : 0.0;
}

function refKl(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $ai = (float) $value;
        $bi = (float) $b[$i];
        $sum += $ai * refLog(($ai + SIMSIMD_EPSILON) / ($bi + SIMSIMD_EPSILON));
    }

    return $sum;
}

function refJs(array $a, array $b): float
{
    $sum = 0.0;
    foreach ($a as $i => $value) {
        $ai = (float) $value;
        $bi = (float) $b[$i];
        $mid = ($ai + $bi) / 2;
        $sum += $ai * refLog(($ai + SIMSIMD_EPSILON) / ($mid + SIMSIMD_EPSILON));
        $sum += $bi * refLog(($bi + SIMSIMD_EPSILON) / ($mid + SIMSIMD_EPSILON));
    }
    $half = $sum / 2;

    return $half > 0.0 ? refSqrt($half) : 0.0;
}

/** f64 is the portable approximate kernel. f32 may be that kernel or a SIMD log. */
function closeToKernel(float $got, float $approx, float $exact, string $message): void
{
    check(is_finite($got), $message . " got non-finite $got");
    $approxError = abs($got - $approx);
    $exactError = abs($got - $exact);
    $scale = max(min(abs($approx), abs($exact)), 1e-3);
    check(
        min($approxError, $exactError) <= 0.05 * $scale,
        $message . " got $got approx $approx exact $exact",
    );
}

function refHamming(string $a, string $b): float
{
    $bits = 0;
    $length = strlen($a);
    for ($i = 0; $i < $length; $i++) {
        $bits += substr_count(decbin(ord($a[$i]) ^ ord($b[$i])), '1');
    }

    return (float) $bits;
}

function refJaccard(string $a, string $b): float
{
    $intersection = 0;
    $union = 0;
    $length = strlen($a);
    for ($i = 0; $i < $length; $i++) {
        $left = ord($a[$i]);
        $right = ord($b[$i]);
        $intersection += substr_count(decbin($left & $right), '1');
        $union += substr_count(decbin($left | $right), '1');
    }

    return $union !== 0 ? 1.0 - $intersection / $union : 1.0;
}

/**
 * @param callable(array, array, string=): float $call
 */
function denseFailures(callable $call, string $name, string $kind): void
{
    $bad = static function (string $label, array $left, array $right) use ($call, $name, $kind): void {
        throws(ValueException::class, static fn () => $call($left, $right, $kind), $label, "$name $kind " . $label);
    };

    throws(LengthException::class, static fn () => $call([1, 0], [1], $kind), '2 and 1', "$name $kind shorter right");
    throws(LengthException::class, static fn () => $call([1], [1, 0], $kind), '1 and 2', "$name $kind shorter left");
    throws(LengthException::class, static fn () => $call([], [0], $kind), '0 and 1', "$name $kind empty left");
    throws(LengthException::class, static fn () => $call([0], [], $kind), '1 and 0', "$name $kind empty right");
    $bad('list', [1 => 1, 2 => 2], [1, 2]);
    $bad('list', [1, 2], [1 => 1, 2 => 2]);
    $bad('list', [0 => 1, 2 => 3], [1, 3]);
    $bad('list', [1, 3], [0 => 1, 2 => 3]);
    $bad('list', ['a' => 1], [1]);
    $bad('list', [1], ['a' => 1]);
    $fragment = $kind === 'i8' ? 'int' : 'number';
    foreach (['x', true, null, '1', [1], new stdClass()] as $element) {
        $bad($fragment, [1, $element], [1, 1]);
        $bad($fragment, [1, 1], [1, $element]);
    }
    foreach ([1, null, new stdClass()] as $value) {
        throws(TypeError::class, static fn () => $call($value, [1], $kind), 'array', "$name $kind left " . get_debug_type($value));
        throws(TypeError::class, static fn () => $call([1], $value, $kind), 'array', "$name $kind right " . get_debug_type($value));
    }
    throws(ValueException::class, static fn () => $call('ab', [1], $kind), 'f16', "$name $kind packed left");
    throws(ValueException::class, static fn () => $call([1], 'ab', $kind), 'f16', "$name $kind packed right");
    if ($kind !== 'i8') {
        return;
    }
    throws(ValueException::class, static fn () => $call([128], [1], 'i8'), '-128', "$name i8 128 on the left");
    throws(ValueException::class, static fn () => $call([-129], [1], 'i8'), '-128', "$name i8 -129 on the left");
    throws(ValueException::class, static fn () => $call([1], [128], 'i8'), '-128', "$name i8 128 on the right");
    throws(ValueException::class, static fn () => $call([1], [-129], 'i8'), '-128', "$name i8 -129 on the right");
    throws(ValueException::class, static fn () => $call([1, 128], [1, 1], 'i8'), '-128', "$name i8 128 as the second left element");
    throws(ValueException::class, static fn () => $call([1, 1], [1, -129], 'i8'), '-128', "$name i8 -129 as the second right element");
    throws(ValueException::class, static fn () => $call([1.5], [2], 'i8'), 'int', "$name i8 float on the left");
    throws(ValueException::class, static fn () => $call([1], [1.5], 'i8'), 'int', "$name i8 float on the right");
    throws(ValueException::class, static fn () => $call([1.0], [1], 'i8'), 'int', "$name i8 float 1.0 on the left");
}

function denseArgumentFailures(callable $call, string $name): void
{
    throws(ArgumentCountError::class, static fn () => $call(), 'at least', "$name no arguments");
    throws(ArgumentCountError::class, static fn () => $call([1]), 'at least', "$name missing argument");
    throws(ArgumentCountError::class, static fn () => $call([1], [1], 'f64', 'extra'), 'at most', "$name extra argument");
    foreach ([1, null, []] as $kind) {
        throws(TypeError::class, static fn () => $call([1], [1], $kind), 'string', "$name kind " . get_debug_type($kind));
    }
    foreach (['', 'F64', 'f64 ', ' i8', 'u16', 'u32', 'f16c', 'i8 '] as $kind) {
        throws(ValueException::class, static fn () => $call([1], [1], $kind), 'f64', "$name kind [$kind]");
    }
}

function binaryFailures(callable $call, string $name): void
{
    throws(LengthException::class, static fn () => $call('ab', 'a'), '2 and 1', "$name shorter right");
    throws(LengthException::class, static fn () => $call('a', 'ab'), '1 and 2', "$name shorter left");
    throws(LengthException::class, static fn () => $call('', 'a'), '0 and 1', "$name empty left");
    throws(LengthException::class, static fn () => $call('a', ''), '1 and 0', "$name empty right");
    throws(LengthException::class, static fn () => $call('é', 'e'), '2 and 1', "$name counts bytes");
    foreach ([[1], 1, null, new stdClass()] as $value) {
        throws(TypeError::class, static fn () => $call($value, 'a'), 'string', "$name left " . get_debug_type($value));
        throws(TypeError::class, static fn () => $call('a', $value), 'string', "$name right " . get_debug_type($value));
    }
    throws(ArgumentCountError::class, static fn () => $call(), 'exactly 2', "$name no arguments");
    throws(ArgumentCountError::class, static fn () => $call('a'), '1 given', "$name missing argument");
    throws(ArgumentCountError::class, static fn () => $call('a', 'a', 'f64'), '3 given', "$name extra argument");
}

function reflectedType(?ReflectionType $type): string
{
    if ($type instanceof ReflectionNamedType) {
        return $type->getName();
    }
    if ($type instanceof ReflectionUnionType) {
        $names = [];
        foreach ($type->getTypes() as $part) {
            $names[] = $part instanceof ReflectionNamedType ? $part->getName() : '';
        }
        sort($names);

        return implode('|', $names);
    }

    return '';
}

function assertFloat(float $value, string $message = 'distance'): float
{
    check(is_float($value), $message . ' did not return a float');

    return $value;
}

check(extension_loaded('simsimd'), 'simsimd is not loaded');
$funcs = get_extension_funcs('simsimd');
check($funcs === false || $funcs === [], 'simsimd exported a function');
$version = phpversion('simsimd');
check(
    is_string($version) && preg_match('/^[0-9]+\.[0-9]+\.[0-9]+([\-+].*)?$/', $version) === 1,
    'module version is not a semver from the git tag',
);
ob_start();
phpinfo(INFO_MODULES);
$info = ob_get_clean();
check(str_contains($info, 'simsimd'), 'module name is missing from phpinfo');
check(str_contains($info, 'Switon'), 'module vendor is missing');
check(str_contains($info, 'https://github.com/switon-php/ext-simsimd'), 'module URL is missing');
check(str_contains($info, 'SIMD distances for equal-length vectors'), 'module summary is missing');
check(str_contains($info, $version), 'module info version does not match phpversion');
check(str_contains($info, '6.5.16'), 'SimSIMD version is missing from module info');

// Exception, LengthException, ValueException
foreach ([
    SimsimdException::class => Exception::class,
    LengthException::class => SimsimdException::class,
    ValueException::class => SimsimdException::class,
] as $class => $parent) {
    $reflection = new ReflectionClass($class);
    check($reflection->getParentClass()->getName() === $parent, "$class parent");
    check(!$reflection->isAbstract() && !$reflection->isFinal(), "$class modifiers");
    check($reflection->getConstants() === [], "$class declared a constant");
    $own = [];
    foreach ($reflection->getMethods() as $method) {
        if ($method->getDeclaringClass()->getName() === $class) {
            $own[] = $method->getName();
        }
    }
    check($own === [], "$class declared a method: " . implode(',', $own));
}
$previous = new SimsimdException('previous', 7);
$length = new LengthException('length', 3, $previous);
$value = new ValueException('value', 4, $previous);
check($length->getMessage() === 'length' && $length->getCode() === 3 && $length->getPrevious() === $previous, 'LengthException constructor');
check($value->getMessage() === 'value' && $value->getCode() === 4 && $value->getPrevious() === $previous, 'ValueException constructor');
check($length instanceof SimsimdException && $length instanceof Exception, 'LengthException hierarchy');
check($value instanceof SimsimdException && !$value instanceof LengthException, 'ValueException hierarchy');
check(!$length instanceof \LengthException, 'LengthException became the SPL type');
check(!$value instanceof \InvalidArgumentException, 'ValueException became InvalidArgumentException');

// Vector
$vector = new ReflectionClass(Vector::class);
check(!$vector->isAbstract() && !$vector->isFinal(), 'Vector modifiers');
check($vector->getParentClass() === false, 'Vector has a parent');
check($vector->getConstants() === [] && $vector->getProperties() === [], 'Vector has a constant or a property');
$constructor = $vector->getConstructor();
check($constructor !== null && $constructor->isProtected(), 'constructor is not protected');
throws(Error::class, static fn () => new Vector(), 'protected', 'construct');

class VectorChild extends Vector
{
    public function __construct()
    {
        parent::__construct();
    }
}

$child = new VectorChild();
near(assertFloat($child::dot([2], [5])), 10.0, 'subclass static call');

$half = 'array|string';
$expectedMethods = [
    'cosine' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'dot' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'sqeuclidean' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'euclidean' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'jensenShannon' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'kullbackLeibler' => ['float', ['a' => $half, 'b' => $half], 'f64'],
    'hamming' => ['float', ['a' => 'string', 'b' => 'string'], null],
    'jaccard' => ['float', ['a' => 'string', 'b' => 'string'], null],
    'complexDot' => ['array', ['a' => $half, 'b' => $half], 'f64'],
    'complexConjugateDot' => ['array', ['a' => $half, 'b' => $half], 'f64'],
    'intersect' => ['float', ['a' => 'array', 'b' => 'array'], 'u32'],
    'bilinear' => ['float', ['a' => $half, 'b' => $half, 'c' => $half], 'f64'],
    'complexBilinear' => ['array', ['a' => $half, 'b' => $half, 'c' => $half], 'f64'],
    'mahalanobis' => ['float', ['a' => $half, 'b' => $half, 'c' => $half], 'f64'],
    'wsum' => ['array', ['a' => $half, 'b' => $half], 'f64', ['alpha' => 1.0, 'beta' => 1.0]],
    'fma' => ['array', ['a' => $half, 'b' => $half, 'c' => $half], 'f64', ['alpha' => 1.0, 'beta' => 1.0]],
    'sparseI16Dot' => ['array', ['a' => 'array', 'b' => 'array', 'aWeights' => 'array', 'bWeights' => 'array'], null],
    'sparseBf16Dot' => ['array', ['a' => 'array', 'b' => 'array', 'aWeights' => 'array', 'bWeights' => 'array'], null],
    'capabilities' => ['array', [], null],
    'flushDenormals' => ['bool', [], null],
];
$public = [];
foreach ($vector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
    $public[] = $method->getName();
    check($method->isStatic() && !$method->isAbstract(), $method->getName() . ' is not a concrete static method');
    check($method->getDeclaringClass()->getName() === Vector::class, $method->getName() . ' is inherited');
    $spec = $expectedMethods[$method->getName()] ?? null;
    check($spec !== null, 'unexpected method ' . $method->getName());
    check($method->getReturnType() instanceof ReflectionNamedType, $method->getName() . ' return type');
    check($method->getReturnType()->getName() === $spec[0] && !$method->getReturnType()->allowsNull(), $method->getName() . ' return');
    $names = [];
    foreach ($method->getParameters() as $parameter) {
        $names[$parameter->getName()] = $parameter;
    }
    $expectedNames = array_keys($spec[1]);
    foreach ($spec[3] ?? [] as $name => $default) {
        $expectedNames[] = $name;
    }
    if ($spec[2] !== null) {
        $expectedNames[] = 'kind';
    }
    check(array_keys($names) === $expectedNames, $method->getName() . ' parameters');
    foreach ($spec[1] as $name => $type) {
        check(reflectedType($names[$name]->getType()) === $type, "$name type on " . $method->getName());
        check(!$names[$name]->isOptional(), $method->getName() . " $name is optional");
    }
    foreach ($spec[3] ?? [] as $name => $default) {
        check(reflectedType($names[$name]->getType()) === 'float', "$name type on " . $method->getName());
        check($names[$name]->isOptional() && $names[$name]->getDefaultValue() == $default, $method->getName() . " $name default");
    }
    if ($spec[2] !== null) {
        $kind = $names['kind'];
        check($kind->isOptional() && $kind->getDefaultValue() === $spec[2], $method->getName() . ' kind default');
        check($kind->getType() instanceof ReflectionNamedType && $kind->getType()->getName() === 'string', $method->getName() . ' kind type');
    }
}
check($public === array_keys($expectedMethods), 'public methods changed: ' . implode(',', $public));

// cosine
foreach (['f64', 'f32', 'i8'] as $kind) {
    // i8 cosine uses an approximate rsqrt. On CI that is about 5e-4 away from libc sqrt.
    $tol = $kind === 'f64' ? 1e-5 : ($kind === 'i8' ? 1e-3 : 1e-6);
    near(assertFloat(Vector::cosine([1, 0], [1, 0], $kind)), 0.0, "cosine $kind identical", 1e-6);
    near(Vector::cosine([1, 0], [0, 1], $kind), 1.0, "cosine $kind orthogonal", 1e-6);
    $oppositeTol = $kind === 'f64' ? 1e-6 : 2.5e-4;
    near(Vector::cosine([1, 0], [-1, 0], $kind), 2.0, "cosine $kind opposite", $oppositeTol);
    check(abs(Vector::cosine([1, 0], [-1, 0], $kind) - M_PI) > 0.5, "cosine $kind became angular");
    near(Vector::cosine([0, 0], [1, 0], $kind), 1.0, "cosine $kind one zero vector", 1e-6);
    $zeros = $kind === 'i8' ? [0, 0] : [0.0, 0.0];
    near(Vector::cosine($zeros, $zeros, $kind), 0.0, "cosine $kind zero vector", 1e-6);
    foreach (DENSE_LENGTHS as $n) {
        $left = ints($n, 1);
        $right = ints($n, 4);
        near(Vector::cosine($left, $right, $kind), refCosine($left, $right), "cosine $kind ints $n", $tol);
        if ($kind !== 'i8') {
            $left = floats($n, 2);
            $right = floats($n, 5);
            near(Vector::cosine($left, $right, $kind), refCosine($left, $right), "cosine $kind floats $n", $tol);
        }
    }
}
near(Vector::cosine([1, 0], [0, 1]), Vector::cosine([1, 0], [0, 1], 'f64'), 'cosine default kind');
near(Vector::cosine(a: [3.0, 4.0], b: [3.0, 4.0], kind: 'f32'), 0.0, 'cosine named arguments', 1e-6);
denseArgumentFailures(Vector::cosine(...), 'cosine');
foreach (['f64', 'f32', 'i8'] as $kind) {
    denseFailures(Vector::cosine(...), 'cosine', $kind);
}

// dot
foreach (['f64', 'f32', 'i8'] as $kind) {
    near(assertFloat(Vector::dot([1, 2, 3], [4, 5, 6], $kind)), 32.0, "dot $kind");
    near(Vector::dot([-2, 3], [4, 5], $kind), 7.0, "dot $kind signed");
    near(Vector::dot([1, 0], [0, 1], $kind), 0.0, "dot $kind orthogonal");
    near(Vector::dot([1, 0], [1, 0], $kind), 1.0, "dot $kind is the inner product");
    foreach (DENSE_LENGTHS as $n) {
        $left = ints($n, 1);
        $right = ints($n, 4);
        near(Vector::dot($left, $right, $kind), refDot($left, $right), "dot $kind ints $n");
        if ($kind !== 'i8') {
            $left = floats($n, 2);
            $right = floats($n, 5);
            near(Vector::dot($left, $right, $kind), refDot($left, $right), "dot $kind floats $n", $kind === 'f32' ? 1e-5 : 1e-9);
        }
    }
}
near(Vector::dot([1, 2, 3], [4, 5, 6]), 32.0, 'dot default kind');
near(Vector::dot([1, 1.5], [2, 2.0]), 5.0, 'dot mixed int and float');
near(Vector::dot([-128, 127], [1, -1], 'i8'), -255.0, 'dot i8 bounds on both sides');
near(Vector::dot([16777217], [1], 'f64'), 16777217.0, 'dot f64 keeps 2^24+1');
check(Vector::dot([16777217], [1], 'f32') === 16777216.0, 'dot f32 did not narrow 2^24+1');
check(is_nan(Vector::dot([NAN], [1.0])), 'dot did not pass NaN through');
check(is_infinite(Vector::dot([INF], [1.0])), 'dot did not pass INF through');
near(Vector::dot(a: [-0.0], b: [1.0]), 0.0, 'dot named arguments');
denseArgumentFailures(Vector::dot(...), 'dot');
foreach (['f64', 'f32', 'i8'] as $kind) {
    denseFailures(Vector::dot(...), 'dot', $kind);
}

// sqeuclidean
foreach (['f64', 'f32', 'i8'] as $kind) {
    near(assertFloat(Vector::sqeuclidean([1, 2], [4, 6], $kind)), 25.0, "sqeuclidean $kind");
    near(Vector::sqeuclidean([-1, -2], [-4, -6], $kind), 25.0, "sqeuclidean $kind negatives");
    near(Vector::sqeuclidean([1, 0], [1, 0], $kind), 0.0, "sqeuclidean $kind identical");
    near(Vector::sqeuclidean([1, 2], [4, 6], $kind), Vector::sqeuclidean([4, 6], [1, 2], $kind), "sqeuclidean $kind symmetry");
    foreach (DENSE_LENGTHS as $n) {
        $left = ints($n, 2);
        $right = ints($n, 6);
        near(Vector::sqeuclidean($left, $right, $kind), refSqeuclidean($left, $right), "sqeuclidean $kind ints $n");
    }
}
near(Vector::sqeuclidean([1, 2], [4, 6]), 25.0, 'sqeuclidean default kind');
near(Vector::sqeuclidean([-128], [127], 'i8'), 65025.0, 'sqeuclidean i8 bounds');
denseArgumentFailures(Vector::sqeuclidean(...), 'sqeuclidean');
foreach (['f64', 'f32', 'i8'] as $kind) {
    denseFailures(Vector::sqeuclidean(...), 'sqeuclidean', $kind);
}

// euclidean
foreach (['f64', 'f32', 'i8'] as $kind) {
    nearSqrt(Vector::euclidean([3, 4], [0, 0], $kind), 25.0, "euclidean $kind");
    nearSqrt(Vector::euclidean([-3, -4], [0, 0], $kind), 25.0, "euclidean $kind negatives");
    near(Vector::euclidean([3, 4], [0, 0], $kind), Vector::euclidean([0, 0], [3, 4], $kind), "euclidean $kind symmetry");
    near(Vector::euclidean([1, 2], [1, 2], $kind), 0.0, "euclidean $kind identical");
    foreach (DENSE_LENGTHS as $n) {
        $left = ints($n, 3);
        $right = ints($n, 8);
        $squared = Vector::sqeuclidean($left, $right, $kind);
        nearSqrt(Vector::euclidean($left, $right, $kind), $squared, "euclidean $kind ints $n");
    }
}
nearSqrt(Vector::euclidean([3, 4], [0, 0]), 25.0, 'euclidean default kind');
nearSqrt(Vector::euclidean([-128], [127], 'i8'), 65025.0, 'euclidean i8 bounds');
denseArgumentFailures(Vector::euclidean(...), 'euclidean');
foreach (['f64', 'f32', 'i8'] as $kind) {
    denseFailures(Vector::euclidean(...), 'euclidean', $kind);
}

// jensenShannon. f64 is always the serial approximate kernel. f32 follows the CPU.
foreach (['f64', 'f32'] as $kind) {
    near(assertFloat(Vector::jensenShannon([0.5, 0.5], [0.5, 0.5], $kind)), 0.0, "jensenShannon $kind identical", 1e-6);
    foreach (DENSE_LENGTHS as $n) {
        $left = probs($n, 1);
        $right = probs($n, 4);
        $got = Vector::jensenShannon($left, $right, $kind);
        if ($kind === 'f64') {
            near($got, refJs($left, $right), "jensenShannon f64 $n", 1e-5);
        } else {
            closeToKernel($got, refJs($left, $right), refJsExact($left, $right), "jensenShannon f32 $n");
        }
        near($got, Vector::jensenShannon($right, $left, $kind), "jensenShannon $kind symmetry $n", $kind === 'f64' ? 1e-9 : 1e-5);
        check($got >= 0.0, "jensenShannon $kind $n was negative");
    }
}
near(Vector::jensenShannon([0.5, 0.5], [0.5, 0.5]), 0.0, 'jensenShannon default kind');
near(Vector::jensenShannon(a: [0.5, 0.5], b: [0.5, 0.5], kind: 'f32'), 0.0, 'jensenShannon named arguments', 1e-6);
denseArgumentFailures(Vector::jensenShannon(...), 'jensenShannon');
throws(ValueException::class, static fn () => Vector::jensenShannon([0.5, 0.5], [0.5, 0.5], 'i8'), 'probability', 'jensenShannon rejects i8');
foreach (['f64', 'f32'] as $kind) {
    denseFailures(Vector::jensenShannon(...), 'jensenShannon', $kind);
}

// kullbackLeibler
foreach (['f64', 'f32'] as $kind) {
    near(assertFloat(Vector::kullbackLeibler([0.5, 0.5], [0.5, 0.5], $kind)), 0.0, "kullbackLeibler $kind identical", 1e-6);
    foreach (DENSE_LENGTHS as $n) {
        if ($n === 0) {
            near(Vector::kullbackLeibler([], [], $kind), 0.0, "kullbackLeibler $kind empty");
            continue;
        }
        $left = probs($n, 1);
        $right = probs($n, 4);
        $forward = Vector::kullbackLeibler($left, $right, $kind);
        $reverse = Vector::kullbackLeibler($right, $left, $kind);
        if ($kind === 'f64') {
            near($forward, refKl($left, $right), "kullbackLeibler f64 $n", 1e-5);
        } else {
            closeToKernel($forward, refKl($left, $right), refKlExact($left, $right), "kullbackLeibler f32 $n");
        }
        check($forward !== $reverse, "kullbackLeibler $kind $n did not keep argument order");
    }
}
near(Vector::kullbackLeibler([0.5, 0.5], [0.5, 0.5]), 0.0, 'kullbackLeibler default kind');
near(Vector::kullbackLeibler(a: [0.2], b: [0.2]), 0.0, 'kullbackLeibler named arguments', 1e-6);
denseArgumentFailures(Vector::kullbackLeibler(...), 'kullbackLeibler');
throws(ValueException::class, static fn () => Vector::kullbackLeibler([0.5, 0.5], [0.5, 0.5], 'i8'), 'probability', 'kullbackLeibler rejects i8');
foreach (['f64', 'f32'] as $kind) {
    denseFailures(Vector::kullbackLeibler(...), 'kullbackLeibler', $kind);
}

// hamming
near(assertFloat(Vector::hamming("\x00", "\xff")), 8.0, 'hamming 0x00 and 0xff');
near(Vector::hamming("\x0f", "\x00"), 4.0, 'hamming 0x0f and 0x00');
near(Vector::hamming('ab', 'ab'), 0.0, 'hamming identical');
near(Vector::hamming("\x00\xff", "\xff\x00"), 16.0, 'hamming two bytes');
near(Vector::hamming("\x00\x00", "\x00\x00"), 0.0, 'hamming interior NUL identical');
near(Vector::hamming('é', 'é'), 0.0, 'hamming multibyte identical');
foreach (BYTE_LENGTHS as $n) {
    $left = bytes($n, 11);
    $right = bytes($n, 29);
    near(Vector::hamming($left, $right), refHamming($left, $right), "hamming bytes $n");
    near(Vector::hamming($left, $left), 0.0, "hamming identical $n");
    near(Vector::hamming($left, $right), Vector::hamming($right, $left), "hamming symmetry $n");
}
$tail = bytes(17, 3);
$tail[16] = "\xff";
near(Vector::hamming($tail, bytes(17, 3)), refHamming($tail, bytes(17, 3)), 'hamming tail byte');
near(Vector::hamming(a: "\x0f", b: "\x00"), 4.0, 'hamming named arguments');
binaryFailures(Vector::hamming(...), 'hamming');

// jaccard
near(assertFloat(Vector::jaccard("\xff", "\xff")), 0.0, 'jaccard identical');
near(Vector::jaccard("\xff", "\x00"), 1.0, 'jaccard disjoint');
near(Vector::jaccard('', ''), 1.0, 'jaccard empty');
near(Vector::jaccard("\x00", "\x00"), 1.0, 'jaccard zero byte');
near(Vector::jaccard("\x00\x00", "\x00\x00"), 1.0, 'jaccard zero bytes');
near(Vector::jaccard("\x0f", "\xff"), 0.5, 'jaccard partial');
foreach (BYTE_LENGTHS as $n) {
    $left = bytes($n, 11);
    $right = bytes($n, 29);
    near(Vector::jaccard($left, $right), refJaccard($left, $right), "jaccard bytes $n");
    near(Vector::jaccard($left, $left), $n === 0 || $left === str_repeat("\x00", $n) ? 1.0 : 0.0, "jaccard identical $n");
    near(Vector::jaccard($left, $right), Vector::jaccard($right, $left), "jaccard symmetry $n");
}
near(Vector::jaccard(a: "\x0f", b: "\xff"), 0.5, 'jaccard named arguments');
binaryFailures(Vector::jaccard(...), 'jaccard');

// capabilities
$caps = Vector::capabilities();
check(is_array($caps) && !array_is_list($caps), 'capabilities is not an associative array');
$capKeys = [
    'neon', 'neonF16', 'neonBf16', 'neonI8',
    'sve', 'sveF16', 'sveBf16', 'sveI8',
    'haswell', 'skylake', 'ice', 'genoa', 'sapphire', 'turin', 'sierra',
    'dynamicDispatch',
];
check(array_keys($caps) === $capKeys, 'capabilities keys differ: ' . implode(',', array_keys($caps)));
foreach ($capKeys as $key) {
    check(is_bool($caps[$key]), "capabilities $key is not a bool");
}
check($caps['dynamicDispatch'] === true, 'capabilities dynamicDispatch is off');
$arm = in_array(php_uname('m'), ['arm64', 'aarch64'], true);
$armKeys = ['neon', 'neonF16', 'neonBf16', 'neonI8', 'sve', 'sveF16', 'sveBf16', 'sveI8'];
$x86Keys = ['haswell', 'skylake', 'ice', 'genoa', 'sapphire', 'turin', 'sierra'];
if ($arm) {
    check($caps['neon'] === true, 'arm capabilities neon is off');
    foreach ($x86Keys as $key) {
        check($caps[$key] === false, "arm capabilities $key is on");
    }
} else {
    foreach ($armKeys as $key) {
        check($caps[$key] === false, "non-arm capabilities $key is on");
    }
}
$caps['dynamicDispatch'] = false;
check(Vector::capabilities()['dynamicDispatch'] === true, 'capabilities returned a shared array');
check(Vector::capabilities() === Vector::capabilities(), 'capabilities is not stable');
throws(ArgumentCountError::class, static fn () => Vector::capabilities(1), 'exactly 0', 'capabilities argument');
throws(Error::class, static fn () => Vector::capabilities(extra: 1), 'named', 'capabilities named argument');

// flushDenormals stays last: it changes this thread's floating-point environment.
check(Vector::flushDenormals() === true && is_bool(Vector::flushDenormals()), 'flushDenormals did not return true');
throws(ArgumentCountError::class, static fn () => Vector::flushDenormals(1), 'exactly 0', 'flushDenormals argument');
throws(Error::class, static fn () => Vector::flushDenormals(extra: 1), 'named', 'flushDenormals named argument');

echo "ok\n";
