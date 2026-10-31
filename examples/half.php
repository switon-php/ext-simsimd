<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// A PHP list is converted to f16. The same bytes can be passed packed.
$left = [1.0, 2.0, 3.0];
$right = [4.0, 5.0, 6.0];

echo Vector::dot($left, $right, 'f16'), "\n";
echo Vector::dot($left, $right, 'bf16'), "\n";

// Sample output. 32 is exact for these integers in both half formats.
// 32
// 32
