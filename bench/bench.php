<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

if (!extension_loaded('simsimd')) {
    fwrite(STDERR, "simsimd extension is not loaded\n");
    exit(1);
}

/**
 * @param callable(): float $fn
 */
function microseconds(int $iterations, callable $fn): float
{
    for ($i = 0; $i < 20; $i++) {
        $fn();
    }
    $start = hrtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }

    return (hrtime(true) - $start) / $iterations / 1000;
}

function lists(int $length, int $seed): array
{
    $out = [];
    for ($i = 0; $i < $length; $i++) {
        $out[] = (($i * 3 + $seed) % 17) / 17;
    }

    return $out;
}

/** Popcount of one byte. Built once so the PHP side is not formatting strings. */
function popcountByte(int $byte): int
{
    static $table = null;
    if ($table === null) {
        $table = [];
        for ($i = 0; $i < 256; $i++) {
            $table[$i] = substr_count(sprintf('%08b', $i), '1');
        }
    }

    return $table[$byte];
}

function phpCosine(array $left, array $right): float
{
    $dot = 0.0;
    $leftSq = 0.0;
    $rightSq = 0.0;
    $length = count($left);
    for ($i = 0; $i < $length; $i++) {
        $a = $left[$i];
        $b = $right[$i];
        $dot += $a * $b;
        $leftSq += $a * $a;
        $rightSq += $b * $b;
    }
    if ($leftSq == 0.0 && $rightSq == 0.0) {
        return 0.0;
    }

    return 1.0 - $dot / sqrt($leftSq * $rightSq);
}

function phpHamming(string $left, string $right): float
{
    $bits = 0;
    $length = strlen($left);
    for ($i = 0; $i < $length; $i++) {
        $bits += popcountByte(ord($left[$i]) ^ ord($right[$i]));
    }

    return (float) $bits;
}

/** Same compressor as simsimd_f32_to_f16. Normal numbers around 1 round cleanly. */
function f32ToF16Bits(float $value): int
{
    $bits = unpack('L', pack('f', $value))[1];
    $bits = ($bits + 0x00001000) & 0xFFFFFFFF;
    $exponent = ($bits & 0x7F800000) >> 23;
    $mantissa = $bits & 0x007FFFFF;
    $result = (($bits & 0x80000000) >> 16)
        | (($exponent > 112) ? (((($exponent - 112) << 10) & 0x7C00) | ($mantissa >> 13)) : 0)
        | ((($exponent < 113) && ($exponent > 101)) ? (((((0x007FF000 + $mantissa) >> (125 - $exponent)) + 1) >> 1)) : 0)
        | (($exponent > 143) ? 0x7FFF : 0);

    return $result & 0xFFFF;
}

function f16BitsToF32(int $bits): float
{
    $exponent = ($bits & 0x7C00) >> 10;
    $mantissa = ($bits & 0x03FF) << 13;
    $mantissaBits = unpack('L', pack('f', (float) $mantissa))[1];
    $value = ($mantissaBits >> 23) & 0xFF;
    $conv = (($bits & 0x8000) << 16)
        | (($exponent !== 0) ? ((($exponent + 112) << 23) | $mantissa) : 0)
        | ((($exponent === 0) && ($mantissa !== 0)) ? ((($value - 37) << 23) | (($mantissa << (150 - $value)) & 0x007FE000)) : 0);

    return unpack('f', pack('L', $conv & 0xFFFFFFFF))[1];
}

function packF16(array $values): string
{
    $bytes = '';
    foreach ($values as $value) {
        $bytes .= pack('S', f32ToF16Bits((float) $value));
    }

    return $bytes;
}

/** @return list<float> */
function unpackF16(string $packed): array
{
    $out = [];
    $length = intdiv(strlen($packed), 2);
    for ($i = 0; $i < $length; $i++) {
        $bits = unpack('S', substr($packed, $i * 2, 2))[1];
        $out[] = f16BitsToF32($bits);
    }

    return $out;
}

/** @return list<int> */
function sortedIds(int $length, int $step, int $start): array
{
    $out = [];
    for ($i = 0; $i < $length; $i++) {
        $out[] = $start + $i * $step;
    }

    return $out;
}

function phpIntersect(array $left, array $right): int
{
    $i = 0;
    $j = 0;
    $count = 0;
    $leftCount = count($left);
    $rightCount = count($right);
    while ($i < $leftCount && $j < $rightCount) {
        if ($left[$i] === $right[$j]) {
            $count++;
            $j++;
        } elseif ($left[$i] < $right[$j]) {
            $i++;
        } else {
            $j++;
        }
    }

    return $count;
}

$longLeft = lists(1536, 1);
$longRight = lists(1536, 4);
$shortLeft = [1.0, 0.0, 0.0, 1.0];
$shortRight = [0.0, 1.0, 1.0, 0.0];
$bytesLeft = random_bytes(256);
$bytesRight = random_bytes(256);
$packedLeft = packF16($longLeft);
$packedRight = packF16($longRight);
$halfLeft = unpackF16($packedLeft);
$halfRight = unpackF16($packedRight);
$idLeft = sortedIds(4096, 2, 0);
$idRight = sortedIds(4096, 3, 0);

$phpLong = phpCosine($longLeft, $longRight);
$extLong = Vector::cosine($longLeft, $longRight, 'f32');
if (abs($phpLong - $extLong) > 1e-4) {
    fwrite(STDERR, "cosine 1536 diverged: php $phpLong ext $extLong\n");
    exit(1);
}
$phpShort = phpCosine($shortLeft, $shortRight);
$extShort = Vector::cosine($shortLeft, $shortRight, 'f32');
if (abs($phpShort - $extShort) > 1e-4) {
    fwrite(STDERR, "cosine 4 diverged: php $phpShort ext $extShort\n");
    exit(1);
}
$phpBits = phpHamming($bytesLeft, $bytesRight);
$extBits = Vector::hamming($bytesLeft, $bytesRight);
if ($phpBits !== $extBits) {
    fwrite(STDERR, "hamming diverged: php $phpBits ext $extBits\n");
    exit(1);
}
$phpHalf = phpCosine($halfLeft, $halfRight);
$extHalf = Vector::cosine($packedLeft, $packedRight, 'f16');
if (abs($phpHalf - $extHalf) > 1e-3) {
    fwrite(STDERR, "cosine f16 diverged: php $phpHalf ext $extHalf\n");
    exit(1);
}
$phpIds = phpIntersect($idLeft, $idRight);
$extIds = Vector::intersect($idLeft, $idRight);
if ((float) $phpIds !== $extIds) {
    fwrite(STDERR, "intersect diverged: php $phpIds ext $extIds\n");
    exit(1);
}

$rows = [
    ['cosine 1536 f32', 2000, static fn (): float => phpCosine($longLeft, $longRight), static fn (): float => Vector::cosine($longLeft, $longRight, 'f32')],
    ['cosine 1536 f64', 2000, static fn (): float => phpCosine($longLeft, $longRight), static fn (): float => Vector::cosine($longLeft, $longRight, 'f64')],
    ['cosine 4 f32', 20000, static fn (): float => phpCosine($shortLeft, $shortRight), static fn (): float => Vector::cosine($shortLeft, $shortRight, 'f32')],
    ['hamming 256 bytes', 2000, static fn (): float => phpHamming($bytesLeft, $bytesRight), static fn (): float => Vector::hamming($bytesLeft, $bytesRight)],
    ['cosine 1536 f16', 2000, static fn (): float => phpCosine($halfLeft, $halfRight), static fn (): float => Vector::cosine($packedLeft, $packedRight, 'f16')],
    ['intersect 4096 u32', 2000, static fn (): float => (float) phpIntersect($idLeft, $idRight), static fn (): float => Vector::intersect($idLeft, $idRight)],
];

printf("%-20s %10s %10s\n", 'call', 'php', 'simsimd');
foreach ($rows as [$name, $iterations, $php, $ext]) {
    printf(
        "%-20s %8.2f us %8.2f us\n",
        $name,
        microseconds($iterations, $php),
        microseconds($iterations, $ext),
    );
}
