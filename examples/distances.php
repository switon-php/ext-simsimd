<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

$left = [1.0, 2.0, 3.0];
$right = [4.0, 5.0, 6.0];

// Default kind is f64. dot is the inner product. The other two are distances.
echo Vector::dot($left, $right), "\n";
echo Vector::sqeuclidean($left, $right), "\n";
echo Vector::euclidean($left, $right), "\n";

// Quantized vectors. i8 accepts only ints in -128..127.
echo Vector::dot([1, -2, 3], [4, 5, -6], 'i8'), "\n";

// Sample output. 32, 27, and -24 are exact. The square root follows the f64 kernel.
// 32
// 27
// 5.1961524227066
// -24
