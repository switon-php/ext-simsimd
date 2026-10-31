<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// wsum is alpha * a + beta * b. fma is alpha * a * b + beta * c.
$a = [1.0, 2.0, 3.0];
$b = [4.0, 5.0, 6.0];
$c = [7.0, 8.0, 9.0];

echo implode(' ', Vector::wsum($a, $b, 2.0, 0.5)), "\n";
echo implode(' ', Vector::fma($a, $b, $c, 1.0, 1.0)), "\n";
echo Vector::dot([1, 2, 3], [4, 5, 6], 'u8'), "\n";

// Sample output. These integers stay exact in f64. The u8 dot is the same product.
// 4 6.5 9
// 11 18 27
// 32
